<?php
/**
 * BaiTapModel: Xử lý dữ liệu liên quan đến bài tập và bài nộp
 */
class BaiTapModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL (Sửa port/user/pass nếu cần)
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
            $this->db = new PDO($dsn, 'root', '');
            $this->db->exec("SET NAMES 'utf8mb4'"); // Ép kết nối UTF-8
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            $this->db = null;
            die("Không thể kết nối CSDL: " . $e->getMessage());
        }
    }

     /**
     * Public getter for DB connection (Dùng cho helper trong Controller)
     */
    public function getDb() {
        return $this->db;
    }


    /**
     * Lấy mã lớp của học sinh đang đăng nhập
     */
    private function getMaLopHocSinh($ma_hoc_sinh) {
        if ($this->db === null || !$ma_hoc_sinh) return null;
        try {
            $stmt = $this->db->prepare("SELECT ma_lop FROM hoc_sinh WHERE ma_hoc_sinh = ?");
            $stmt->execute([$ma_hoc_sinh]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi getMaLopHocSinh: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách bài tập cho học sinh
     * --- SỬA LỖI MẤT BÀI TẬP (Xóa ma_phan_cong_giao) ---
     */
    public function getDanhSachBaiTap($ma_hoc_sinh) {
        if ($this->db === null || !$ma_hoc_sinh) return [];

        $ma_lop = $this->getMaLopHocSinh($ma_hoc_sinh);
        if (!$ma_lop) {
             error_log("Không tìm thấy mã lớp cho học sinh ID: " . $ma_hoc_sinh);
             return [];
        }

         $sql = "SELECT
                    bt.ma_bai_tap AS id,
                    bt.ten_bai_tap AS name,
                    -- SỬA: Xóa 'AND bt.ma_phan_cong_giao = bpc.ma_phan_cong'
                    (SELECT mh_inner.ten_mon_hoc
                     FROM mon_hoc mh_inner
                     JOIN bang_phan_cong bpc ON mh_inner.ma_mon_hoc = bpc.ma_mon_hoc
                     WHERE bpc.ma_lop = bt.ma_lop
                     -- Giả sử bài tập Toán (dựa trên tên) sẽ lấy môn Toán
                     AND bt.ten_bai_tap LIKE CONCAT('%', mh_inner.ten_mon_hoc, '%')
                     LIMIT 1
                    ) AS subject,
                    bt.ngay_giao AS assignedDate,
                    bt.han_nop AS dueDate,
                    CASE
                        WHEN bn.ma_bai_nop IS NOT NULL THEN
                            CASE bn.trang_thai
                                WHEN 'ChamDiem' THEN 'Chờ Chấm'
                                WHEN 'HoanThanh' THEN IF(bn.ngay_nop > bt.han_nop, 'Hoàn Thành (Trễ)', 'Hoàn Thành')
                                ELSE IF(bn.ngay_nop > bt.han_nop, 'Đã Nộp (Trễ)', 'Đã Nộp')
                            END
                        WHEN bt.han_nop < CURDATE() THEN 'Quá Hạn'
                        ELSE 'Chưa Làm'
                    END AS status,
                    CASE bt.loai_bai_tap
                        WHEN 'TracNghiem' THEN 'multiple-choice'
                        WHEN 'TuLuan' THEN 'essay'
                        WHEN 'UploadFile' THEN 'upload-file'
                        ELSE 'unknown'
                    END as type,
                    bt.mo_ta as content, -- Chỉ lấy mo_ta cho danh sách
                    bt.file_dinh_kem as attachment
                FROM bai_tap bt
                LEFT JOIN bai_nop bn ON bt.ma_bai_tap = bn.ma_bai_tap AND bn.ma_nguoi_dung = :ma_hoc_sinh
                WHERE bt.ma_lop = :ma_lop
                GROUP BY bt.ma_bai_tap
                ORDER BY bt.han_nop DESC, bt.ngay_giao DESC";


        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_hoc_sinh', $ma_hoc_sinh, PDO::PARAM_INT);
            $stmt->bindParam(':ma_lop', $ma_lop, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();

             foreach ($results as &$row) {
                 if ($row['dueDate']) {
                     $row['dueDate'] = $row['dueDate'] . 'T23:59:59';
                 }
                 $row['questions'] = []; // Danh sách không cần câu hỏi
             }
             unset($row);

            return $results;
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachBaiTap: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy chi tiết một bài tập (bao gồm câu hỏi nếu là trắc nghiệm)
     * --- SỬA LỖI MẤT BÀI TẬP (Xóa ma_phan_cong_giao) ---
     */
    public function getChiTietBaiTap($ma_bai_tap, $ma_hoc_sinh) {
         if ($this->db === null || !$ma_bai_tap || !$ma_hoc_sinh) return null;

         // SỬA SQL: Xóa 'AND bt.ma_phan_cong_giao = bpc.ma_phan_cong'
         $sql = "SELECT bt.*,
                    CASE bt.loai_bai_tap
                        WHEN 'TracNghiem' THEN 'multiple-choice'
                        WHEN 'TuLuan' THEN 'essay'
                        WHEN 'UploadFile' THEN 'upload-file'
                        ELSE 'unknown'
                    END as type_js,
                    bttl.de_bai_chi_tiet AS essay_content,
                    bttn.danh_sach_cau_hoi AS mcq_content, -- Lấy trực tiếp JSON thô
                    (SELECT mh_inner.ten_mon_hoc
                     FROM mon_hoc mh_inner
                     JOIN bang_phan_cong bpc ON mh_inner.ma_mon_hoc = bpc.ma_mon_hoc
                     WHERE bpc.ma_lop = bt.ma_lop
                     -- Giả sử bài tập Toán (dựa trên tên) sẽ lấy môn Toán
                     AND bt.ten_bai_tap LIKE CONCAT('%', mh_inner.ten_mon_hoc, '%')
                     LIMIT 1
                    ) AS subject_name
                 FROM bai_tap bt
                 LEFT JOIN bai_tap_tu_luan bttl ON bt.ma_bai_tap = bttl.ma_bai_tap AND bt.loai_bai_tap = 'TuLuan'
                 LEFT JOIN bai_tap_trac_nghiem bttn ON bt.ma_bai_tap = bttn.ma_bai_tap AND bt.loai_bai_tap = 'TracNghiem'
                 WHERE bt.ma_bai_tap = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_bai_tap]);
            $assignment = $stmt->fetch();

            if($assignment) {
                 $assignment['questions'] = []; // Khởi tạo, JS sẽ điền vào
                 $content_to_use = $assignment['mo_ta']; // Mặc định là mô tả chung

                 // --- SỬA LOGIC PARSE JSON (vJavaScript Parse) ---
                 // KHÔNG parse JSON ở đây nữa
                 if ($assignment['type_js'] === 'multiple-choice' && !empty($assignment['mcq_content'])) {
                     // Chỉ gán content thô. JS sẽ chịu trách nhiệm parse
                     $content_to_use = $assignment['mcq_content'];
                 } elseif ($assignment['type_js'] === 'essay' && !empty($assignment['essay_content'])) {
                      $content_to_use = $assignment['essay_content'];
                 }
                 // --- HẾT SỬA LOGIC ---

                 if ($assignment['han_nop']) { $assignment['dueDate'] = $assignment['han_nop'] . 'T23:59:59'; }
                 $assignment['id'] = $assignment['ma_bai_tap'];
                 $assignment['name'] = $assignment['ten_bai_tap'];
                 $assignment['assignedDate'] = $assignment['ngay_giao'];
                 $assignment['type'] = $assignment['type_js'];
                 $assignment['subject'] = $assignment['subject_name'] ?? 'N/A';
                 $assignment['attachment'] = $assignment['file_dinh_kem'];
                 $assignment['content'] = $content_to_use; // Gán content (có thể là JSON thô)

                 $assignment['status'] = $this->getTrangThaiBaiNopInternal($ma_bai_tap, $ma_hoc_sinh, $assignment['han_nop']);

                 unset($assignment['ma_bai_tap'], $assignment['ten_bai_tap'], $assignment['ngay_giao'], $assignment['han_nop'], $assignment['loai_bai_tap'], $assignment['type_js'], $assignment['essay_content'], $assignment['mcq_content'], $assignment['subject_name'], $assignment['file_dinh_kem'], $assignment['mo_ta']);
            }
            return $assignment;

        } catch (PDOException $e) {
             error_log("Lỗi getChiTietBaiTap: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper nội bộ để lấy trạng thái bài nộp
     */
    private function getTrangThaiBaiNopInternal($ma_bai_tap, $ma_hoc_sinh, $han_nop_db) {
        try {
            $sql_status = "SELECT trang_thai, ngay_nop FROM bai_nop WHERE ma_bai_tap = ? AND ma_nguoi_dung = ?";
            $stmt_status = $this->db->prepare($sql_status);
            $stmt_status->execute([$ma_bai_tap, $ma_hoc_sinh]);
            $submission_info = $stmt_status->fetch();

            $status = 'Chưa Làm'; // Mặc định
            $han_nop_ts = $han_nop_db ? strtotime($han_nop_db . ' 23:59:59') : null;

            if ($submission_info) {
                $trang_thai_nop = $submission_info['trang_thai'];
                $ngay_nop = $submission_info['ngay_nop'];
                $is_late = ($han_nop_ts !== null && strtotime($ngay_nop) > $han_nop_ts);

                if ($trang_thai_nop == 'ChamDiem') $status = 'Chờ Chấm';
                elseif ($trang_thai_nop == 'HoanThanh') $status = $is_late ? 'Hoàn Thành (Trễ)' : 'Hoàn Thành';
                else $status = $is_late ? 'Đã Nộp (Trễ)' : 'Đã Nộp';

            } elseif ($han_nop_ts !== null && time() > $han_nop_ts) {
                 $status = 'Quá Hạn';
            }
            return $status;
        } catch (PDOException $e) {
            error_log("Lỗi getTrangThaiBaiNopInternal: " . $e->getMessage());
            return 'Lỗi Status';
        }
    }


    /**
     * Lưu bài nộp dạng Trắc nghiệm
     */
    public function luuBaiNopTracNghiem($ma_bai_tap, $ma_hoc_sinh, $answersJson) {
        if ($this->db === null) return false;
        $noi_dung_tra_loi = $answersJson;
        $ngay_nop = date('Y-m-d H:i:s');
        $sql = "INSERT INTO bai_nop (ma_bai_tap, ma_nguoi_dung, ngay_nop, trang_thai, noi_dung_tra_loi, lan_nop)
                VALUES (:ma_bai_tap, :ma_nguoi_dung, :ngay_nop, :trang_thai, :noi_dung, 1)
                ON DUPLICATE KEY UPDATE
                ngay_nop = VALUES(ngay_nop),
                trang_thai = VALUES(trang_thai),
                noi_dung_tra_loi = VALUES(noi_dung_tra_loi),
                lan_nop = lan_nop + 1";
        try {
            $trang_thai = 'DaNop';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_bai_tap', $ma_bai_tap, PDO::PARAM_INT);
            $stmt->bindParam(':ma_nguoi_dung', $ma_hoc_sinh, PDO::PARAM_INT);
            $stmt->bindParam(':ngay_nop', $ngay_nop);
            $stmt->bindParam(':trang_thai', $trang_thai);
            $stmt->bindParam(':noi_dung', $noi_dung_tra_loi, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Lỗi luuBaiNopTracNghiem: " . $e->getMessage()); return false;
        }
    }

    /**
     * Lưu bài nộp dạng Tự Luận
     */
    public function luuBaiNopTuLuan($ma_bai_tap, $ma_hoc_sinh, $noi_dung = null, $file_path = null) {
        if ($this->db === null) return false;
        $ngay_nop = date('Y-m-d H:i:s');
        $sql = "INSERT INTO bai_nop (ma_bai_tap, ma_nguoi_dung, ngay_nop, trang_thai, noi_dung_tra_loi, file_dinh_kem, lan_nop)
                VALUES (:ma_bai_tap, :ma_nguoi_dung, :ngay_nop, :trang_thai, :noi_dung, :file_kem, 1)
                ON DUPLICATE KEY UPDATE
                ngay_nop = VALUES(ngay_nop),
                trang_thai = VALUES(trang_thai),
                noi_dung_tra_loi = VALUES(noi_dung_tra_loi),
                file_dinh_kem = VALUES(file_dinh_kem),
                lan_nop = lan_nop + 1";
        try {
             $trang_thai = 'DaNop';
             $stmt = $this->db->prepare($sql);
             $stmt->bindParam(':ma_bai_tap', $ma_bai_tap, PDO::PARAM_INT);
             $stmt->bindParam(':ma_nguoi_dung', $ma_hoc_sinh, PDO::PARAM_INT);
             $stmt->bindParam(':ngay_nop', $ngay_nop);
             $stmt->bindParam(':trang_thai', $trang_thai);
             $noi_dung_param = $noi_dung === null ? null : $noi_dung;
             $file_path_param = $file_path === null ? null : $file_path;
             $stmt->bindParam(':noi_dung', $noi_dung_param, PDO::PARAM_STR);
             $stmt->bindParam(':file_kem', $file_path_param, PDO::PARAM_STR);
             return $stmt->execute();
        } catch (PDOException $e) {
             error_log("Lỗi luuBaiNopTuLuan: " . $e->getMessage()); return false;
        }
    }

     /**
     * Lấy trạng thái bài nộp (Public, có thể gọi từ Controller)
     */
    public function getTrangThaiBaiNopPublic($ma_bai_tap, $ma_hoc_sinh) {
        try {
            $stmt_han = $this->db->prepare("SELECT han_nop FROM bai_tap WHERE ma_bai_tap = ?");
            $stmt_han->execute([$ma_bai_tap]);
            $han_nop_db = $stmt_han->fetchColumn();
            return $this->getTrangThaiBaiNopInternal($ma_bai_tap, $ma_hoc_sinh, $han_nop_db);
        } catch (PDOException $e) {
            error_log("Lỗi getTrangThaiBaiNopPublic: " . $e->getMessage());
            return 'Lỗi Status';
        }
    }


    // --- HÀM MỚI ĐỂ XEM LẠI BÀI NỘP ---

    /**
     * Lấy chi tiết bài ĐÃ NỘP của học sinh
     */
    public function getBaiNopChiTiet($ma_bai_tap, $ma_hoc_sinh) {
        if ($this->db === null) return null;
        try {
            $sql = "SELECT 
                        noi_dung_tra_loi, 
                        file_dinh_kem, 
                        ngay_nop, 
                        diem_so, 
                        trang_thai 
                    FROM bai_nop 
                    WHERE ma_bai_tap = ? AND ma_nguoi_dung = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_bai_tap, $ma_hoc_sinh]);
            return $stmt->fetch(); // Trả về thông tin bài nộp
        } catch (PDOException $e) {
            error_log("Lỗi getBaiNopChiTiet: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Hủy/Xóa bài đã nộp của học sinh
     */
    public function huyBaiNop($ma_bai_tap, $ma_hoc_sinh) {
        if ($this->db === null) {
             // Thêm return để dừng hàm nếu $db là null
             error_log("Lỗi huyBaiNop: Kết nối CSDL bị null.");
             return ['success' => false, 'message' => 'Lỗi kết nối CSDL khi hủy bài.'];
        }

        // Bắt đầu transaction
        $this->db->beginTransaction(); // <--- Dòng này hoặc dòng tương tự có thể là dòng 330 theo lỗi báo

        try {
            // 1. Kiểm tra xem bài nộp có tồn tại và thuộc về HS này không + Trạng thái
             $sql_check = "SELECT ma_bai_nop, trang_thai, file_dinh_kem FROM bai_nop WHERE ma_bai_tap = ? AND ma_nguoi_dung = ?";
             $stmt_check = $this->db->prepare($sql_check); // Dùng $this->db
             $stmt_check->execute([$ma_bai_tap, $ma_hoc_sinh]);
             $bai_nop = $stmt_check->fetch();


            if (!$bai_nop) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy bài nộp của bạn cho bài tập này.'];
            }

            // 2. Kiểm tra xem bài đã bị chấm điểm hoặc đang chấm chưa
            if ($bai_nop['trang_thai'] === 'HoanThanh') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Bài tập đã được chấm điểm, không thể hủy.'];
            }
             if ($bai_nop['trang_thai'] === 'ChamDiem') {
                 $this->db->rollBack();
                 return ['success' => false, 'message' => 'Bài tập đang được chấm, không thể hủy.'];
             }

             $file_path = $bai_nop['file_dinh_kem']; // Lấy đường dẫn file từ kết quả check

            // 3. Xóa bài nộp trong CSDL
            $sql_delete = "DELETE FROM bai_nop WHERE ma_bai_nop = ?";
            $stmt_delete = $this->db->prepare($sql_delete); // Dùng $this->db
            $deleted = $stmt_delete->execute([$bai_nop['ma_bai_nop']]);

            if ($deleted) {
                // 4. Nếu xóa CSDL thành công VÀ có file_path, xóa file vật lý
                if ($file_path) {
                    $full_path = '../public/' . $file_path;
                    if (file_exists($full_path)) {
                        @unlink($full_path);
                    }
                }

                // 5. Lấy trạng thái mới (Chưa làm hay Quá hạn)
                $sql_get_assignment = "SELECT han_nop FROM bai_tap WHERE ma_bai_tap = ?";
                $stmt_get_assignment = $this->db->prepare($sql_get_assignment); // Dùng $this->db
                $stmt_get_assignment->execute([$ma_bai_tap]);
                $assignment = $stmt_get_assignment->fetch();
                $newStatus = 'Chưa Làm';
                // Sửa điều kiện kiểm tra DateTime
                if ($assignment && $assignment['han_nop'] && (new DateTime()) > (new DateTime($assignment['han_nop'] . ' 23:59:59'))) {
                    $newStatus = 'Quá Hạn';
                }


                $this->db->commit();
                // Trả về mảng thay vì boolean đơn thuần
                return [
                    'success' => true,
                    'message' => 'Đã hủy bài nộp thành công!', // Thêm message
                    'newStatus' => $newStatus
                ];
            } else {
                $this->db->rollBack();
                 // Trả về mảng lỗi
                 return ['success' => false, 'message' => 'Không thể xóa bài nộp khỏi CSDL.'];
            }

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi PDO khi hủy bài nộp (Model): " . $e->getMessage());
            // Trả về mảng lỗi
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu khi hủy bài nộp.'];
        } catch (Exception $e) {
             $this->db->rollBack();
             error_log("Lỗi khác khi hủy bài nộp (Model): " . $e->getMessage());
              // Trả về mảng lỗi
             return ['success' => false, 'message' => 'Đã xảy ra lỗi không mong muốn.'];
        }
    }
}
?>

