<?php
class HocsinhTkbModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL (Thử port 3307 trước, nếu không được thì 3306)
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
        if (!$connected) die("Lỗi kết nối CSDL: Vui lòng kiểm tra XAMPP (MySQL).");
    }

    // Lấy thông tin cơ bản của học sinh & lớp
    public function getThongTinLopHocSinh($user_id) {
        $sql = "SELECT hs.ma_hoc_sinh, hs.ma_lop, l.ten_lop, 
                       nd_hs.ho_ten AS ho_ten, 
                       nd_gv.ho_ten AS ten_gvcn 
                FROM hoc_sinh hs
                JOIN nguoi_dung nd_hs ON hs.ma_hoc_sinh = nd_hs.ma_nguoi_dung
                JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                LEFT JOIN nguoi_dung nd_gv ON l.ma_gvcn = nd_gv.ma_nguoi_dung
                WHERE hs.ma_hoc_sinh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    // Lấy thông tin học kỳ dựa trên ngày hiện tại
    public function getHocKyTuNgay($ngay_sql) {
        $sql = "SELECT ma_hoc_ky, ten_hoc_ky FROM hoc_ky 
                WHERE :ngay BETWEEN ngay_bat_dau AND ngay_ket_thuc LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ngay' => $ngay_sql]);
        return $stmt->fetch() ?: null;
    }

    // Lấy danh sách giờ học (Tiết 1: 07:00 - 07:45...)
    public function getGioHoc() {
        $sql = "SELECT ma_tiet_hoc, gio_bat_dau, gio_ket_thuc FROM tiet_hoc ORDER BY ma_tiet_hoc ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = [];
        while ($row = $stmt->fetch()) {
            $start = date('H:i', strtotime($row['gio_bat_dau']));
            $end   = date('H:i', strtotime($row['gio_ket_thuc']));
            $result[$row['ma_tiet_hoc']] = "$start - $end";
        }
        return $result; 
    }

    // --- [CORE LOGIC: OVERLAY SYSTEM] ---

    /**
     * Bước 1: Lấy Lịch Cứng (Base Schedule) - Lặp lại hàng tuần
     */
    private function getTkbCung($ma_lop, $ma_hoc_ky) {
        $sql = "SELECT t.thu, t.tiet, m.ten_mon_hoc, nd_gv.ho_ten AS ten_giao_vien, 
                       COALESCE(ph.ten_phong, 'P.Học') AS ten_phong, 
                       t.loai_tiet, t.ghi_chu
                FROM tkb_chi_tiet t
                LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                LEFT JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                LEFT JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                LEFT JOIN nguoi_dung nd_gv ON gv.ma_giao_vien = nd_gv.ma_nguoi_dung
                LEFT JOIN phong_hoc ph ON t.ma_phong_hoc = ph.ma_phong
                WHERE t.ma_lop = ? AND t.ma_hoc_ky = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop, $ma_hoc_ky]);
        
        $tkb = [];
        while ($row = $stmt->fetch()) {
            $tkb[$row['thu']][$row['tiet']] = [
                'mon'       => $row['ten_mon_hoc'],
                'gv'        => $row['ten_giao_vien'],
                'phong'     => $row['ten_phong'],
                'loai_tiet' => $row['loai_tiet'] ?: 'hoc',
                'ghi_chu'   => $row['ghi_chu'] ?: '',
                'is_changed'=> false
            ];
        }
        return $tkb;
    }

    /**
     * Bước 2: Lấy Lịch Thay Đổi (Overlay) trong tuần cụ thể
     */
    private function getThayDoiTrongTuan($ma_lop, $start_date, $end_date) {
        $sql = "SELECT t.ngay_thay_doi, t.tiet, t.loai_tiet, t.ghi_chu,
                       m.ten_mon_hoc, nd.ho_ten as ten_giao_vien
                FROM tkb_thay_doi t
                LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                LEFT JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                LEFT JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                WHERE t.ma_lop = ? AND t.ngay_thay_doi BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop, $start_date, $end_date]);
        return $stmt->fetchAll();
    }

    /**
     * Bước 3: HÀM PUBLIC CHÍNH - Trộn Lịch Cứng + Thay Đổi
     */
    public function getTkbChinhThuc($ma_lop, $ma_hoc_ky, $start_date, $end_date) {
        // 1. Lấy nền (lịch cứng)
        $tkb = $this->getTkbCung($ma_lop, $ma_hoc_ky);

        // 2. Lấy lớp phủ (thay đổi)
        $changes = $this->getThayDoiTrongTuan($ma_lop, $start_date, $end_date);

        // 3. Đè dữ liệu
        foreach ($changes as $c) {
            // Đổi ngày Y-m-d sang Thứ (2-8)
            $timestamp = strtotime($c['ngay_thay_doi']);
            $thu = date('N', $timestamp) + 1; 
            $tiet = $c['tiet'];

            // Chuẩn bị dữ liệu hiển thị
            $mon_hien_thi = $c['ten_mon_hoc'];
            if ($c['loai_tiet'] == 'tam_nghi') {
                $mon_hien_thi = "(Nghỉ) " . ($c['ten_mon_hoc'] ?? 'Tạm nghỉ');
            }

            $tkb[$thu][$tiet] = [
                'mon'       => $mon_hien_thi,
                'gv'        => $c['ten_giao_vien'] ?? '',
                'phong'     => 'Xem chi tiết ngày', // Có thể để trống hoặc query thêm bảng phòng
                'loai_tiet' => $c['loai_tiet'],
                'ghi_chu'   => $c['ghi_chu'],
                'is_changed'=> true // Cờ quan trọng để View hiển thị cảnh báo
            ];
        }

        return $tkb;
    }
}
?>