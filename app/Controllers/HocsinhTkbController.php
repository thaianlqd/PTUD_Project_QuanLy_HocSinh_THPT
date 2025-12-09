<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Lưu ý: Sửa đường dẫn Models cho đúng với máy bác (viết hoa/thường)
require_once __DIR__ . '/../Models/HocsinhTkbModel.php';

class HocsinhTkbController {
    private $model;

    public function __construct() {
        // Kiểm tra quyền Học Sinh
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'HocSinh') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        $this->model = new HocsinhTkbModel();
    }

    // public function index() {
    //     $user_id = $_SESSION['user_id'];

    //     // 1. Lấy thông tin lớp
    //     $info_hs = $this->model->getThongTinLopHocSinh($user_id);
        
    //     if (!$info_hs || empty($info_hs['ma_lop'])) {
    //         echo "Lỗi: Bạn chưa được phân vào lớp học nào.";
    //         return;
    //     }

    //     // 2. Xử lý ngày tháng
    //     $date_str = $_GET['date'] ?? date('Y-m-d');
    //     try {
    //         $current_date = new DateTime($date_str);
    //     } catch (Exception $e) {
    //         $current_date = new DateTime();
    //     }

    //     // Tìm Thứ 2 đầu tuần
    //     $day_of_week = (int)$current_date->format('N'); 
    //     $start_week = clone $current_date;
    //     $start_week->modify('-' . ($day_of_week - 1) . ' days');

    //     // Tạo mảng ngày
    //     $week_dates = [];
    //     $temp = clone $start_week;
    //     for ($i = 0; $i < 7; $i++) {
    //         $week_dates[] = $temp->format('d/m');
    //         $temp->modify('+1 day');
    //     }

    //     // Navigation links
    //     $prev_week = (clone $start_week)->modify('-7 days')->format('Y-m-d');
    //     $next_week = (clone $start_week)->modify('+7 days')->format('Y-m-d');

    //     // 3. Lấy Học Kỳ
    //     $hoc_ky_info = $this->model->getHocKyTuNgay($start_week->format('Y-m-d'));
    //     $ma_hoc_ky = $hoc_ky_info['ma_hoc_ky'] ?? null;
    //     $ten_hoc_ky = $hoc_ky_info['ten_hoc_ky'] ?? 'Ngoài thời gian học';

    //     // 4. Lấy dữ liệu TKB
    //     $tkb_data = [];
    //     if ($ma_hoc_ky) {
    //         $tkb_data = $this->model->getTkbLop($info_hs['ma_lop'], $ma_hoc_ky);
    //     }

    //     // 5. Đẩy data ra View
    //     $data = [
    //         'ten_lop'       => $info_hs['ten_lop'],
    //         'ten_gvcn'      => $info_hs['ten_gvcn'] ?? 'Chưa cập nhật', // <--- ĐÃ BỔ SUNG DÒNG NÀY
    //         'week_dates'    => $week_dates,
    //         'selected_date' => $current_date->format('Y-m-d'),
    //         'prev_link'     => BASE_URL . "/hocsinhTkb/index?date=$prev_week",
    //         'next_link'     => BASE_URL . "/hocsinhTkb/index?date=$next_week",
    //         'ten_hoc_ky'    => $ten_hoc_ky,
    //         'ma_hoc_ky'     => $ma_hoc_ky,
    //         'tkb_data'      => $tkb_data
    //     ];

    //     // LOAD VIEW
    //     $this->loadView('HocSinh/xem_tkb', $data);
    // }
    public function index() {
        $user_id = $_SESSION['user_id'];

        // 1. Lấy thông tin lớp và GVCN
        $info_hs = $this->model->getThongTinLopHocSinh($user_id);
        
        if (!$info_hs || empty($info_hs['ma_lop'])) {
            // Trường hợp HS chưa được xếp lớp
            echo "Lỗi: Bạn chưa được phân vào lớp học nào.";
            return;
        }

        // 2. Xử lý ngày tháng (Date Picker)
        $date_str = $_GET['date'] ?? date('Y-m-d');
        try {
            $current_date = new DateTime($date_str);
        } catch (Exception $e) {
            $current_date = new DateTime();
        }

        // Tìm ngày Thứ 2 đầu tuần
        $day_of_week = (int)$current_date->format('N'); // 1=T2, 7=CN
        $start_week = clone $current_date;
        $start_week->modify('-' . ($day_of_week - 1) . ' days');

        // Tạo mảng ngày trong tuần (d/m) để hiển thị header
        $week_dates = [];
        $temp = clone $start_week;
        for ($i = 0; $i < 7; $i++) {
            $week_dates[] = $temp->format('d/m');
            $temp->modify('+1 day');
        }

        // Link điều hướng tuần trước/sau
        $prev_week = (clone $start_week)->modify('-7 days')->format('Y-m-d');
        $next_week = (clone $start_week)->modify('+7 days')->format('Y-m-d');

        // 3. Xác định Học Kỳ dựa trên ngày đầu tuần
        $hoc_ky_info = $this->model->getHocKyTuNgay($start_week->format('Y-m-d'));
        $ma_hoc_ky = $hoc_ky_info['ma_hoc_ky'] ?? null;
        $ten_hoc_ky = $hoc_ky_info['ten_hoc_ky'] ?? 'Ngoài thời gian học';

        // 4. Lấy dữ liệu TKB (Môn học, GV, Phòng)
        $tkb_data = [];
        if ($ma_hoc_ky) {
            $tkb_data = $this->model->getTkbLop($info_hs['ma_lop'], $ma_hoc_ky);
        }

        // --- 5. LẤY DANH SÁCH GIỜ HỌC (QUAN TRỌNG ĐỂ HIỂN THỊ GIỜ) ---
        $gio_hoc = $this->model->getGioHoc();
        // -------------------------------------------------------------

        // 6. Đóng gói dữ liệu đẩy sang View
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
            'gio_hoc'       => $gio_hoc  // <--- Bắt buộc phải có dòng này View mới hiện giờ
        ];

        // Load View
        $this->loadView('HocSinh/xem_tkb', $data);
    }

    private function loadView($viewPath, $data) {
        extract($data);
        
        // Sửa đường dẫn này tùy theo cấu trúc thư mục thực tế của bác
        // Ví dụ: app/Views/HocSinh/xem_tkb.php
        $fullPath = __DIR__ . '/../Views/' . $viewPath . '.php';

        if (file_exists($fullPath)) {
            require_once $fullPath;
        } else {
            die("Lỗi: Không tìm thấy file view.<br>Hệ thống đang tìm tại: <strong>$fullPath</strong>");
        }
    }
}
?>