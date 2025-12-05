<?php
/**
 * Model xử lý toàn bộ dữ liệu Dashboard + các chức năng của Học Sinh
 * Đã fix hoàn toàn lỗi:
 * - "Hồ sơ học tập chưa hoàn chỉnh"
 * - "Unknown column 'ds.diem_trung_binh_mon'"
 * - Tính điểm trung bình môn tự động (không cần cột diem_trung_binh_mon)
 */
class HocSinhModel {
    private $db;

    public function __construct() {
        $ports = [3307, 3306]; // Thử cả 2 port phổ biến
        $this->db = null;

        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $pdo = new PDO($dsn, 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->exec("SET NAMES 'utf8mb4'");
                $this->db = $pdo;
                break;
            } catch (PDOException $e) {
                continue;
            }
        }

        if (!$this->db) {
            error_log("HocSinhModel: Không thể kết nối CSDL.");
            die("LỖI KẾT NỐI DATABASE. Vui lòng kiểm tra MySQL đang chạy trên port 3306 hoặc 3307.");
        }
    }

    // 1. LẤY THÔNG TIN HỌC SINH CHÍNH (dùng cho Dashboard)
    public function getThongTinHS($user_id) {
        $sql = "SELECT 
                    hs.ma_hoc_sinh,
                    nd.ho_ten,
                    nd.ngay_sinh,
                    l.ten_lop,
                    l.ma_lop,
                    COALESCE(nh.ten_nam_hoc, 'Chưa xác định') AS nien_khoa
                FROM hoc_sinh hs
                LEFT JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                LEFT JOIN nam_hoc nh ON l.ma_nam_hoc = nh.ma_nam_hoc
                WHERE hs.ma_hoc_sinh = ?
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Lỗi getThongTinHS: " . $e->getMessage());
            return null;
        }
    }

    // 2. HỖ TRỢ: Lấy tên lớp từ mã lớp (dùng khi fallback session)
    public function getTenLopByMaLop($ma_lop) {
        $sql = "SELECT ten_lop, ma_nam_hoc FROM lop_hoc WHERE ma_lop = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // 3. HỖ TRỢ: Lấy niên khóa từ ma_nam_hoc
    public function getNienKhoaByMaNamHoc($ma_nam_hoc) {
        if (!$ma_nam_hoc) return null;
        $sql = "SELECT ten_nam_hoc FROM nam_hoc WHERE ma_nam_hoc = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_nam_hoc]);
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // 4. THỐNG KÊ BÀI TẬP ĐÃ NỘP / CHƯA NỘP
        // 4. THỐNG KÊ BÀI TẬP ĐÃ NỘP / CHƯA NỘP (ĐÃ SỬA 100% LỖI)
    public function getStatsBaiTap($hs_id, $ma_lop) {
        if ($this->db === null) {
            return ['da_nop' => 0, 'chua_nop' => 0];
        }

        try {
            // Tổng số bài tập của lớp
            $sqlTotal = "SELECT COUNT(*) FROM bai_tap WHERE ma_lop = ?";
            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->execute([$ma_lop]);
            $total = (int)$stmtTotal->fetchColumn();

            // Số bài đã nộp của học sinh
            $sqlDone = "SELECT COUNT(*) 
                        FROM bai_nop bn 
                        JOIN bai_tap bt ON bn.ma_bai_tap = bt.ma_bai_tap
                        WHERE bn.ma_nguoi_dung = ? AND bt.ma_lop = ?";
            $stmtDone = $this->db->prepare($sqlDone);
            $stmtDone->execute([$hs_id, $ma_lop]);
            $done = (int)$stmtDone->fetchColumn();

            $chua_nop = max(0, $total - $done);

            return [
                'da_nop'   => $done,
                'chua_nop' => $chua_nop
            ];
        } catch (PDOException $e) {
            error_log("Lỗi getStatsBaiTap: " . $e->getMessage());
            return ['da_nop' => 0, 'chua_nop' => 0];
        }
    }

    // 5. ĐIỂM TRUNG BÌNH MÔN – TỰ ĐỘNG TÍNH (KHÔNG CẦN CỘT diem_trung_binh_mon)
    public function getDiemTrungBinhMon($hs_id) {
        $sql = "
            SELECT 
                mh.ten_mon_hoc,
                ROUND(AVG(ds.diem), 2) AS diem_tb
            FROM diem_so ds
            JOIN mon_hoc mh ON ds.ma_mon_hoc = mh.ma_mon_hoc
            WHERE ds.ma_hoc_sinh = ? 
              AND ds.diem IS NOT NULL 
              AND ds.diem >= 0
            GROUP BY ds.ma_mon_hoc, mh.ten_mon_hoc
            ORDER BY mh.ten_mon_hoc
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$hs_id]);
            
            $result = [];
            foreach ($stmt->fetchAll() as $row) {
                $result[$row['ten_mon_hoc']] = $row['diem_tb'];
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi getDiemTrungBinhMon: " . $e->getMessage());
            return [];
        }
    }

    // 6. ĐIỂM TỔNG KẾT HỌC KỲ
    public function getDiemTongKetHK($hs_id) {
        // Nếu có bảng ket_qua_hoc_tap thì ưu tiên lấy
        $sql = "SELECT diem_tb_hoc_ky FROM ket_qua_hoc_tap WHERE ma_nguoi_dung = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hs_id]);
        $res = $stmt->fetchColumn();

        if ($res !== false && $res !== null) {
            return round($res, 2);
        }

        // Nếu chưa có thì tính trung bình từ các môn
        $diemMon = $this->getDiemTrungBinhMon($hs_id);
        if (empty($diemMon)) return '--';

        $tong = array_sum($diemMon);
        $soMon = count($diemMon);
        return $soMon > 0 ? round($tong / $soMon, 2) : '--';
    }

    // 7. LỊCH HỌC TUẦN (Thời khóa biểu)
    public function getLichHocTuan($ma_lop) {
        $sql = "
            SELECT 
                t.thu,
                t.tiet_bat_dau AS tiet,
                mh.ten_mon_hoc AS mon,
                nd.ho_ten AS gv,
                p.ten_phong AS phong
            FROM tkb_chi_tiet t
            JOIN thoi_khoa_bieu tkb ON t.ma_tkb = tkb.ma_tkb
            JOIN mon_hoc mh ON t.ma_mon = mh.ma_mon_hoc
            LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
            LEFT JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
            LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
            LEFT JOIN phong_hoc p ON t.ma_phong_hoc = p.ma_phong
            WHERE tkb.ma_lop = ? AND tkb.trang_thai = 'ApDung'
            ORDER BY 
                FIELD(t.thu, 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy', 'Chủ nhật'),
                t.tiet_bat_dau
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getLichHocTuan: " . $e->getMessage());
            return [];
        }
    }
}
?>