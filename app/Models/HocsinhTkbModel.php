<?php
class HocsinhTkbModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL
        $ports = [3307, 3306];
        $connected = false;
        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $connected = true;
                break;
            } catch (PDOException $e) { continue; }
        }
        if (!$connected) die("Lỗi kết nối CSDL.");
    }

    // --- SỬA LẠI HÀM NÀY GỌN HƠN ---
    public function getThongTinLopHocSinh($user_id) {
        $sql = "SELECT 
                    hs.ma_hoc_sinh, 
                    hs.ma_lop, 
                    l.ten_lop, 
                    nd_hs.ho_ten AS ho_ten,      -- Tên học sinh
                    nd_gv.ho_ten AS ten_gvcn     -- Tên GVCN (Lấy trực tiếp từ bảng lop_hoc)
                FROM hoc_sinh hs
                JOIN nguoi_dung nd_hs ON hs.ma_hoc_sinh = nd_hs.ma_nguoi_dung
                JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                LEFT JOIN nguoi_dung nd_gv ON l.ma_gvcn = nd_gv.ma_nguoi_dung -- JOIN thẳng vào bảng người dùng qua mã GVCN
                WHERE hs.ma_hoc_sinh = ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    // Các hàm dưới giữ nguyên
    public function getHocKyTuNgay($ngay_sql) {
        $sql = "SELECT ma_hoc_ky, ten_hoc_ky 
                FROM hoc_ky
                WHERE :ngay BETWEEN ngay_bat_dau AND ngay_ket_thuc
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ngay', $ngay_sql);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    // public function getTkbLop($ma_lop, $ma_hoc_ky) {
    //     $sql = "SELECT 
    //                 t.thu, t.tiet, 
    //                 m.ten_mon_hoc, 
    //                 nd_gv.ho_ten AS ten_giao_vien,
    //                 COALESCE(ph.ten_phong, 'P.Học') AS ten_phong
    //             FROM tkb_chi_tiet t
    //             JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
    //             JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
    //             JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
    //             JOIN nguoi_dung nd_gv ON gv.ma_giao_vien = nd_gv.ma_nguoi_dung
    //             LEFT JOIN phong_hoc ph ON t.ma_phong_hoc = ph.ma_phong
    //             WHERE t.ma_lop = ? AND t.ma_hoc_ky = ?";
        
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([$ma_lop, $ma_hoc_ky]);
        
    //     $tkb_grid = [];
    //     while ($row = $stmt->fetch()) {
    //         $tkb_grid[$row['thu']][$row['tiet']] = [
    //             'mon' => $row['ten_mon_hoc'],
    //             'gv'  => $row['ten_giao_vien'],
    //             'phong' => $row['ten_phong']
    //         ];
    //     }
    //     return $tkb_grid;
    // }
    public function getTkbLop($ma_lop, $ma_hoc_ky) {
        $sql = "SELECT 
                    t.thu, t.tiet, 
                    m.ten_mon_hoc, 
                    nd_gv.ho_ten AS ten_giao_vien,
                    COALESCE(ph.ten_phong, 'P.Học') AS ten_phong,
                    t.loai_tiet,
                    t.ghi_chu
                FROM tkb_chi_tiet t
                LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                LEFT JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                LEFT JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                LEFT JOIN nguoi_dung nd_gv ON gv.ma_giao_vien = nd_gv.ma_nguoi_dung
                LEFT JOIN phong_hoc ph ON t.ma_phong_hoc = ph.ma_phong
                WHERE t.ma_lop = ? AND t.ma_hoc_ky = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop, $ma_hoc_ky]);
        
        $tkb_grid = [];
        while ($row = $stmt->fetch()) {
            $tkb_grid[$row['thu']][$row['tiet']] = [
                'mon'   => $row['ten_mon_hoc'],
                'gv'    => $row['ten_giao_vien'],
                'phong' => $row['ten_phong'],
                'loai_tiet' => $row['loai_tiet'] ?: 'hoc',
                'ghi_chu'   => $row['ghi_chu'] ?: ''
            ];
        }
        return $tkb_grid;
    }


    /**
     * HÀM MỚI: Lấy danh sách giờ học từ CSDL
     */
    public function getGioHoc() {
        // Lấy danh sách tiết và giờ bắt đầu - kết thúc
        $sql = "SELECT ma_tiet_hoc, gio_bat_dau, gio_ket_thuc FROM tiet_hoc ORDER BY ma_tiet_hoc ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $result = [];
        while ($row = $stmt->fetch()) {
            // Format giờ: 07:00:00 -> 07:00 (bỏ giây cho gọn)
            $start = date('H:i', strtotime($row['gio_bat_dau']));
            $end   = date('H:i', strtotime($row['gio_ket_thuc']));
            
            // Lưu vào mảng: key là mã tiết (1, 2, 3...)
            $result[$row['ma_tiet_hoc']] = "$start - $end";
        }
        return $result; 
    }
}
?>