<?php
class GiaoVienChuNhiemModel {
    private $db;

    public function __construct() {
        $ports = [3307, 3306];
        foreach ($ports as $port) {
            try {
                $this->db = new PDO("mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4", 'root', '');
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->db->exec("SET NAMES 'utf8mb4'");
                break;
            } catch (PDOException $e) { continue; }
        }
    }

    public function getLopChuNhiem($ma_giao_vien) {
        if ($this->db === null) return null;
        $sql = "SELECT l.*,
                       (SELECT COUNT(*) FROM hoc_sinh hs WHERE hs.ma_lop = l.ma_lop) AS si_so_thuc
                FROM lop_hoc l
                WHERE l.ma_gvcn = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_giao_vien]);
        return $stmt->fetch();
    }

    /**
     * Danh sách HS + ĐTB HK theo đúng học kỳ/năm của lớp
     */
    public function getDanhSachHocSinh($ma_lop, $ma_hoc_ky = 'HK1') {
        if ($this->db === null) return [];
        $sql = "SELECT 
                    hs.ma_hoc_sinh,
                    nd.ho_ten,
                    kq.diem_tb_hk,
                    kq.hanh_kiem,
                    kq.nhan_xet_gvcn,
                    0 AS so_buoi_vang
                FROM hoc_sinh hs
                INNER JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                INNER JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                LEFT JOIN ket_qua_hoc_tap kq 
                       ON kq.ma_hoc_sinh = hs.ma_hoc_sinh
                      AND kq.ma_hoc_ky   = ?
                      AND kq.ma_nam_hoc  = l.ma_nam_hoc
                WHERE hs.ma_lop = ?
                ORDER BY nd.ho_ten";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hoc_ky, $ma_lop]);
        return $stmt->fetchAll();
    }

    /**
     * Thống kê hạnh kiểm theo đúng học kỳ/năm
     */
    public function getChartHanhKiem($ma_lop, $ma_hoc_ky = 'HK1') {
        $sql = "SELECT kq.hanh_kiem, COUNT(*) AS so_luong
                FROM hoc_sinh hs
                INNER JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                LEFT JOIN ket_qua_hoc_tap kq
                       ON kq.ma_hoc_sinh = hs.ma_hoc_sinh
                      AND kq.ma_hoc_ky   = ?
                      AND kq.ma_nam_hoc  = l.ma_nam_hoc
                WHERE hs.ma_lop = ?
                GROUP BY kq.hanh_kiem";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hoc_ky, $ma_lop]);

        $data = ['Tot' => 0, 'Kha' => 0, 'Dat' => 0, 'ChuaDat' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $key = $row['hanh_kiem'] ?? null;
            if ($key && isset($data[$key])) {
                $data[$key] = $row['so_luong'];
            }
        }
        return $data;
    }

    // ... (Code cũ giữ nguyên) ...

    /**
     * [MỚI] Lấy danh sách đơn xin phép của lớp chủ nhiệm
     */
    public function getDanhSachDonXinPhep($ma_lop) {
        // JOIN 3 bảng: Phieu -> HocSinh -> NguoiDung (để lấy tên)
        $sql = "SELECT 
                    p.*,
                    nd.ho_ten AS ten_hoc_sinh
                FROM phieu_xin_nghi_hoc p
                JOIN hoc_sinh hs ON p.ma_nguoi_dung = hs.ma_hoc_sinh
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                WHERE hs.ma_lop = ?
                -- Sắp xếp: Chờ duyệt lên đầu, sau đó đến ngày gửi mới nhất
                ORDER BY FIELD(p.trang_thai_don, 'ChoDuyet', 'DaDuyet', 'TuChoi'), p.ngay_lam_don DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop]);
        return $stmt->fetchAll();
    }

    /**
     * [MỚI] Cập nhật trạng thái đơn (Duyệt/Từ chối)
     */
    public function duyetDonXinPhep($ma_phieu, $trang_thai) { // trang_thai: 'DaDuyet' hoặc 'TuChoi'
        try {
            $sql = "UPDATE phieu_xin_nghi_hoc SET trang_thai_don = ? WHERE ma_phieu = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$trang_thai, $ma_phieu]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
}
?>