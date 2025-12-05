<?php
class GiaoVienChuNhiemModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL
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

    /**
     * SỬA LẠI: Tìm lớp dựa trên cột 'ma_gvcn' trong bảng 'lop_hoc'
     * (Cách này chuẩn xác nhất với Database của bạn)
     */
    public function getLopChuNhiem($ma_giao_vien) {
        if ($this->db === null) return null;
        
        // Query đơn giản và trực tiếp hơn
        // Tìm xem giáo viên này (ID = ?) có tên trong cột ma_gvcn của lớp nào không
        $sql = "SELECT l.*, 
                       (SELECT COUNT(*) FROM hoc_sinh hs WHERE hs.ma_lop = l.ma_lop) as si_so_thuc
                FROM lop_hoc l
                WHERE l.ma_gvcn = ? 
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_giao_vien]);
        return $stmt->fetch();
    }

    /**
     * Lấy danh sách học sinh của lớp (Kèm điểm TB và số vắng)
     */
    public function getDanhSachHocSinh($ma_lop) {
        if ($this->db === null) return [];
        $sql = "SELECT 
                    hs.ma_hoc_sinh,
                    nd.ho_ten,
                    kq.diem_tb_hoc_ky,
                    kq.xep_loai_hanh_kiem,
                    kq.nhan_xet,
                    (SELECT COUNT(*) FROM chi_tiet_diem_danh ct 
                     WHERE ct.ma_nguoi_dung = hs.ma_hoc_sinh 
                     AND ct.trang_thai_diem_danh IN ('VangCoPhep', 'VangKhongPhep')) as so_buoi_vang
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN ket_qua_hoc_tap kq ON hs.ma_hoc_sinh = kq.ma_nguoi_dung
                WHERE hs.ma_lop = ?
                ORDER BY nd.ho_ten";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop]);
        return $stmt->fetchAll();
    }

    /**
     * Thống kê Hạnh kiểm
     */
    public function getChartHanhKiem($ma_lop) {
        $sql = "SELECT kq.xep_loai_hanh_kiem, COUNT(*) as so_luong
                FROM hoc_sinh hs
                LEFT JOIN ket_qua_hoc_tap kq ON hs.ma_hoc_sinh = kq.ma_nguoi_dung
                WHERE hs.ma_lop = ?
                GROUP BY kq.xep_loai_hanh_kiem";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop]);
        
        $data = ['Tot' => 0, 'Kha' => 0, 'TB' => 0, 'Yeu' => 0, 'ChuaXep' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $key = $row['xep_loai_hanh_kiem'] ?? 'ChuaXep';
            $data[$key] = $row['so_luong'];
        }
        return $data;
    }
}
?>