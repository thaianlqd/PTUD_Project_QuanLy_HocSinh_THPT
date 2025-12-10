<?php
class TuyenSinhModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL (Tự động thử port 3307 và 3306)
        $ports = [3307, 3306];
        $connected = false;
        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->db->exec("SET NAMES 'utf8mb4'");
                $connected = true;
                break;
            } catch (PDOException $e) { continue; }
        }
        if (!$connected) die("Lỗi kết nối CSDL.");
    }

    // --- API 1: CHỈ TIÊU ---
    public function getDanhSachTruong() {
        return $this->db->query("SELECT ma_truong, ten_truong, chi_tieu_hoc_sinh, so_luong_hoc_sinh FROM truong_thpt ORDER BY ma_truong")->fetchAll();
    }

    public function updateChiTieuBatch($data) {
        $sql = "UPDATE truong_thpt SET chi_tieu_hoc_sinh = ? WHERE ma_truong = ?";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $ma_truong => $chi_tieu) {
            $stmt->execute([$chi_tieu, $ma_truong]);
        }
        return true;
    }

    // --- API 2: NHẬP ĐIỂM ---
    public function getDanhSachThiSinhByTruong($ma_truong) {
        // Lấy thí sinh đăng ký NV1 vào trường này
        $sql = "SELECT ts.ma_nguoi_dung, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh, ts.truong_thcs, ts.lop_hoc,
                       dts.diem_toan, dts.diem_van, dts.diem_anh
                FROM thi_sinh ts
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                JOIN nguyen_vong nv ON ts.ma_nguoi_dung = nv.ma_nguoi_dung
                LEFT JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                WHERE nv.thu_tu_nguyen_vong = 1 AND nv.ma_truong = ?
                ORDER BY ts.so_bao_danh ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_truong]);
        return $stmt->fetchAll();
    }

    public function updateDiemBatch($payload) {
        $sql = "INSERT INTO diem_thi_tuyen_sinh (ma_nguoi_dung, diem_toan, diem_van, diem_anh, nam_tuyen_sinh)
                VALUES (?, ?, ?, ?, 2025)
                ON DUPLICATE KEY UPDATE 
                diem_toan = VALUES(diem_toan), diem_van = VALUES(diem_van), diem_anh = VALUES(diem_anh)";
        $stmt = $this->db->prepare($sql);
        foreach ($payload as $row) {
            $stmt->execute([$row['ma_nguoi_dung'], $row['diem_toan'], $row['diem_van'], $row['diem_anh']]);
        }
        return true;
    }

    // --- API 3: LỌC ẢO (CORE LOGIC - ĐÃ NÂNG CẤP) ---
    // --- API 3: LỌC ẢO (CORE LOGIC - FULL & FIXED) ---
    public function runLocAo($resetAll = true) {
        $this->db->beginTransaction();
        try {
            // 1. Chuẩn bị dữ liệu ban đầu
            $da_tuyen = []; // Mảng lưu số lượng đã tuyển của từng trường
            $exclude_users = []; // Danh sách thí sinh đã có kết quả chốt (Xác nhận/Từ chối) -> Không xét lại

            // Lấy chỉ tiêu tất cả các trường
            $chi_tieu = $this->db->query("SELECT ma_truong, chi_tieu_hoc_sinh FROM truong_thpt")->fetchAll(PDO::FETCH_KEY_PAIR);
            foreach ($chi_tieu as $tid => $ct) { $da_tuyen[$tid] = 0; }

            if ($resetAll) {
                // --- CHẾ ĐỘ 1: RESET TOÀN BỘ (Làm mới từ đầu - Xóa sạch) ---
                $this->db->exec("DELETE FROM ket_qua_thi_tuyen_sinh");
                $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0");
            } else {
                // --- CHẾ ĐỘ 2: GIỮ LẠI KẾT QUẢ ĐÃ CHỐT ---
                
                // B1: Lấy danh sách những người đã có quyết định (Xác nhận nhập học HOẶC Từ chối nhập học)
                $sqlConfirmed = "SELECT kq.ma_truong_trung_tuyen, dts.ma_nguoi_dung, kq.trang_thai_xac_nhan
                                 FROM ket_qua_thi_tuyen_sinh kq
                                 JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                                 WHERE kq.trang_thai_xac_nhan IN ('Xac_nhan_nhap_hoc', 'Tu_choi_nhap_hoc')";
                
                $confirmedList = $this->db->query($sqlConfirmed)->fetchAll();

                // B2: Duyệt danh sách để cập nhật chỉ tiêu và danh sách loại trừ
                foreach ($confirmedList as $item) {
                    $tid = $item['ma_truong_trung_tuyen'];
                    
                    // Logic quan trọng: Chỉ người "Xác nhận nhập học" mới chiếm 1 slot của trường
                    if ($item['trang_thai_xac_nhan'] === 'Xac_nhan_nhap_hoc') {
                        if (isset($da_tuyen[$tid])) {
                            $da_tuyen[$tid]++; 
                        }
                    }
                    
                    // Cả "Xác nhận" và "Từ chối" đều được đưa vào danh sách loại trừ (để không bị xét tuyển lại)
                    $exclude_users[] = $item['ma_nguoi_dung']; 
                }

                // B3: CHỈ XÓA những dòng CHƯA có kết quả cuối cùng (Chờ xác nhận, Trượt,...)
                // Những dòng 'Xac_nhan_nhap_hoc' và 'Tu_choi_nhap_hoc' sẽ ĐƯỢC GIỮ NGUYÊN trong DB
                $this->db->exec("DELETE FROM ket_qua_thi_tuyen_sinh WHERE trang_thai_xac_nhan NOT IN ('Xac_nhan_nhap_hoc', 'Tu_choi_nhap_hoc')");
            }

            // 2. Lấy danh sách thí sinh để xét tuyển (Có sắp xếp Tiêu chí phụ)
            // Logic: Tổng điểm cao nhất -> Toán cao nhất -> Văn cao nhất -> Anh cao nhất
            $sql = "SELECT dts.ma_diem_thi, dts.ma_nguoi_dung, 
                           (dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh) as tong_diem 
                    FROM diem_thi_tuyen_sinh dts
                    WHERE diem_toan IS NOT NULL AND diem_van IS NOT NULL AND diem_anh IS NOT NULL
                    ORDER BY tong_diem DESC, dts.diem_toan DESC, dts.diem_van DESC, dts.diem_anh DESC";
            
            $thi_sinh_list = $this->db->query($sql)->fetchAll();

            // 3. Chuẩn bị câu lệnh Insert
            $stmtInsert = $this->db->prepare("INSERT INTO ket_qua_thi_tuyen_sinh (ma_diem_thi, tong_diem, trang_thai, ma_nguyen_vong_trung_tuyen, ma_truong_trung_tuyen, trang_thai_xac_nhan) VALUES (?, ?, ?, ?, ?, 'Cho_xac_nhan')");
            $stmtFail = $this->db->prepare("INSERT INTO ket_qua_thi_tuyen_sinh (ma_diem_thi, tong_diem, trang_thai, trang_thai_xac_nhan) VALUES (?, ?, 'Truot', 'Cho_xac_nhan')");

            // 4. Vòng lặp xét tuyển
            foreach ($thi_sinh_list as $ts) {
                // QUAN TRỌNG: Nếu thí sinh này nằm trong danh sách loại trừ (đã xác nhận hoặc đã từ chối)
                // -> Bỏ qua, không xét lại, giữ nguyên kết quả cũ trong DB.
                if (!$resetAll && in_array($ts['ma_nguoi_dung'], $exclude_users)) {
                    continue; 
                }

                $passed = false;
                // Lấy nguyện vọng của thí sinh
                $nvs = $this->db->query("SELECT ma_nguyen_vong, ma_truong FROM nguyen_vong WHERE ma_nguoi_dung = {$ts['ma_nguoi_dung']} ORDER BY thu_tu_nguyen_vong ASC")->fetchAll();
                
                foreach ($nvs as $nv) {
                    $tid = $nv['ma_truong'];
                    // Kiểm tra còn slot không
                    if (isset($chi_tieu[$tid]) && $da_tuyen[$tid] < $chi_tieu[$tid]) {
                        $da_tuyen[$tid]++; // Chiếm 1 slot
                        $stmtInsert->execute([$ts['ma_diem_thi'], $ts['tong_diem'], 'Dau', $nv['ma_nguyen_vong'], $tid]);
                        $passed = true;
                        break; // Đậu NV này rồi thì thôi các NV sau
                    }
                }

                if (!$passed) {
                    // Nếu trượt hết các NV thì ghi nhận là Trượt
                    $stmtFail->execute([$ts['ma_diem_thi'], $ts['tong_diem']]);
                }
            }

            // 5. Cập nhật lại số lượng học sinh thực tế vào bảng trường (để Admin theo dõi)
            foreach ($da_tuyen as $mt => $sl) {
                $this->db->prepare("UPDATE truong_thpt SET so_luong_hoc_sinh = ? WHERE ma_truong = ?")->execute([$sl, $mt]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // --- API 6: LẤY KẾT QUẢ LỌC ẢO (ĐÃ CẬP NHẬT LẤY NV1, NV2, NV3) ---
    public function getKetQuaLoc() {
        // SQL này sẽ nối thêm 3 lần vào bảng nguyện vọng để lấy tên trường NV1, NV2, NV3
        $sql = "SELECT 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    kq.tong_diem, 
                    kq.trang_thai, 
                    kq.trang_thai_xac_nhan, 
                    COALESCE(t_trung.ten_truong, '---') as truong_trung_tuyen,
                    
                    -- Lấy tên trường NV1
                    COALESCE(t1.ten_truong, '---') AS ten_truong_nv1,
                    
                    -- Lấy tên trường NV2
                    COALESCE(t2.ten_truong, '---') AS ten_truong_nv2,
                    
                    -- Lấy tên trường NV3
                    COALESCE(t3.ten_truong, '---') AS ten_truong_nv3

                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                
                -- Join để lấy tên trường trúng tuyển (nếu có)
                LEFT JOIN truong_thpt t_trung ON kq.ma_truong_trung_tuyen = t_trung.ma_truong
                
                -- Join lấy NV1
                LEFT JOIN nguyen_vong nv1 ON ts.ma_nguoi_dung = nv1.ma_nguoi_dung AND nv1.thu_tu_nguyen_vong = 1
                LEFT JOIN truong_thpt t1 ON nv1.ma_truong = t1.ma_truong
                
                -- Join lấy NV2
                LEFT JOIN nguyen_vong nv2 ON ts.ma_nguoi_dung = nv2.ma_nguoi_dung AND nv2.thu_tu_nguyen_vong = 2
                LEFT JOIN truong_thpt t2 ON nv2.ma_truong = t2.ma_truong
                
                -- Join lấy NV3
                LEFT JOIN nguyen_vong nv3 ON ts.ma_nguoi_dung = nv3.ma_nguoi_dung AND nv3.thu_tu_nguyen_vong = 3
                LEFT JOIN truong_thpt t3 ON nv3.ma_truong = t3.ma_truong

                ORDER BY kq.tong_diem DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    // --- API 4: CHỐT DANH SÁCH ---
    public function getDsLopKhoi10() {
        // THÊM: , si_so vào dòng SELECT
        return $this->db->query("SELECT ma_lop, ten_lop, ma_truong, khoi, ma_to_hop_mon, si_so FROM lop_hoc WHERE khoi = 10 AND trang_thai_lop = 'HoatDong'")->fetchAll();
    }

    public function getThiSinhTrungTuyenTheoTruong($ma_truong) {
        $sql = "SELECT kq.ma_ket_qua_tuyen_sinh, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh, kq.tong_diem, kq.trang_thai_xac_nhan
                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                WHERE kq.ma_truong_trung_tuyen = ? AND kq.trang_thai = 'Dau'
                ORDER BY kq.tong_diem DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_truong]);
        return $stmt->fetchAll();
    }

    public function updateTrangThaiXacNhanBatch($data, $ma_truong) {
        $sql = "UPDATE ket_qua_thi_tuyen_sinh SET trang_thai_xac_nhan = ? WHERE ma_ket_qua_tuyen_sinh = ? AND ma_truong_trung_tuyen = ?";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $item) {
            $stmt->execute([$item['trang_thai'], $item['ma_ket_qua'], $ma_truong]);
        }
        return true;
    }

    public function chotNhapHoc($ma_truong, $ma_lop_dich) {
        $this->db->beginTransaction();
        try {
            // Lấy thí sinh đã XÁC NHẬN nhập học
            $sql = "SELECT dts.ma_nguoi_dung 
                    FROM ket_qua_thi_tuyen_sinh kq
                    JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                    WHERE kq.ma_truong_trung_tuyen = ? AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
                    AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_truong]);
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmtIns = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ngay_nhap_hoc, trang_thai, ma_lop) VALUES (?, CURDATE(), 'DangHoc', ?)");
            $count = 0;
            foreach ($users as $uid) {
                $stmtIns->execute([$uid, $ma_lop_dich]);
                $count++;
            }
            
            // Cập nhật lại sĩ số lớp
             $this->db->prepare("UPDATE lop_hoc SET si_so = (SELECT COUNT(*) FROM hoc_sinh WHERE ma_lop = ?) WHERE ma_lop = ?")->execute([$ma_lop_dich, $ma_lop_dich]);

            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getDsTruongThcs() {
        $sql = "SELECT DISTINCT truong_thcs FROM thi_sinh WHERE truong_thcs IS NOT NULL AND truong_thcs != '' ORDER BY truong_thcs";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDsThiSinhByTruongThcs($truong_thcs) {
        $sql = "
            SELECT 
                ts.ma_nguoi_dung,
                nd.ho_ten,
                nd.ngay_sinh,
                ts.so_bao_danh,
                ts.lop_hoc,
                ts.truong_thcs,
                
                nv1.ma_truong AS ma_truong_nv1,
                t1.ten_truong AS ten_truong_nv1,
                
                nv2.ma_truong AS ma_truong_nv2,
                t2.ten_truong AS ten_truong_nv2,
                
                nv3.ma_truong AS ma_truong_nv3,
                t3.ten_truong AS ten_truong_nv3,
                
                dt.diem_toan,
                dt.diem_van,
                dt.diem_anh
            FROM thi_sinh ts
            JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
            
            LEFT JOIN nguyen_vong nv1 ON ts.ma_nguoi_dung = nv1.ma_nguoi_dung AND nv1.thu_tu_nguyen_vong = 1
            LEFT JOIN truong_thpt t1 ON nv1.ma_truong = t1.ma_truong
            
            LEFT JOIN nguyen_vong nv2 ON ts.ma_nguoi_dung = nv2.ma_nguoi_dung AND nv2.thu_tu_nguyen_vong = 2
            LEFT JOIN truong_thpt t2 ON nv2.ma_truong = t2.ma_truong
            
            LEFT JOIN nguyen_vong nv3 ON ts.ma_nguoi_dung = nv3.ma_nguoi_dung AND nv3.thu_tu_nguyen_vong = 3
            LEFT JOIN truong_thpt t3 ON nv3.ma_truong = t3.ma_truong
            
            LEFT JOIN diem_thi_tuyen_sinh dt ON ts.ma_nguoi_dung = dt.ma_nguoi_dung
            
            WHERE ts.truong_thcs = :truong_thcs
            ORDER BY ts.so_bao_danh
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['truong_thcs' => $truong_thcs]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách thí sinh ĐÃ XÁC NHẬN nhưng CHƯA CÓ LỚP (theo tổ hợp)
    public function getThiSinhChoXepLop($ma_truong, $ma_to_hop) {
        // Cần join bảng phieu_dang_ky_nhap_hoc để biết thí sinh chọn tổ hợp nào
        // Lưu ý: Lúc nãy bên phía HS mình chưa lưu ma_to_hop vào phiếu, giờ cần sửa lại
        // Hoặc tạm thời lấy ma_to_hop từ tham số truyền vào để lọc
        
        // GIẢ ĐỊNH: Bạn đã update bảng phieu_dang_ky_nhap_hoc có cột ma_to_hop_mon (nếu chưa có thì phải thêm)
        // Nếu chưa có cột đó, ta sẽ join qua ket_qua_thi_tuyen_sinh
        
        $sql = "SELECT dts.ma_nguoi_dung, ts.so_bao_danh, nd.ho_ten, kq.tong_diem
                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                -- Join phiếu đăng ký để lấy nguyện vọng tổ hợp (nếu có lưu)
                -- Ở đây mình giả sử logic lọc theo điểm cao nhất trước
                WHERE kq.ma_truong_trung_tuyen = ? 
                AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
                AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)
                ORDER BY kq.tong_diem DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_truong]);
        return $stmt->fetchAll();
    }



    // Lấy danh sách học sinh ĐÃ XÁC NHẬN, CHƯA CÓ LỚP của một trường cụ thể
    // (Kèm theo thông tin tổ hợp môn nếu có)
    // --- API 11: Lấy danh sách học sinh chờ xếp lớp (Đã xác nhận + Chưa có lớp) ---
    // --- SỬA LẠI: Bỏ phần lấy tên tổ hợp để tránh lỗi thiếu cột DB ---
    // public function getHocSinhChoXepLopByTruong($ma_truong) {
    //     $sql = "SELECT 
    //                 dts.ma_nguoi_dung, 
    //                 ts.so_bao_danh, 
    //                 nd.ho_ten, 
    //                 nd.ngay_sinh, 
    //                 kq.tong_diem,
    //                 -- Thay vì lấy từ DB, ta trả về text mặc định để không lỗi
    //                 'Chưa ghi nhận' as ten_to_hop 
    //             FROM ket_qua_thi_tuyen_sinh kq
    //             JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
    //             JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
    //             JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                
    //             -- BỎ ĐOẠN JOIN NÀY ĐI VÌ BẢNG CHƯA CÓ CỘT
    //             -- LEFT JOIN phieu_dang_ky_nhap_hoc pdk ON dts.ma_nguoi_dung = pdk.ma_nguoi_dung 
    //             -- LEFT JOIN to_hop_mon thm ON pdk.ma_to_hop_mon = thm.ma_to_hop_mon

    //             WHERE kq.ma_truong_trung_tuyen = ? 
    //             AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
    //             AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)
                
    //             ORDER BY kq.tong_diem DESC";
                
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([$ma_truong]);
    //     return $stmt->fetchAll();
    // }
    // --- API 11: Lấy danh sách học sinh chờ xếp lớp (CÓ GROUP BY ĐỂ CHỐNG TRÙNG LẶP) ---
    public function getHocSinhChoXepLopByTruong($ma_truong) {
        $sql = "SELECT 
                    dts.ma_nguoi_dung, 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    nd.ngay_sinh, 
                    kq.tong_diem,
                    
                    -- Dùng hàm MAX để lấy 1 giá trị đại diện (tránh lỗi SQL strict mode)
                    COALESCE(MAX(thm.ten_to_hop), 'Chưa ghi nhận') as ten_to_hop,
                    MAX(pdk.ma_to_hop_mon) as ma_to_hop_mon
                    
                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                
                -- Join bảng phiếu để lấy tổ hợp môn
                LEFT JOIN phieu_dang_ky_nhap_hoc pdk ON dts.ma_nguoi_dung = pdk.ma_nguoi_dung 
                LEFT JOIN to_hop_mon thm ON pdk.ma_to_hop_mon = thm.ma_to_hop_mon

                WHERE kq.ma_truong_trung_tuyen = ? 
                AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
                -- Chỉ lấy em nào chưa có trong bảng hoc_sinh
                AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)
                
                -- QUAN TRỌNG: Dòng này giúp gộp các dòng trùng lặp lại thành 1
                GROUP BY dts.ma_nguoi_dung, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh, kq.tong_diem
                
                ORDER BY kq.tong_diem DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_truong]);
        return $stmt->fetchAll();
    }

    // --- API 12: Thực hiện xếp lớp (Insert vào bảng hoc_sinh) ---
    // public function thucHienXepLop($ma_lop, $danh_sach_ma_hoc_sinh) {
    //     $this->db->beginTransaction();
    //     try {
    //         // 1. Insert vào bảng hoc_sinh
    //         $stmt = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ma_lop, ngay_nhap_hoc, trang_thai) VALUES (?, ?, CURDATE(), 'DangHoc')");
            
    //         $count = 0;
    //         foreach ($danh_sach_ma_hoc_sinh as $ma_hs) {
    //             // Kiểm tra lại lần cuối xem đã có trong bảng học sinh chưa để tránh duplicate
    //             $check = $this->db->query("SELECT 1 FROM hoc_sinh WHERE ma_hoc_sinh = $ma_hs")->fetch();
    //             if (!$check) {
    //                 $stmt->execute([$ma_hs, $ma_lop]);
    //                 $count++;
    //             }
    //         }

    //         // 2. Cập nhật sĩ số lớp
    //         // Đếm lại thực tế trong bảng hoc_sinh để update cho chuẩn
    //         $stmtCount = $this->db->prepare("UPDATE lop_hoc SET si_so = (SELECT COUNT(*) FROM hoc_sinh WHERE ma_lop = ?) WHERE ma_lop = ?");
    //         $stmtCount->execute([$ma_lop, $ma_lop]);

    //         $this->db->commit();
    //         return $count;
    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         throw $e;
    //     }
    // }
    public function thucHienXepLop($ma_lop, $danh_sach_ma_hoc_sinh) {
        $this->db->beginTransaction();
        try {
            // 1. Insert vào bảng hoc_sinh (Code cũ)
            $stmt = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ma_lop, ngay_nhap_hoc, trang_thai) VALUES (?, ?, CURDATE(), 'DangHoc')");
            
            // [NEW] Chuẩn bị câu lệnh update Role
            $stmtUpdateRole = $this->db->prepare("
                UPDATE tai_khoan tk
                JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan
                SET tk.vai_tro = 'HocSinh'
                WHERE nd.ma_nguoi_dung = ?
            ");

            $count = 0;
            foreach ($danh_sach_ma_hoc_sinh as $ma_hs) {
                $check = $this->db->query("SELECT 1 FROM hoc_sinh WHERE ma_hoc_sinh = $ma_hs")->fetch();
                if (!$check) {
                    $stmt->execute([$ma_hs, $ma_lop]); // Insert học sinh
                    $stmtUpdateRole->execute([$ma_hs]); // [NEW] Update role thành HocSinh ngay lập tức
                    $count++;
                }
            }

            // ... (Code update sĩ số cũ giữ nguyên) ...
            $stmtCount = $this->db->prepare("UPDATE lop_hoc SET si_so = (SELECT COUNT(*) FROM hoc_sinh WHERE ma_lop = ?) WHERE ma_lop = ?");
            $stmtCount->execute([$ma_lop, $ma_lop]);

            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function autoPhanLop($ma_truong, $danh_sach_hs_ids) {
        $this->db->beginTransaction();
        try {
            $countSuccess = 0;
            $countFail = 0;

            // 1. Lấy tất cả các lớp của trường, kèm theo thông tin sĩ số và mã tổ hợp
            // Sắp xếp theo sĩ số tăng dần (để ưu tiên xếp vào lớp vắng trước -> cân bằng sĩ số)
            $sqlLop = "SELECT ma_lop, ma_to_hop_mon, si_so, chi_tieu_lop 
                       FROM lop_hoc 
                       WHERE ma_truong = ? AND trang_thai_lop = 'HoatDong' 
                       ORDER BY si_so ASC"; 
            $stmtLop = $this->db->prepare($sqlLop);
            $stmtLop->execute([$ma_truong]);
            $allClasses = $stmtLop->fetchAll();

            // 2. Lấy thông tin tổ hợp của các học sinh được chọn
            // Chuyển mảng IDs thành chuỗi để query
            $idsPlaceholders = implode(',', array_fill(0, count($danh_sach_hs_ids), '?'));
            $sqlHS = "SELECT dts.ma_nguoi_dung, pdk.ma_to_hop_mon 
                      FROM diem_thi_tuyen_sinh dts
                      JOIN phieu_dang_ky_nhap_hoc pdk ON dts.ma_nguoi_dung = pdk.ma_nguoi_dung
                      WHERE dts.ma_nguoi_dung IN ($idsPlaceholders)";
            $stmtHS = $this->db->prepare($sqlHS);
            $stmtHS->execute($danh_sach_hs_ids);
            $students = $stmtHS->fetchAll();

            $stmtInsert = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ma_lop, ngay_nhap_hoc, trang_thai) VALUES (?, ?, CURDATE(), 'DangHoc')");
            $stmtUpdateSiSo = $this->db->prepare("UPDATE lop_hoc SET si_so = si_so + 1 WHERE ma_lop = ?");

            // 3. Duyệt từng học sinh để xếp
            foreach ($students as $hs) {
                $maHS = $hs['ma_nguoi_dung'];
                $maToHopHS = $hs['ma_to_hop_mon'];
                $assigned = false;

                // Tìm lớp phù hợp: Cùng tổ hợp VÀ còn chỗ (si_so < chi_tieu - giả sử chi tiêu lớp là 45 chẳng hạn)
                // Do danh sách lớp đã sort theo sĩ số ASC, nên nó sẽ tự động chọn lớp vắng nhất
                foreach ($allClasses as &$lop) { // Dùng tham chiếu &lop để update sĩ số ảo
                    if ($lop['ma_to_hop_mon'] == $maToHopHS) {
                        // Giả sử max sĩ số là 45 (hoặc lấy từ DB nếu có cột chi_tieu_lop)
                        $maxSiSo = $lop['chi_tieu_lop'] ?? 45; 
                        
                        if ($lop['si_so'] < $maxSiSo) {
                            // Tìm thấy lớp phù hợp!
                            $stmtInsert->execute([$maHS, $lop['ma_lop']]);
                            $stmtUpdateSiSo->execute([$lop['ma_lop']]);
                            
                            // Update sĩ số trong mảng tạm để vòng lặp sau biết
                            $lop['si_so']++; 
                            $assigned = true;
                            $countSuccess++;
                            break; // Xếp xong thì thoát vòng lặp lớp, chuyển sang HS tiếp theo
                        }
                    }
                }

                if (!$assigned) {
                    $countFail++; // Không tìm thấy lớp phù hợp hoặc lớp đã đầy
                }
            }

            $this->db->commit();
            
            $msg = "Đã xếp thành công: $countSuccess học sinh.";
            if ($countFail > 0) $msg .= "\nThất bại: $countFail (Do không có lớp phù hợp hoặc lớp đã đầy).";
            
            return ['success' => true, 'message' => $msg];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }


    // --- API 13: Lấy danh sách học sinh trong lớp (Có đánh dấu Mới) ---
    public function getDsHocSinhTrongLop($ma_lop) {
        // Lấy thông tin học sinh và ngày nhập học
        $sql = "SELECT 
                    hs.ma_hoc_sinh, 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    nd.ngay_sinh, 
                    hs.ngay_nhap_hoc,
                    -- Kiểm tra xem có phải mới nhập học hôm nay không (So sánh ngày nhập học với ngày hiện tại)
                    (hs.ngay_nhap_hoc = CURDATE()) as is_new
                FROM hoc_sinh hs
                JOIN thi_sinh ts ON hs.ma_hoc_sinh = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                WHERE hs.ma_lop = ?
                ORDER BY hs.ngay_nhap_hoc DESC, nd.ho_ten ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop]);
        return $stmt->fetchAll();
    }
}
?>