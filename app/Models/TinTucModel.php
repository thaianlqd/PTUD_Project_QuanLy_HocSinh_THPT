<?php
class TinTucModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL (Copy lại đoạn kết nối chuẩn của bác)
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

    // Lấy tất cả bài viết (Cho trang Admin)
    public function getAllBaiViet() {
        $sql = "SELECT * FROM bai_viet ORDER BY ngay_dang DESC";
        return $this->db->query($sql)->fetchAll();
    }

    // Lấy bài viết công khai (Cho Trang Chủ)
    public function getBaiVietCongKhai($limit = 3) {
        // Sửa 'CongKhai' thành 'DaDang'
        $sql = "SELECT * FROM bai_viet WHERE trang_thai = 'DaDang' ORDER BY ngay_dang DESC LIMIT $limit";
        return $this->db->query($sql)->fetchAll();
    }

    // Thêm bài viết mới
    public function addBaiViet($data) {
        $sql = "INSERT INTO bai_viet (tieu_de, noi_dung, tac_gia, loai_bai_viet, trang_thai, ngay_dang, ma_nguoi_dung) 
                VALUES (?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['tieu_de'], 
            $data['noi_dung'], 
            $data['tac_gia'], 
            $data['loai_bai_viet'], 
            $data['trang_thai'],
            $data['ma_nguoi_dung']
        ]);
    }

    // Cập nhật bài viết
    public function updateBaiViet($id, $data) {
        $sql = "UPDATE bai_viet SET tieu_de = ?, noi_dung = ?, tac_gia = ?, loai_bai_viet = ?, trang_thai = ? WHERE ma_bai_viet = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['tieu_de'], 
            $data['noi_dung'], 
            $data['tac_gia'], 
            $data['loai_bai_viet'], 
            $data['trang_thai'],
            $id
        ]);
    }

    // Xóa bài viết
    public function deleteBaiViet($id) {
        $stmt = $this->db->prepare("DELETE FROM bai_viet WHERE ma_bai_viet = ?");
        return $stmt->execute([$id]);
    }
}
?>