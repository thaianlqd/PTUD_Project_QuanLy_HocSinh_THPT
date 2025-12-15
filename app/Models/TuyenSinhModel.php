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
    // public function runLocAo($resetAll = true) {
    //     $this->db->beginTransaction();
    //     try {
    //         // 1. Chuẩn bị dữ liệu ban đầu
    //         $da_tuyen = []; // Mảng lưu số lượng đã tuyển của từng trường
    //         $exclude_users = []; // Danh sách thí sinh đã có kết quả chốt (Xác nhận/Từ chối) -> Không xét lại

    //         // Lấy chỉ tiêu tất cả các trường
    //         $chi_tieu = $this->db->query("SELECT ma_truong, chi_tieu_hoc_sinh FROM truong_thpt")->fetchAll(PDO::FETCH_KEY_PAIR);
    //         foreach ($chi_tieu as $tid => $ct) { $da_tuyen[$tid] = 0; }

    //         if ($resetAll) {
    //             // --- CHẾ ĐỘ 1: RESET TOÀN BỘ (Làm mới từ đầu - Xóa sạch) ---
    //             $this->db->exec("DELETE FROM ket_qua_thi_tuyen_sinh");
    //             $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0");
    //         } else {
    //             // --- CHẾ ĐỘ 2: GIỮ LẠI KẾT QUẢ ĐÃ CHỐT ---
                
    //             // B1: Lấy danh sách những người đã có quyết định (Xác nhận nhập học HOẶC Từ chối nhập học)
    //             $sqlConfirmed = "SELECT kq.ma_truong_trung_tuyen, dts.ma_nguoi_dung, kq.trang_thai_xac_nhan
    //                              FROM ket_qua_thi_tuyen_sinh kq
    //                              JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
    //                              WHERE kq.trang_thai_xac_nhan IN ('Xac_nhan_nhap_hoc', 'Tu_choi_nhap_hoc')";
                
    //             $confirmedList = $this->db->query($sqlConfirmed)->fetchAll();

    //             // B2: Duyệt danh sách để cập nhật chỉ tiêu và danh sách loại trừ
    //             foreach ($confirmedList as $item) {
    //                 $tid = $item['ma_truong_trung_tuyen'];
                    
    //                 // Logic quan trọng: Chỉ người "Xác nhận nhập học" mới chiếm 1 slot của trường
    //                 if ($item['trang_thai_xac_nhan'] === 'Xac_nhan_nhap_hoc') {
    //                     if (isset($da_tuyen[$tid])) {
    //                         $da_tuyen[$tid]++; 
    //                     }
    //                 }
                    
    //                 // Cả "Xác nhận" và "Từ chối" đều được đưa vào danh sách loại trừ (để không bị xét tuyển lại)
    //                 $exclude_users[] = $item['ma_nguoi_dung']; 
    //             }

    //             // B3: CHỈ XÓA những dòng CHƯA có kết quả cuối cùng (Chờ xác nhận, Trượt,...)
    //             // Những dòng 'Xac_nhan_nhap_hoc' và 'Tu_choi_nhap_hoc' sẽ ĐƯỢC GIỮ NGUYÊN trong DB
    //             $this->db->exec("DELETE FROM ket_qua_thi_tuyen_sinh WHERE trang_thai_xac_nhan NOT IN ('Xac_nhan_nhap_hoc', 'Tu_choi_nhap_hoc')");
    //         }

    //         // 2. Lấy danh sách thí sinh để xét tuyển (Có sắp xếp Tiêu chí phụ)
    //         // Logic: Tổng điểm cao nhất -> Toán cao nhất -> Văn cao nhất -> Anh cao nhất
    //         $sql = "SELECT dts.ma_diem_thi, dts.ma_nguoi_dung, 
    //                        (dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh) as tong_diem 
    //                 FROM diem_thi_tuyen_sinh dts
    //                 WHERE diem_toan IS NOT NULL AND diem_van IS NOT NULL AND diem_anh IS NOT NULL
    //                 ORDER BY tong_diem DESC, dts.diem_toan DESC, dts.diem_van DESC, dts.diem_anh DESC";
            
    //         $thi_sinh_list = $this->db->query($sql)->fetchAll();

    //         // 3. Chuẩn bị câu lệnh Insert
    //         $stmtInsert = $this->db->prepare("INSERT INTO ket_qua_thi_tuyen_sinh (ma_diem_thi, tong_diem, trang_thai, ma_nguyen_vong_trung_tuyen, ma_truong_trung_tuyen, trang_thai_xac_nhan) VALUES (?, ?, ?, ?, ?, 'Cho_xac_nhan')");
    //         $stmtFail = $this->db->prepare("INSERT INTO ket_qua_thi_tuyen_sinh (ma_diem_thi, tong_diem, trang_thai, trang_thai_xac_nhan) VALUES (?, ?, 'Truot', 'Cho_xac_nhan')");

    //         // 4. Vòng lặp xét tuyển
    //         foreach ($thi_sinh_list as $ts) {
    //             // QUAN TRỌNG: Nếu thí sinh này nằm trong danh sách loại trừ (đã xác nhận hoặc đã từ chối)
    //             // -> Bỏ qua, không xét lại, giữ nguyên kết quả cũ trong DB.
    //             if (!$resetAll && in_array($ts['ma_nguoi_dung'], $exclude_users)) {
    //                 continue; 
    //             }

    //             $passed = false;
    //             // Lấy nguyện vọng của thí sinh
    //             $nvs = $this->db->query("SELECT ma_nguyen_vong, ma_truong FROM nguyen_vong WHERE ma_nguoi_dung = {$ts['ma_nguoi_dung']} ORDER BY thu_tu_nguyen_vong ASC")->fetchAll();
                
    //             foreach ($nvs as $nv) {
    //                 $tid = $nv['ma_truong'];
    //                 // Kiểm tra còn slot không
    //                 if (isset($chi_tieu[$tid]) && $da_tuyen[$tid] < $chi_tieu[$tid]) {
    //                     $da_tuyen[$tid]++; // Chiếm 1 slot
    //                     $stmtInsert->execute([$ts['ma_diem_thi'], $ts['tong_diem'], 'Dau', $nv['ma_nguyen_vong'], $tid]);
    //                     $passed = true;
    //                     break; // Đậu NV này rồi thì thôi các NV sau
    //                 }
    //             }

    //             if (!$passed) {
    //                 // Nếu trượt hết các NV thì ghi nhận là Trượt
    //                 $stmtFail->execute([$ts['ma_diem_thi'], $ts['tong_diem']]);
    //             }
    //         }

    //         // 5. Cập nhật lại số lượng học sinh thực tế vào bảng trường (để Admin theo dõi)
    //         foreach ($da_tuyen as $mt => $sl) {
    //             $this->db->prepare("UPDATE truong_thpt SET so_luong_hoc_sinh = ? WHERE ma_truong = ?")->execute([$sl, $mt]);
    //         }

    //         $this->db->commit();
    //         return true;
    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         throw $e;
    //     }
    // }
    // --- CHỨC NĂNG LỌC ẢO (Đã nâng cấp Reset sạch sẽ) ---
    // public function runLocAo($mode = 'reset') {
    //     // ====================================================================
    //     // A. CHẾ ĐỘ RESET: DỌN DẸP VỀ TRẠNG THÁI BAN ĐẦU
    //     // ====================================================================
    //     if ($mode == 'reset') {
    //         try {
    //             // 1. Tắt kiểm tra khóa ngoại để xóa dữ liệu ràng buộc
    //             $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
    //             $this->db->beginTransaction();

    //             // 2. Trả Role từ 'HocSinh' về lại 'ThiSinh'
    //             $this->db->exec("UPDATE tai_khoan tk JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan JOIN thi_sinh ts ON nd.ma_nguoi_dung = ts.ma_nguoi_dung SET tk.vai_tro = 'ThiSinh' WHERE tk.vai_tro = 'HocSinh'");

    //             // 3. XÓA HỌC SINH KHỐI 10 (SỬA LỖI TRIGGER 1442 Ở ĐÂY)
    //             // Thay vì xóa lồng (DELETE ... WHERE IN SELECT ...), ta tách ra làm 2 bước:
                
    //             // Bước 3a: Lấy danh sách ID các lớp Khối 10 ra trước
    //             $stmtGetLop = $this->db->query("SELECT ma_lop FROM lop_hoc WHERE khoi = 10");
    //             $dsLop10 = $stmtGetLop->fetchAll(PDO::FETCH_COLUMN); // Lấy về mảng [10, 11, 12...]

    //             // Bước 3b: Nếu có lớp 10 thì mới xóa học sinh trong các lớp đó
    //             if (!empty($dsLop10)) {
    //                 $chuoiIDLop = implode(',', $dsLop10); // Biến thành chuỗi "10,11,12"
    //                 // Lúc này câu lệnh xóa chỉ chứa số, không còn dính tới bảng 'lop_hoc' -> Trigger hoạt động OK
    //                 $this->db->exec("DELETE FROM hoc_sinh WHERE ma_lop IN ($chuoiIDLop)");
    //             }

    //             // 4. Reset sĩ số lớp về 0 (Vì đã xóa hết học sinh rồi)
    //             // (Nếu Trigger của bác đã tự trừ sĩ số rồi thì dòng này chạy đè lên cũng không sao, càng chắc chắn)
    //             $this->db->exec("UPDATE lop_hoc SET si_so = 0 WHERE khoi = 10");

    //             // 5. RESET TRẠNG THÁI THI SINH VỀ BAN ĐẦU
    //             // Xóa kết quả đậu, xóa xác nhận, về trạng thái chờ
    //             $sqlReset = "UPDATE thi_sinh SET 
    //                             trang_thai = 'ChoXetTuyen', 
    //                             trang_thai_xac_nhan = 'Chua_Xac_Nhan',
    //                             truong_trung_tuyen = NULL, 
    //                             nguyen_vong_trung_tuyen = NULL";
    //             $this->db->exec($sqlReset);

    //             // 6. Reset chỉ tiêu các trường về 0
    //             $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0");

    //             $this->db->commit();
    //             $this->db->exec("SET FOREIGN_KEY_CHECKS = 1"); // Bật lại khóa ngoại
    //             return ['success' => true, 'message' => 'Đã RESET toàn bộ! Dữ liệu sạch sẽ, không bị lỗi Trigger.'];

    //         } catch (Exception $e) {
    //             $this->db->rollBack();
    //             $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
    //             return ['success' => false, 'message' => 'Lỗi Reset: ' . $e->getMessage()];
    //         }
    //     }

    //     // ====================================================================
    //     // B. CHẾ ĐỘ CHẠY LỌC ẢO (TÍNH ĐIỂM VÀ XẾP ĐẬU/RỚT)
    //     // ====================================================================
        
    //     // (Phần này giữ nguyên logic chuẩn mà chúng ta đã chốt)
    //     $this->db->beginTransaction();
    //     try {
    //         // 1. Reset số lượng đã tuyển tạm thời
    //         $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0");

    //         // 2. Lấy chỉ tiêu và chuẩn bị biến đếm
    //         $truongs = $this->db->query("SELECT ma_truong, chi_tieu_hoc_sinh FROM truong_thpt")->fetchAll(PDO::FETCH_KEY_PAIR);
    //         $countSlot = array_fill_keys(array_keys($truongs), 0);

    //         // 3. Lấy danh sách thí sinh + điểm + nguyện vọng (Sắp xếp điểm cao xuống thấp)
    //         $sqlTS = "SELECT ts.*, dt.diem_toan, dt.diem_van, dt.diem_anh,
    //                          (dt.diem_toan * 2 + dt.diem_van * 2 + dt.diem_anh) as tong_diem,
    //                          t1.ten_truong as ten_nv1, t2.ten_truong as ten_nv2, t3.ten_truong as ten_nv3,
    //                          nv1.ma_truong as ma_nv1, nv2.ma_truong as ma_nv2, nv3.ma_truong as ma_nv3
    //                   FROM thi_sinh ts
    //                   LEFT JOIN diem_thi_tuyen_sinh dt ON ts.ma_nguoi_dung = dt.ma_nguoi_dung
    //                   LEFT JOIN nguyen_vong nv1 ON ts.ma_nguoi_dung = nv1.ma_nguoi_dung AND nv1.thu_tu_nguyen_vong = 1
    //                   LEFT JOIN truong_thpt t1 ON nv1.ma_truong = t1.ma_truong
    //                   LEFT JOIN nguyen_vong nv2 ON ts.ma_nguoi_dung = nv2.ma_nguoi_dung AND nv2.thu_tu_nguyen_vong = 2
    //                   LEFT JOIN truong_thpt t2 ON nv2.ma_truong = t2.ma_truong
    //                   LEFT JOIN nguyen_vong nv3 ON ts.ma_nguoi_dung = nv3.ma_nguoi_dung AND nv3.thu_tu_nguyen_vong = 3
    //                   LEFT JOIN truong_thpt t3 ON nv3.ma_truong = t3.ma_truong
    //                   ORDER BY tong_diem DESC, dt.diem_toan DESC, dt.diem_van DESC";
            
    //         $thiSinhs = $this->db->query($sqlTS)->fetchAll();

    //         $stmtUpdate = $this->db->prepare("UPDATE thi_sinh SET trang_thai = ?, truong_trung_tuyen = ?, nguyen_vong_trung_tuyen = ? WHERE ma_nguoi_dung = ?");
    //         $stmtGetIDTruong = $this->db->prepare("SELECT ma_truong FROM truong_thpt WHERE ten_truong = ?");

    //         // 4. Duyệt từng thí sinh để xét tuyển
    //         foreach ($thiSinhs as $ts) {
    //             // Nếu thí sinh đã xác nhận nhập học từ trước -> Giữ chỗ
    //             if ($ts['trang_thai_xac_nhan'] == 'Xac_nhan_nhap_hoc') {
    //                 $stmtGetIDTruong->execute([$ts['truong_trung_tuyen']]);
    //                 $mid = $stmtGetIDTruong->fetchColumn();
    //                 if ($mid && isset($countSlot[$mid])) $countSlot[$mid]++;
    //                 continue; 
    //             }

    //             // Xét lần lượt NV1, NV2, NV3
    //             $dau = false;
    //             $nvs = [
    //                 ['ma' => $ts['ma_nv1'], 'ten' => $ts['ten_nv1'], 'thu_tu' => 1],
    //                 ['ma' => $ts['ma_nv2'], 'ten' => $ts['ten_nv2'], 'thu_tu' => 2],
    //                 ['ma' => $ts['ma_nv3'], 'ten' => $ts['ten_nv3'], 'thu_tu' => 3]
    //             ];

    //             foreach ($nvs as $nv) {
    //                 $mt = $nv['ma'];
    //                 // Nếu trường tồn tại và còn chỉ tiêu
    //                 if (!empty($mt) && isset($truongs[$mt])) {
    //                     if ($countSlot[$mt] < $truongs[$mt]) {
    //                         // Cập nhật ĐẬU
    //                         $stmtUpdate->execute(['Dau', $nv['ten'], $nv['thu_tu'], $ts['ma_nguoi_dung']]);
    //                         $countSlot[$mt]++;
    //                         $dau = true;
    //                         break; // Đậu rồi thì nghỉ xét tiếp
    //                     }
    //                 }
    //             }

    //             if (!$dau) {
    //                 // Cập nhật TRƯỢT
    //                 $stmtUpdate->execute(['Truot', NULL, NULL, $ts['ma_nguoi_dung']]);
    //             }
    //         }

    //         // 5. Lưu lại số lượng đã tuyển thực tế vào DB
    //         $stmtUpdateSl = $this->db->prepare("UPDATE truong_thpt SET so_luong_hoc_sinh = ? WHERE ma_truong = ?");
    //         foreach ($countSlot as $k => $v) {
    //             $stmtUpdateSl->execute([$v, $k]);
    //         }

    //         $this->db->commit();
    //         return ['success' => true, 'message' => 'Đã chạy lọc ảo thành công!'];

    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         return ['success' => false, 'message' => 'Lỗi Lọc Ảo: ' . $e->getMessage()];
    //     }
    // }

    public function runLocAo($mode = 'reset') {
        // ... (Phần A: CHẾ ĐỘ RESET giữ nguyên như cũ) ...
        if ($mode == 'reset') {
            // ... (Code reset cũ của bạn) ...
            try {
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
                $this->db->beginTransaction();
                // ... các lệnh xóa ...
                $this->db->exec("UPDATE tai_khoan tk JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan JOIN thi_sinh ts ON nd.ma_nguoi_dung = ts.ma_nguoi_dung SET tk.vai_tro = 'ThiSinh' WHERE tk.vai_tro = 'HocSinh'");
                
                // Xóa danh sách học sinh lớp 10
                $lops = $this->db->query("SELECT ma_lop FROM lop_hoc WHERE khoi = 10")->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($lops)) {
                    $chuoiLop = implode(',', $lops);
                    $this->db->exec("DELETE FROM hoc_sinh WHERE ma_lop IN ($chuoiLop)");
                }
                $this->db->exec("UPDATE lop_hoc SET si_so = 0 WHERE khoi = 10");
                $this->db->exec("DELETE FROM ket_qua_thi_tuyen_sinh");

                // Reset bảng thi_sinh
                $sqlReset = "UPDATE thi_sinh SET 
                                trang_thai = 'ChoXetTuyen', 
                                trang_thai_xac_nhan = 'Chua_Xac_Nhan',
                                truong_trung_tuyen = NULL, 
                                nguyen_vong_trung_tuyen = NULL";
                $this->db->exec($sqlReset);
                $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0");

                $this->db->commit();
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
                return ['success' => true, 'message' => 'Đã RESET toàn bộ!'];
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
                return ['success' => false, 'message' => 'Lỗi Reset: ' . $e->getMessage()];
            }
        }

        

        // ====================================================================
        // B. CHẾ ĐỘ CHẠY LỌC ẢO (ĐÃ FIX LOGIC TỪ CHỐI)
        // ====================================================================
        $this->db->beginTransaction();
        try {
            // 1. Reset số lượng tuyển dụng tạm thời
            $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0");

            // 2. Lấy chỉ tiêu
            $truongs = $this->db->query("SELECT ma_truong, chi_tieu_hoc_sinh FROM truong_thpt")->fetchAll(PDO::FETCH_KEY_PAIR);
            $countSlot = array_fill_keys(array_keys($truongs), 0);

            // 3. Lấy danh sách thí sinh (Sắp xếp điểm cao xuống thấp)
            $sqlTS = "SELECT ts.*, dt.diem_toan, dt.diem_van, dt.diem_anh,
                             (dt.diem_toan * 2 + dt.diem_van * 2 + dt.diem_anh) as tong_diem,
                             t1.ten_truong as ten_nv1, t2.ten_truong as ten_nv2, t3.ten_truong as ten_nv3,
                             nv1.ma_truong as ma_nv1, nv2.ma_truong as ma_nv2, nv3.ma_truong as ma_nv3
                      FROM thi_sinh ts
                      LEFT JOIN diem_thi_tuyen_sinh dt ON ts.ma_nguoi_dung = dt.ma_nguoi_dung
                      LEFT JOIN nguyen_vong nv1 ON ts.ma_nguoi_dung = nv1.ma_nguoi_dung AND nv1.thu_tu_nguyen_vong = 1
                      LEFT JOIN truong_thpt t1 ON nv1.ma_truong = t1.ma_truong
                      LEFT JOIN nguyen_vong nv2 ON ts.ma_nguoi_dung = nv2.ma_nguoi_dung AND nv2.thu_tu_nguyen_vong = 2
                      LEFT JOIN truong_thpt t2 ON nv2.ma_truong = t2.ma_truong
                      LEFT JOIN nguyen_vong nv3 ON ts.ma_nguoi_dung = nv3.ma_nguoi_dung AND nv3.thu_tu_nguyen_vong = 3
                      LEFT JOIN truong_thpt t3 ON nv3.ma_truong = t3.ma_truong
                      ORDER BY tong_diem DESC, dt.diem_toan DESC, dt.diem_van DESC";
            
            $thiSinhs = $this->db->query($sqlTS)->fetchAll();

            $stmtUpdate = $this->db->prepare("UPDATE thi_sinh SET trang_thai = ?, truong_trung_tuyen = ?, nguyen_vong_trung_tuyen = ? WHERE ma_nguoi_dung = ?");
            $stmtGetIDTruong = $this->db->prepare("SELECT ma_truong FROM truong_thpt WHERE ten_truong = ?");

            // 4. Vòng lặp xét tuyển
            foreach ($thiSinhs as $ts) {
                
                // --- CASE 1: ĐÃ XÁC NHẬN -> Giữ nguyên, tính 1 slot ---
                if ($ts['trang_thai_xac_nhan'] == 'Xac_nhan_nhap_hoc') {
                    $stmtGetIDTruong->execute([$ts['truong_trung_tuyen']]);
                    $mid = $stmtGetIDTruong->fetchColumn();
                    if ($mid && isset($countSlot[$mid])) {
                        $countSlot[$mid]++;
                    }
                    continue; 
                }

                // --- CASE 2: ĐÃ TỪ CHỐI -> Vẫn cho ĐẬU (để hiện tên) nhưng KHÔNG TÍNH SLOT ---
                if ($ts['trang_thai_xac_nhan'] == 'Tu_choi') {
                    // Update lại trạng thái 'Dau' (để chắc chắn nó hiện trong danh sách)
                    // Giữ nguyên trường cũ, NV cũ
                    $stmtUpdate->execute(['Dau', $ts['truong_trung_tuyen'], $ts['nguyen_vong_trung_tuyen'], $ts['ma_nguoi_dung']]);
                    
                    // QUAN TRỌNG: KHÔNG tăng biến $countSlot
                    // Để dành slot này cho người tiếp theo
                    continue; 
                }

                // --- CASE 3: Các trường hợp còn lại (Chưa xác nhận, Mới...) ---
                $dau = false;
                $nvs = [
                    ['ma' => $ts['ma_nv1'], 'ten' => $ts['ten_nv1'], 'thu_tu' => 1],
                    ['ma' => $ts['ma_nv2'], 'ten' => $ts['ten_nv2'], 'thu_tu' => 2],
                    ['ma' => $ts['ma_nv3'], 'ten' => $ts['ten_nv3'], 'thu_tu' => 3]
                ];

                foreach ($nvs as $nv) {
                    $mt = $nv['ma'];
                    if (!empty($mt) && isset($truongs[$mt])) {
                        // Kiểm tra còn chỉ tiêu không
                        if ($countSlot[$mt] < $truongs[$mt]) {
                            // ĐẬU
                            $stmtUpdate->execute(['Dau', $nv['ten'], $nv['thu_tu'], $ts['ma_nguoi_dung']]);
                            $countSlot[$mt]++;
                            $dau = true;
                            break; 
                        }
                    }
                }

                if (!$dau) {
                    $stmtUpdate->execute(['Truot', NULL, NULL, $ts['ma_nguoi_dung']]);
                }
            }

            // 5. Update số lượng thực tế
            $stmtUpdateSl = $this->db->prepare("UPDATE truong_thpt SET so_luong_hoc_sinh = ? WHERE ma_truong = ?");
            foreach ($countSlot as $k => $v) {
                $stmtUpdateSl->execute([$v, $k]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Đã chạy lọc ảo thành công!'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi Lọc Ảo: ' . $e->getMessage()];
        }
    }

    // --- API: LẤY DANH SÁCH THÍ SINH TRƯỢT ---
    public function getDanhSachTruot() {
        $sql = "SELECT 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    
                    -- Tính tổng điểm
                    (dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh) as tong_diem,
                    
                    -- Lấy tên 3 nguyện vọng để hiển thị xem trượt trường nào
                    t1.ten_truong as nv1,
                    t2.ten_truong as nv2,
                    t3.ten_truong as nv3
                    
                FROM thi_sinh ts
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                
                -- Join lấy tên NV1, NV2, NV3
                LEFT JOIN nguyen_vong nv1 ON ts.ma_nguoi_dung = nv1.ma_nguoi_dung AND nv1.thu_tu_nguyen_vong = 1
                LEFT JOIN truong_thpt t1 ON nv1.ma_truong = t1.ma_truong
                
                LEFT JOIN nguyen_vong nv2 ON ts.ma_nguoi_dung = nv2.ma_nguoi_dung AND nv2.thu_tu_nguyen_vong = 2
                LEFT JOIN truong_thpt t2 ON nv2.ma_truong = t2.ma_truong
                
                LEFT JOIN nguyen_vong nv3 ON ts.ma_nguoi_dung = nv3.ma_nguoi_dung AND nv3.thu_tu_nguyen_vong = 3
                LEFT JOIN truong_thpt t3 ON nv3.ma_truong = t3.ma_truong
                
                -- ĐIỀU KIỆN QUAN TRỌNG: CHỈ LẤY NGƯỜI TRƯỢT
                WHERE ts.trang_thai = 'Truot'
                
                ORDER BY tong_diem DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    // --- API: TÍNH ĐIỂM CHUẨN CÁC TRƯỜNG (Để hiển thị bảng bên trên) ---
    public function getBangDiemChuan() {
        // Logic: Điểm chuẩn = Điểm thấp nhất của thí sinh ĐẬU vào trường đó
        $sql = "SELECT 
                    tt.ten_truong,
                    tt.chi_tieu_hoc_sinh,
                    tt.so_luong_hoc_sinh as da_tuyen,
                    
                    -- Tìm điểm min của những người đậu
                    COALESCE(MIN(dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh), 0) as diem_chuan
                    
                FROM truong_thpt tt
                -- Join với những thí sinh ĐẬU vào trường này
                LEFT JOIN thi_sinh ts ON ts.truong_trung_tuyen = tt.ten_truong AND ts.trang_thai = 'Dau'
                LEFT JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                
                GROUP BY tt.ma_truong, tt.ten_truong
                ORDER BY diem_chuan DESC";
                
        return $this->db->query($sql)->fetchAll();
    }
    
    // --- API 6: LẤY KẾT QUẢ LỌC ẢO (ĐÃ CẬP NHẬT LẤY NV1, NV2, NV3) ---
    // public function getKetQuaLoc() {
    //     // SQL này sẽ nối thêm 3 lần vào bảng nguyện vọng để lấy tên trường NV1, NV2, NV3
    //     $sql = "SELECT 
    //                 ts.so_bao_danh, 
    //                 nd.ho_ten, 
    //                 kq.tong_diem, 
    //                 kq.trang_thai, 
    //                 kq.trang_thai_xac_nhan, 
    //                 COALESCE(t_trung.ten_truong, '---') as truong_trung_tuyen,
                    
    //                 -- Lấy tên trường NV1
    //                 COALESCE(t1.ten_truong, '---') AS ten_truong_nv1,
                    
    //                 -- Lấy tên trường NV2
    //                 COALESCE(t2.ten_truong, '---') AS ten_truong_nv2,
                    
    //                 -- Lấy tên trường NV3
    //                 COALESCE(t3.ten_truong, '---') AS ten_truong_nv3

    //             FROM ket_qua_thi_tuyen_sinh kq
    //             JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
    //             JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
    //             JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                
    //             -- Join để lấy tên trường trúng tuyển (nếu có)
    //             LEFT JOIN truong_thpt t_trung ON kq.ma_truong_trung_tuyen = t_trung.ma_truong
                
    //             -- Join lấy NV1
    //             LEFT JOIN nguyen_vong nv1 ON ts.ma_nguoi_dung = nv1.ma_nguoi_dung AND nv1.thu_tu_nguyen_vong = 1
    //             LEFT JOIN truong_thpt t1 ON nv1.ma_truong = t1.ma_truong
                
    //             -- Join lấy NV2
    //             LEFT JOIN nguyen_vong nv2 ON ts.ma_nguoi_dung = nv2.ma_nguoi_dung AND nv2.thu_tu_nguyen_vong = 2
    //             LEFT JOIN truong_thpt t2 ON nv2.ma_truong = t2.ma_truong
                
    //             -- Join lấy NV3
    //             LEFT JOIN nguyen_vong nv3 ON ts.ma_nguoi_dung = nv3.ma_nguoi_dung AND nv3.thu_tu_nguyen_vong = 3
    //             LEFT JOIN truong_thpt t3 ON nv3.ma_truong = t3.ma_truong

    //             ORDER BY kq.tong_diem DESC";
        
    //     return $this->db->query($sql)->fetchAll();
    // }
    // --- API: LẤY KẾT QUẢ HIỂN THỊ RA BẢNG ---
    public function getKetQuaLoc() {
        // Code này lấy trực tiếp từ bảng thi_sinh (nơi vừa được update Đậu/Rớt)
        // Không phụ thuộc vào bảng ket_qua_thi_tuyen_sinh nữa
        
        $sql = "SELECT 
                    ts.ma_nguoi_dung,
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    nd.ngay_sinh, 
                    
                    -- Tính lại tổng điểm để hiển thị
                    (dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh) as tong_diem,
                    
                    -- Lấy trạng thái trực tiếp từ bảng thi_sinh
                    ts.trang_thai, 
                    ts.trang_thai_xac_nhan, 
                    
                    -- Lấy tên trường trúng tuyển (Nếu null thì hiện '---')
                    COALESCE(t_trung.ten_truong, '---') as truong_trung_tuyen,
                    ts.truong_trung_tuyen as ten_truong_trung_tuyen_text
                    
                FROM thi_sinh ts
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                
                -- Join lấy thông tin trường trúng tuyển dựa vào tên trường đã lưu
                LEFT JOIN truong_thpt t_trung ON ts.truong_trung_tuyen = t_trung.ten_truong
                
                -- Chỉ lấy những người ĐẬU (Nếu bác muốn hiện cả rớt thì bỏ dòng WHERE này)
                WHERE ts.trang_thai = 'Dau' 
                
                ORDER BY tong_diem DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    // --- API 4: CHỐT DANH SÁCH ---
    public function getDsLopKhoi10() {
        // THÊM: , si_so vào dòng SELECT
        return $this->db->query("SELECT ma_lop, ten_lop, ma_truong, khoi, ma_to_hop_mon, si_so FROM lop_hoc WHERE khoi = 10 AND trang_thai_lop = 'HoatDong'")->fetchAll();
    }

    // public function getThiSinhTrungTuyenTheoTruong($ma_truong) {
    //     $sql = "SELECT kq.ma_ket_qua_tuyen_sinh, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh, kq.tong_diem, kq.trang_thai_xac_nhan
    //             FROM ket_qua_thi_tuyen_sinh kq
    //             JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
    //             JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
    //             JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
    //             WHERE kq.ma_truong_trung_tuyen = ? AND kq.trang_thai = 'Dau'
    //             ORDER BY kq.tong_diem DESC";
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([$ma_truong]);
    //     return $stmt->fetchAll();
    // }
    public function getThiSinhTrungTuyenTheoTruong($ma_truong) {
        // Lấy tên trường trước (vì bảng thi_sinh lưu tên trường chứ không lưu mã)
        $stmtName = $this->db->prepare("SELECT ten_truong FROM truong_thpt WHERE ma_truong = ?");
        $stmtName->execute([$ma_truong]);
        $tenTruong = $stmtName->fetchColumn();

        if (!$tenTruong) return [];

        $sql = "SELECT 
                    ts.ma_nguoi_dung as ma_ket_qua_tuyen_sinh, -- Dùng tạm ID người dùng làm ID kết quả
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    nd.ngay_sinh, 
                    (dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh) as tong_diem, 
                    ts.trang_thai_xac_nhan
                FROM thi_sinh ts
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                WHERE ts.truong_trung_tuyen = ? AND ts.trang_thai = 'Dau'
                ORDER BY tong_diem DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenTruong]);
        return $stmt->fetchAll();
    }

    // public function updateTrangThaiXacNhanBatch($data, $ma_truong) {
    //     $sql = "UPDATE ket_qua_thi_tuyen_sinh SET trang_thai_xac_nhan = ? WHERE ma_ket_qua_tuyen_sinh = ? AND ma_truong_trung_tuyen = ?";
    //     $stmt = $this->db->prepare($sql);
    //     foreach ($data as $item) {
    //         $stmt->execute([$item['trang_thai'], $item['ma_ket_qua'], $ma_truong]);
    //     }
    //     return true;
    // }
    public function updateTrangThaiXacNhanBatch($data, $ma_truong) {
        // Lưu ý: $item['ma_ket_qua'] lúc này chính là ma_nguoi_dung (do hàm trên trả về)
        $sql = "UPDATE thi_sinh SET trang_thai_xac_nhan = ? WHERE ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $item) {
            $stmt->execute([$item['trang_thai'], $item['ma_ket_qua']]);
        }
        return true;
    }

    // public function chotNhapHoc($ma_truong, $ma_lop_dich) {
    //     $this->db->beginTransaction();
    //     try {
    //         // Lấy thí sinh đã XÁC NHẬN nhập học
    //         $sql = "SELECT dts.ma_nguoi_dung 
    //                 FROM ket_qua_thi_tuyen_sinh kq
    //                 JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
    //                 WHERE kq.ma_truong_trung_tuyen = ? AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
    //                 AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)";
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_truong]);
    //         $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

    //         $stmtIns = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ngay_nhap_hoc, trang_thai, ma_lop) VALUES (?, CURDATE(), 'DangHoc', ?)");
    //         $count = 0;
    //         foreach ($users as $uid) {
    //             $stmtIns->execute([$uid, $ma_lop_dich]);
    //             $count++;
    //         }
            
    //         // Cập nhật lại sĩ số lớp
    //          $this->db->prepare("UPDATE lop_hoc SET si_so = (SELECT COUNT(*) FROM hoc_sinh WHERE ma_lop = ?) WHERE ma_lop = ?")->execute([$ma_lop_dich, $ma_lop_dich]);

    //         $this->db->commit();
    //         return $count;
    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         throw $e;
    //     }
    // }
    public function chotNhapHoc($ma_truong, $ma_lop_dich) {
        // B1: Lấy tên trường từ mã trường (Vì bảng thi_sinh lưu Tên trường)
        $stmtName = $this->db->prepare("SELECT ten_truong FROM truong_thpt WHERE ma_truong = ?");
        $stmtName->execute([$ma_truong]);
        $tenTruong = $stmtName->fetchColumn();

        if (!$tenTruong) return 0; // Không tìm thấy trường thì dừng

        $this->db->beginTransaction();
        try {
            // B2: Lấy danh sách thí sinh ĐÃ XÁC NHẬN nhập học vào trường này
            // Điều kiện: Chưa có tên trong bảng hoc_sinh (tránh trùng lặp)
            $sql = "SELECT ma_nguoi_dung 
                    FROM thi_sinh 
                    WHERE truong_trung_tuyen = ? 
                    AND trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
                    AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = thi_sinh.ma_nguoi_dung)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenTruong]);
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // B3: Chuẩn bị câu lệnh Insert học sinh và Update Role
            $stmtIns = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ngay_nhap_hoc, trang_thai, ma_lop) VALUES (?, CURDATE(), 'DangHoc', ?)");
            
            $stmtRole = $this->db->prepare("UPDATE tai_khoan tk 
                                            JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan 
                                            SET tk.vai_tro = 'HocSinh' 
                                            WHERE nd.ma_nguoi_dung = ?");

            $count = 0;
            foreach ($users as $uid) {
                // Thêm vào bảng học sinh
                $stmtIns->execute([$uid, $ma_lop_dich]);
                // Đổi quyền đăng nhập thành Học sinh
                $stmtRole->execute([$uid]);
                $count++;
            }
            
            // B4: CẬP NHẬT SĨ SỐ CHUẨN XÁC (FIX LỖI ĐẾM SAI)
            // Thay vì cộng dồn, ta đếm trực tiếp số lượng thực tế trong bảng hoc_sinh
            $sqlFixSiSo = "UPDATE lop_hoc 
                           SET si_so = (SELECT COUNT(*) FROM hoc_sinh WHERE ma_lop = ?) 
                           WHERE ma_lop = ?";
            
            $this->db->prepare($sqlFixSiSo)->execute([$ma_lop_dich, $ma_lop_dich]);

            $this->db->commit();
            return $count; // Trả về số lượng học sinh vừa thêm mới

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
    // --- API 11: Lấy danh sách học sinh chờ xếp lớp (CÓ GROUP BY ĐỂ CHỐNG TRÙNG LẶP) ---
    // public function getHocSinhChoXepLopByTruong($ma_truong) {
    //     $sql = "SELECT 
    //                 dts.ma_nguoi_dung, 
    //                 ts.so_bao_danh, 
    //                 nd.ho_ten, 
    //                 nd.ngay_sinh, 
    //                 kq.tong_diem,
                    
    //                 -- Dùng hàm MAX để lấy 1 giá trị đại diện (tránh lỗi SQL strict mode)
    //                 COALESCE(MAX(thm.ten_to_hop), 'Chưa ghi nhận') as ten_to_hop,
    //                 MAX(pdk.ma_to_hop_mon) as ma_to_hop_mon
                    
    //             FROM ket_qua_thi_tuyen_sinh kq
    //             JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
    //             JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
    //             JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                
    //             -- Join bảng phiếu để lấy tổ hợp môn
    //             LEFT JOIN phieu_dang_ky_nhap_hoc pdk ON dts.ma_nguoi_dung = pdk.ma_nguoi_dung 
    //             LEFT JOIN to_hop_mon thm ON pdk.ma_to_hop_mon = thm.ma_to_hop_mon

    //             WHERE kq.ma_truong_trung_tuyen = ? 
    //             AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
    //             -- Chỉ lấy em nào chưa có trong bảng hoc_sinh
    //             AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)
                
    //             -- QUAN TRỌNG: Dòng này giúp gộp các dòng trùng lặp lại thành 1
    //             GROUP BY dts.ma_nguoi_dung, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh, kq.tong_diem
                
    //             ORDER BY kq.tong_diem DESC";
                
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([$ma_truong]);
    //     return $stmt->fetchAll();
    // }

    public function getHocSinhChoXepLopByTruong($ma_truong) {
        // Lấy tên trường
        $stmtName = $this->db->prepare("SELECT ten_truong FROM truong_thpt WHERE ma_truong = ?");
        $stmtName->execute([$ma_truong]);
        $tenTruong = $stmtName->fetchColumn();

        $sql = "SELECT 
                    dts.ma_nguoi_dung, 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    nd.ngay_sinh, 
                    (dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh) as tong_diem,
                    
                    COALESCE(MAX(thm.ten_to_hop), 'Chưa ghi nhận') as ten_to_hop,
                    MAX(pdk.ma_to_hop_mon) as ma_to_hop_mon
                    
                FROM thi_sinh ts
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                
                LEFT JOIN phieu_dang_ky_nhap_hoc pdk ON dts.ma_nguoi_dung = pdk.ma_nguoi_dung 
                LEFT JOIN to_hop_mon thm ON pdk.ma_to_hop_mon = thm.ma_to_hop_mon

                WHERE ts.truong_trung_tuyen = ? 
                AND ts.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
                -- Chỉ lấy em nào chưa có trong bảng hoc_sinh
                AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)
                
                GROUP BY dts.ma_nguoi_dung, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh
                ORDER BY tong_diem DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenTruong]);
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