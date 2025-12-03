<?php
/**
 * BaiTapModel: Xử lý logic bài tập từ phía HỌC SINH
 * (Đã nâng cấp DATETIME, Đồng hồ đếm ngược, Tự chấm điểm)
 * SỬA: Thêm getTrangThaiSauNop; Cải thiện JSON handling.
 */
class BaiTapModel {
    private $db;

    public function __construct() {
        // Danh sách các port cần thử (Ưu tiên 3307 trước, nếu lỗi thì thử 3306)
        $ports = [3307, 3306]; 
        $connected = false;

        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                
                // Cấu hình PDO chuẩn
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // QUAN TRỌNG: Để true để dùng lại được tham số (ví dụ :username dùng 3 lần)
                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); 
                
                $this->db->exec("SET NAMES 'utf8mb4'");
                
                $connected = true;
                break; 
            } catch (PDOException $e) {
                continue;
            }
        }

        if (!$connected) {
            die("Lỗi: Không thể kết nối CSDL (Port 3306/3307). Kiểm tra XAMPP.");
        }
    }

    /**
     * Lấy mã lớp của học sinh
     */
    private function getMaLop($ma_hoc_sinh) {
        $stmt = $this->db->prepare("SELECT ma_lop FROM hoc_sinh WHERE ma_hoc_sinh = ?");
        $stmt->execute([$ma_hoc_sinh]);
        return $stmt->fetchColumn();
    }

    /**
     * Lấy danh sách bài tập cho học sinh (ĐÃ NÂNG CẤP)
     */
    public function getDanhSachBaiTap($ma_hoc_sinh) {
        if ($this->db === null) return [];
        $ma_lop = $this->getMaLop($ma_hoc_sinh);
        if (!$ma_lop) return [];

        // Câu SQL này lấy TẤT CẢ bài tập của lớp
        // và dùng LEFT JOIN + CASE để xác định trạng thái
        $sql = "SELECT
                    bt.ma_bai_tap,
                    bt.ten_bai_tap,
                    bt.mo_ta,
                    bt.han_nop, -- Giờ là DATETIME
                    bt.ngay_giao,
                    bt.loai_bai_tap,
                    bt.file_dinh_kem,
                    mh.ten_mon_hoc,
                    
                    -- Logic trạng thái phức tạp
                    (CASE
                        WHEN bn.trang_thai = 'HoanThanh' THEN CONCAT('Hoàn Thành (', bn.diem_so, 'đ)')
                        WHEN bn.trang_thai = 'DaNop' THEN 'Đã Nộp (Chờ chấm)'
                        WHEN bt.han_nop < NOW() THEN 'Quá Hạn'
                        ELSE 'Chưa Làm'
                    END) AS trang_thai_final
                    
                FROM bai_tap bt
                JOIN mon_hoc mh ON bt.ma_mon_hoc = mh.ma_mon_hoc
                LEFT JOIN bai_nop bn ON bt.ma_bai_tap = bn.ma_bai_tap AND bn.ma_nguoi_dung = :ma_hs
                WHERE bt.ma_lop = :ma_lop
                ORDER BY bt.han_nop DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_hs' => $ma_hoc_sinh, ':ma_lop' => $ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachBaiTap: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy chi tiết bài tập (ĐÃ NÂNG CẤP)
     * Lấy thêm thông tin giờ bắt đầu + thời gian làm bài (nếu là trắc nghiệm)
     */
    public function getChiTietBaiTap($ma_bai_tap, $ma_hoc_sinh) {
        if ($this->db === null) return null;
        $ma_lop = $this->getMaLop($ma_hoc_sinh);

        $sql = "SELECT
                    bt.ma_bai_tap, bt.ten_bai_tap, bt.mo_ta, bt.han_nop, bt.ngay_giao, bt.loai_bai_tap, bt.file_dinh_kem,
                    mh.ten_mon_hoc,
                    -- Lấy nội dung chi tiết từ bảng con
                    bttn.danh_sach_cau_hoi, 
                    bttn.thoi_gian_lam_bai,
                    btl.de_bai_chi_tiet,
                    -- Lấy thông tin bài nộp (nếu có)
                    bn.ma_bai_nop,
                    bn.gio_bat_dau_lam_bai -- (Cột mới cho đồng hồ)
                FROM bai_tap bt
                JOIN mon_hoc mh ON bt.ma_mon_hoc = mh.ma_mon_hoc
                LEFT JOIN bai_tap_trac_nghiem bttn ON bt.ma_bai_tap = bttn.ma_bai_tap
                LEFT JOIN bai_tap_tu_luan btl ON bt.ma_bai_tap = btl.ma_bai_tap
                LEFT JOIN bai_nop bn ON bt.ma_bai_tap = bn.ma_bai_tap AND bn.ma_nguoi_dung = :ma_hs
                WHERE bt.ma_bai_tap = :ma_bt AND bt.ma_lop = :ma_lop";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_bt' => $ma_bai_tap, ':ma_hs' => $ma_hoc_sinh, ':ma_lop' => $ma_lop]);
            $result = $stmt->fetch();

            if (!$result) return null; // Không tìm thấy

            // Gộp mô tả/nội dung
            $result['content'] = $result['de_bai_chi_tiet'] ?? $result['danh_sach_cau_hoi'] ?? $result['mo_ta'];
            $result['da_nop'] = ($result['ma_bai_nop'] !== null);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi getChiTietBaiTap: " . $e->getMessage());
            return null;
        }
    }

    /**
     * HÀM MỚI: Bắt đầu làm bài (Khởi động đồng hồ)
     * Tạo một bản nháp bài nộp với giờ bắt đầu
     */
    public function batDauLamBai($ma_bai_tap, $ma_hoc_sinh) {
        $sql = "INSERT INTO bai_nop (ma_bai_tap, ma_nguoi_dung, trang_thai, gio_bat_dau_lam_bai)
                VALUES (?, ?, 'DaNop', NOW())
                ON DUPLICATE KEY UPDATE
                    gio_bat_dau_lam_bai = IF(gio_bat_dau_lam_bai IS NULL, NOW(), gio_bat_dau_lam_bai)";
        try {
            $this->db->prepare($sql)->execute([$ma_bai_tap, $ma_hoc_sinh]);
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi batDauLamBai: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy chi tiết bài ĐÃ NỘP (Dùng cho modal xem lại)
     */
    public function getBaiNopChiTiet($ma_bai_tap, $ma_hoc_sinh) {
        $sql = "SELECT 
                    ma_bai_nop, ngay_nop, trang_thai, diem_so, file_dinh_kem, noi_dung_tra_loi, gio_bat_dau_lam_bai 
                FROM bai_nop 
                WHERE ma_bai_tap = ? AND ma_nguoi_dung = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_bai_tap, $ma_hoc_sinh]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getBaiNopChiTiet: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Hủy bài nộp (Xóa khỏi bảng bai_nop)
     */
    public function huyBaiNop($ma_bai_tap, $ma_hoc_sinh) {
        $sql = "DELETE FROM bai_nop 
                WHERE ma_bai_tap = ? AND ma_nguoi_dung = ? AND trang_thai <> 'HoanThanh'";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_bai_tap, $ma_hoc_sinh]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Lỗi huyBaiNop: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy trạng thái công khai (dùng sau khi Hủy bài)
     */
    public function getTrangThaiBaiNopPublic($ma_bai_tap, $ma_hoc_sinh) {
         // Phải lấy lại từ DB
        $stmt_bt = $this->db->prepare("SELECT han_nop FROM bai_tap WHERE ma_bai_tap = ?");
        $stmt_bt->execute([$ma_bai_tap]);
        $han_nop = $stmt_bt->fetchColumn();
        
        if ($han_nop && (strtotime($han_nop) < time()) ) {
            return "Quá Hạn";
        }
        return "Chưa Làm";
    }

    /**
     * HÀM MỚI: Lấy trạng thái sau khi nộp bài (FIX LỖI CSDL)
     */
    public function getTrangThaiSauNop($ma_bai_tap, $ma_hoc_sinh) {
        if ($this->db === null) return 'Lỗi CSDL';
        $ma_lop = $this->getMaLop($ma_hoc_sinh);
        if (!$ma_lop) return 'Lỗi lớp';

        $sql = "SELECT
                    (CASE
                        WHEN bn.trang_thai = 'HoanThanh' THEN CONCAT('Hoàn Thành (', bn.diem_so, 'đ)')
                        WHEN bn.trang_thai = 'DaNop' THEN 'Đã Nộp (Chờ chấm)'
                        WHEN bt.han_nop < NOW() THEN 'Quá Hạn'
                        ELSE 'Chưa Làm'
                    END) AS trang_thai_final
                FROM bai_tap bt
                LEFT JOIN bai_nop bn ON bt.ma_bai_tap = bn.ma_bai_tap AND bn.ma_nguoi_dung = :ma_hs
                WHERE bt.ma_bai_tap = :ma_bt AND bt.ma_lop = :ma_lop";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            // FIX: Thêm :ma_lop vào mảng execute
            $stmt->execute([
                ':ma_hs' => $ma_hoc_sinh, 
                ':ma_bt' => $ma_bai_tap,
                ':ma_lop' => $ma_lop // <-- ĐÃ THÊM DÒNG NÀY
            ]);
            
            $result = $stmt->fetchColumn();
            return $result ?: 'Chưa Làm';
        } catch (PDOException $e) {
            error_log("Lỗi getTrangThaiSauNop: " . $e->getMessage());
            return 'Lỗi CSDL';
        }
    }

    /**
     * Lưu bài tự luận (gõ hoặc upload)
     */
    public function luuBaiNopTuLuan($ma_bai_tap, $ma_hoc_sinh, $noi_dung, $file_path) {
        // Kiểm tra quá hạn (DATETIME)
        $stmt_han = $this->db->prepare("SELECT han_nop FROM bai_tap WHERE ma_bai_tap = ?");
        $stmt_han->execute([$ma_bai_tap]);
        $han_nop = $stmt_han->fetchColumn();
        if ($han_nop && (strtotime($han_nop) < time())) {
            // Đã bị JS khóa, nhưng check lại cho chắc
            return false; 
        }

        $sql = "INSERT INTO bai_nop (ma_bai_tap, ma_nguoi_dung, noi_dung_tra_loi, file_dinh_kem, trang_thai, ngay_nop)
                VALUES (?, ?, ?, ?, 'DaNop', NOW())
                ON DUPLICATE KEY UPDATE
                    noi_dung_tra_loi = VALUES(noi_dung_tra_loi),
                    file_dinh_kem = VALUES(file_dinh_kem),
                    trang_thai = 'DaNop',
                    ngay_nop = NOW()";
        try {
            $this->db->prepare($sql)->execute([$ma_bai_tap, $ma_hoc_sinh, $noi_dung, $file_path]);
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi luuBaiNopTuLuan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * HÀM MỚI: Tự động chấm điểm Trắc nghiệm (SỬA: Cải thiện JSON handling)
     */
    public function luuVaChamDiemTracNghiem($ma_bai_tap, $ma_hoc_sinh, $answersJson) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi CSDL.'];
        
        try {
            // 1. Lấy thông tin bài tập và giờ bắt đầu
            $sql_info = "SELECT 
                            bttn.thoi_gian_lam_bai, 
                            bttn.danh_sach_cau_hoi,
                            bn.gio_bat_dau_lam_bai
                         FROM bai_tap bt
                         JOIN bai_tap_trac_nghiem bttn ON bt.ma_bai_tap = bttn.ma_bai_tap
                         LEFT JOIN bai_nop bn ON bt.ma_bai_tap = bn.ma_bai_tap AND bn.ma_nguoi_dung = ?
                         WHERE bt.ma_bai_tap = ?";
            
            $stmt_info = $this->db->prepare($sql_info);
            $stmt_info->execute([$ma_hoc_sinh, $ma_bai_tap]);
            $info = $stmt_info->fetch();

            if (!$info || !$info['gio_bat_dau_lam_bai']) {
                return ['success' => false, 'message' => 'Lỗi: Không tìm thấy giờ bắt đầu làm bài.'];
            }

            // 2. Kiểm tra thời gian
            $startTime = strtotime($info['gio_bat_dau_lam_bai']);
            $durationSeconds = (int)$info['thoi_gian_lam_bai'] * 60;
            $endTime = $startTime + $durationSeconds;
            $submitTime = time();

            // Cho phép trễ 5 giây (do mạng)
            if ($submitTime > ($endTime + 5)) {
                // Nộp quá giờ -> Vẫn lưu bài nhưng set 0 điểm
                $diem_so = 0;
                $trang_thai_nop = 'HoanThanh'; // Hoàn thành (với 0 điểm)
                $message = "Bạn đã nộp bài quá giờ! Bài làm không được tính điểm.";
            } else {
                // 3. Nộp trong giờ -> Chấm điểm
                $questionsData = null;
                try {
                    $questionsData = json_decode($info['danh_sach_cau_hoi'], true);
                    if (!$questionsData || !isset($questionsData['questions']) || !is_array($questionsData['questions'])) {
                        throw new Exception("Cấu trúc JSON không hợp lệ (thiếu 'questions').");
                    }
                } catch (Exception $e) {
                    error_log("Lỗi JSON questions: " . $e->getMessage());
                    return ['success' => false, 'message' => 'Lỗi: Không thể đọc câu hỏi gốc.'];
                }

                $questions = $questionsData['questions'];
                $userAnswers = json_decode($answersJson, true) ?: [];
                
                $totalQuestions = count($questions);
                $correctCount = 0;

                foreach ($questions as $q) {
                    $questionId = "q" . $q['id'];
                    $correctAnswer = $q['correct']; // "A", "B"...
                    $userAnswer = $userAnswers[$questionId] ?? null; // "A", "B"...
                    
                    if ($userAnswer === $correctAnswer) {
                        $correctCount++;
                    }
                }
                
                // Tính điểm (thang 10)
                $diem_so = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 10, 2) : 0;
                $trang_thai_nop = 'HoanThanh';
                $message = "Chấm bài thành công!";
            }
            
            // 4. Cập nhật bảng bai_nop
            $sql_update = "UPDATE bai_nop 
                           SET noi_dung_tra_loi = ?, 
                               diem_so = ?, 
                               trang_thai = ?, 
                               ngay_nop = NOW()
                           WHERE ma_bai_tap = ? AND ma_nguoi_dung = ?";
            
            $this->db->prepare($sql_update)->execute([
                $answersJson,
                $diem_so,
                $trang_thai_nop,
                $ma_bai_tap,
                $ma_hoc_sinh
            ]);

            return ['success' => true, 'message' => $message, 'diem_so' => $diem_so];
            
        } catch (PDOException $e) {
            error_log("Lỗi luuVaChamDiemTracNghiem: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL khi chấm bài.'];
        } catch (Exception $e) {
             error_log("Lỗi khác khi chấm bài: " . $e->getMessage());
             return ['success' => false, 'message' => 'Lỗi định dạng dữ liệu khi chấm bài.'];
        }
    }

    /**
     * Lưu bài trắc nghiệm (Hàm cũ - không dùng nữa)
     */
    public function luuBaiNopTracNghiem($ma_bai_tap, $ma_hoc_sinh, $answersJson) {
        // Hàm này đã được thay thế bằng luuVaChamDiemTracNghiem
        // Nhưng chúng ta vẫn giữ lại để tương thích với controller cũ (nếu có)
        // và gọi hàm mới
        $result = $this->luuVaChamDiemTracNghiem($ma_bai_tap, $ma_hoc_sinh, $answersJson);
        return $result['success'];
    }
}
?>