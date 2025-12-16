<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../Models/HocsinhTkbModel.php';

class HocsinhTkbController {
    private $model;

    public function __construct() {
        // Check quyền Học sinh
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'HocSinh') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        $this->model = new HocsinhTkbModel();
    }

    public function index() {
        $user_id = $_SESSION['user_id'];

        // 1. Lấy thông tin lớp
        $info_hs = $this->model->getThongTinLopHocSinh($user_id);
        
        if (!$info_hs || empty($info_hs['ma_lop'])) {
            echo "Lỗi: Bạn chưa được xếp vào lớp học nào. Vui lòng liên hệ Admin.";
            return;
        }

        // 2. Xử lý thời gian (Tuần hiện tại)
        $date_str = $_GET['date'] ?? date('Y-m-d');
        try { 
            $current_date = new DateTime($date_str); 
        } catch (Exception $e) { 
            $current_date = new DateTime(); 
        }

        // Tìm Thứ 2 và Chủ Nhật của tuần đó
        $day_of_week = (int)$current_date->format('N'); // 1=T2, 7=CN
        $start_week = clone $current_date;
        $start_week->modify('-' . ($day_of_week - 1) . ' days');
        
        $end_week = clone $start_week;
        $end_week->modify('+6 days');

        $start_date_sql = $start_week->format('Y-m-d');
        $end_date_sql   = $end_week->format('Y-m-d');

        // Tạo mảng ngày header (d/m)
        $week_dates = [];
        $temp = clone $start_week;
        for ($i = 0; $i < 7; $i++) {
            $week_dates[] = $temp->format('d/m');
            $temp->modify('+1 day');
        }

        // Link điều hướng
        $prev_week = (clone $start_week)->modify('-7 days')->format('Y-m-d');
        $next_week = (clone $start_week)->modify('+7 days')->format('Y-m-d');

        // 3. Xác định Học Kỳ
        $hoc_ky_info = $this->model->getHocKyTuNgay($start_date_sql);
        $ma_hoc_ky = $hoc_ky_info['ma_hoc_ky'] ?? null;
        $ten_hoc_ky = $hoc_ky_info['ten_hoc_ky'] ?? 'Ngoài thời gian học';

        // 4. Lấy TKB Chính Thức (Đã merge lịch cứng & thay đổi)
        $tkb_data = [];
        if ($ma_hoc_ky) {
            $tkb_data = $this->model->getTkbChinhThuc($info_hs['ma_lop'], $ma_hoc_ky, $start_date_sql, $end_date_sql);
        }

        // Lấy giờ học
        $gio_hoc = $this->model->getGioHoc();

        // 5. Render View
        $data = [
            'ten_lop'       => $info_hs['ten_lop'],
            'ten_gvcn'      => $info_hs['ten_gvcn'] ?? 'Chưa cập nhật',
            'week_dates'    => $week_dates,
            'selected_date' => $current_date->format('Y-m-d'),
            'prev_link'     => BASE_URL . "/hocsinhTkb/index?date=$prev_week",
            'next_link'     => BASE_URL . "/hocsinhTkb/index?date=$next_week",
            'ten_hoc_ky'    => $ten_hoc_ky,
            'ma_hoc_ky'     => $ma_hoc_ky,
            'tkb_data'      => $tkb_data,
            'gio_hoc'       => $gio_hoc
        ];

        $this->loadView('HocSinh/xem_tkb', $data);
    }

    private function loadView($viewPath, $data) {
        extract($data);
        $fullPath = __DIR__ . '/../Views/' . $viewPath . '.php';
        if (file_exists($fullPath)) {
            require_once $fullPath;
        } else {
            die("Lỗi: Không tìm thấy file view ($fullPath)");
        }
    }
}
?>