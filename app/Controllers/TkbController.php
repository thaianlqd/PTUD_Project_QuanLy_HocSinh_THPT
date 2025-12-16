<?php
/**
 * TkbController - Xử lý logic Xếp Thời Khóa Biểu (TKB)
 * Tách riêng từ QuanTriController để dễ quản lý
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class TkbController extends Controller {

    private $userModel;
    private $tkbModel;

    public function __construct() {
        // Kiểm tra quyền Quản trị
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'QuanTriVien') {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }
        
        // Load model
        $this->userModel = $this->loadModel('UserModel');
        $this->tkbModel = $this->loadModel('TkbModel');

        if ($this->tkbModel === null || $this->userModel === null) {
             die("Lỗi nghiêm trọng: Không thể tải UserModel hoặc TkbModel.");
        }
    }

    /**
     * URL: /tkb/xeptkb
     * Hiển thị danh sách lớp để chọn xếp lịch
     */
    public function xeptkb() {
        // 1. Lấy ID trường từ Session hoặc CSDL
        $school_id = $_SESSION['admin_school_id'] ?? null;

        if (!$school_id && isset($_SESSION['user_id'])) {
            $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id']);
            $_SESSION['admin_school_id'] = $school_id;
        }

        // 2. Gọi Model lấy danh sách lớp (có lọc theo trường)
        $danhSachLop = $this->tkbModel->getDanhSachLop($school_id);

        // 3. Chuẩn bị dữ liệu
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'lop_hoc' => $danhSachLop
        ];

        // Xử lý thông báo Flash Message
        if (isset($_SESSION['flash_message'])) {
            $data['flash_message'] = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        }

        // 4. Load View
        $content = $this->loadView('QuanTri/danh_sach_lop_tkb', $data);
        echo $content;
    }

    /**
     * URL: /tkb/chiTietTkb/1
     * HOẶC: /tkb/chiTietTkb/1?date=2025-11-20
     * Hiển thị chi tiết TKB của 1 lớp theo tuần + học kỳ
     */
    // public function chiTietTkb($ma_lop = 0) {
    //     $ma_lop = (int)$ma_lop;
    //     if ($ma_lop <= 0) {
    //         header('Location: ' . BASE_URL . '/tkb/xeptkb');
    //         exit;
    //     }

    //     // Lấy school_id của admin
    //     $school_id = $_SESSION['admin_school_id'] ?? null;
    //     if (!$school_id && isset($_SESSION['user_id'])) {
    //         $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id']);
    //         if ($school_id) $_SESSION['admin_school_id'] = $school_id;
    //     }

    //     // --- Logic Xử lý Ngày ---
    //     $selected_date_str = $_GET['date'] ?? date('Y-m-d');
    //     try {
    //         $selected_date = new DateTime($selected_date_str);
    //     } catch (Exception $e) {
    //         $selected_date = new DateTime();
    //     }

    //     $day_of_week = (int)$selected_date->format('N'); // 1 = T2, ..., 7 = CN
    //     $start_of_week = clone $selected_date;
    //     $start_of_week->modify('-' . ($day_of_week - 1) . ' days'); // Lùi về Thứ 2
        
    //     $week_dates = [];
    //     $week_dates_sql = [];
    //     $current_day_iterator = clone $start_of_week;
        
    //     for ($i = 0; $i < 7; $i++) {
    //         $week_dates[] = $current_day_iterator->format('d/m/Y');
    //         $week_dates_sql[] = $current_day_iterator->format('Y-m-d'); 
    //         $current_day_iterator->modify('+1 day');
    //     }

    //     $prev_week_date = (clone $start_of_week)->modify('-7 days')->format('Y-m-d');
    //     $next_week_date = (clone $start_of_week)->modify('+7 days')->format('Y-m-d');
    //     $base_url_tkb = BASE_URL . '/tkb/chiTietTkb/' . $ma_lop;
    //     $current_date_param = '?date=' . $selected_date->format('Y-m-d');
        
    //     // Lưu date param để dùng cho form POST
    //     $_SESSION['last_date_param'] = $current_date_param;

    //     // --- Tìm học kỳ dựa trên ngày ---
    //     $start_date_sql = $week_dates_sql[0]; // Ngày Thứ 2 của tuần
    //     $hoc_ky = $this->tkbModel->getHocKyTuNgay($start_date_sql);
        
    //     $ma_hoc_ky = null;
    //     $ten_hoc_ky = "Nghỉ (Ngoài thời gian học kỳ)";

    //     if ($hoc_ky) {
    //         $ma_hoc_ky = $hoc_ky['ma_hoc_ky'];
    //         $ten_hoc_ky = $hoc_ky['ten_hoc_ky'];
    //     }

    //     // --- Lấy dữ liệu TKB ---
    //     $tkbData = [];
    //     $rangBuoc = [];
    //     $tenLop = $this->tkbModel->getTenLop($ma_lop); 

    //     if ($tenLop === 'N/A') {
    //          $_SESSION['flash_message'] = ['type' => 'danger', 'message' => "Không tìm thấy thông tin lớp học (ID: $ma_lop)."];
    //          header('Location: ' . BASE_URL . '/tkb/xeptkb');
    //          exit;
    //     }
        
    //     // Chỉ tải TKB nếu đang trong 1 học kỳ
    //     if ($ma_hoc_ky !== null) {
    //         $rangBuoc = $this->tkbModel->getRangBuocLop($ma_lop, $ma_hoc_ky);
    //         $tkbData = $this->tkbModel->getChiTietTkbLop($ma_lop, $ma_hoc_ky);
    //     } else {
    //         // Nếu là nghỉ hè, tạo dữ liệu rỗng
    //         $rangBuoc = [
    //             'ten_lop' => $tenLop,
    //             'phong_chinh' => 'N/A', 'gvcn' => 'N/A',
    //             'tong_tiet_da_xep' => 0, 'tong_tiet_ke_hoach' => 0,
    //             'mon_hoc' => []
    //         ];
    //     }

    //     $phongHocChinhID = $this->tkbModel->getPhongHocChinhID($ma_lop);
    //     // Lọc danh sách lớp theo trường
    //     $danhSachTatCaLop = $this->tkbModel->getDanhSachLop($school_id); 

    //     $data = [
    //         'user_name' => $_SESSION['user_name'] ?? 'Admin',
    //         'ma_lop' => $ma_lop,
    //         'nam_hoc' => '2025-2026',
    //         'rang_buoc' => $rangBuoc,
    //         'tkb_data' => $tkbData,
    //         'phong_hoc_chinh_id' => $phongHocChinhID,
    //         'danh_sach_lop' => $danhSachTatCaLop,
            
    //         // Dữ liệu ngày tháng
    //         'selected_date' => $selected_date->format('Y-m-d'),
    //         'week_dates' => $week_dates,
    //         'prev_week_link' => $base_url_tkb . '?date=' . $prev_week_date,
    //         'next_week_link' => $base_url_tkb . '?date=' . $next_week_date,
    //         'current_date_param' => $current_date_param,

    //         // Dữ liệu học kỳ
    //         'ma_hoc_ky' => $ma_hoc_ky,
    //         'ten_hoc_ky' => $ten_hoc_ky
    //     ];
        
    //     if (isset($_SESSION['flash_message'])) {
    //         $data['flash_message'] = $_SESSION['flash_message'];
    //         unset($_SESSION['flash_message']);
    //     }

    //     $content = $this->loadView('QuanTri/chi_tiet_tkb', $data);
    //     echo $content;
    // }
    public function chiTietTkb($ma_lop = 0) {
        $ma_lop = (int)$ma_lop;
        if ($ma_lop <= 0) {
            header('Location: ' . BASE_URL . '/tkb/xeptkb');
            exit;
        }

        // Lấy school_id của admin
        $school_id = $_SESSION['admin_school_id'] ?? null;
        if (!$school_id && isset($_SESSION['user_id'])) {
            $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id']);
            if ($school_id) $_SESSION['admin_school_id'] = $school_id;
        }

        // --- Logic Xử lý Ngày ---
        $selected_date_str = $_GET['date'] ?? date('Y-m-d');
        try {
            $selected_date = new DateTime($selected_date_str);
        } catch (Exception $e) {
            $selected_date = new DateTime();
        }

        $day_of_week = (int)$selected_date->format('N'); // 1 = T2, ..., 7 = CN
        $start_of_week = clone $selected_date;
        $start_of_week->modify('-' . ($day_of_week - 1) . ' days'); // Lùi về Thứ 2
        
        $week_dates = [];
        $week_dates_sql = [];
        $current_day_iterator = clone $start_of_week;
        
        for ($i = 0; $i < 7; $i++) {
            $week_dates[] = $current_day_iterator->format('d/m/Y'); // Dùng để hiển thị
            $week_dates_sql[] = $current_day_iterator->format('Y-m-d'); // Dùng để truy vấn SQL
            $current_day_iterator->modify('+1 day');
        }

        $prev_week_date = (clone $start_of_week)->modify('-7 days')->format('Y-m-d');
        $next_week_date = (clone $start_of_week)->modify('+7 days')->format('Y-m-d');
        $base_url_tkb = BASE_URL . '/tkb/chiTietTkb/' . $ma_lop;
        $current_date_param = '?date=' . $selected_date->format('Y-m-d');
        
        // Lưu date param để redirect sau khi lưu
        $_SESSION['last_date_param'] = $current_date_param;

        // --- Tìm học kỳ ---
        // Lấy ngày T2 và CN của tuần đó để xác định học kỳ
        $start_date_sql = $week_dates_sql[0]; 
        $end_date_sql   = $week_dates_sql[6];

        $hoc_ky = $this->tkbModel->getHocKyTuNgay($start_date_sql);
        
        $ma_hoc_ky = null;
        $ten_hoc_ky = "Nghỉ (Ngoài thời gian học kỳ)";

        if ($hoc_ky) {
            $ma_hoc_ky = $hoc_ky['ma_hoc_ky'];
            $ten_hoc_ky = $hoc_ky['ten_hoc_ky'];
        }

        // --- Lấy dữ liệu TKB ---
        $tkbData = [];
        $rangBuoc = [];
        $tenLop = $this->tkbModel->getTenLop($ma_lop); 

        if ($tenLop === 'N/A') {
             $_SESSION['flash_message'] = ['type' => 'danger', 'message' => "Không tìm thấy lớp học."];
             header('Location: ' . BASE_URL . '/tkb/xeptkb');
             exit;
        }
        
        if ($ma_hoc_ky !== null) {
            #note: phần này cần :v
            // $rangBuoc = $this->tkbModel->getRangBuocLop($ma_lop, $ma_hoc_ky);
            $rangBuoc = $this->tkbModel->getRangBuocLop($ma_lop, $ma_hoc_ky, $start_date_sql, $end_date_sql);
            
            // [QUAN TRỌNG] Gọi hàm lấy TKB theo tuần (đã trộn lịch thay đổi)
            $tkbData = $this->tkbModel->getChiTietTkbTuan($ma_lop, $ma_hoc_ky, $start_date_sql, $end_date_sql);

        } else {
            // Nếu nghỉ hè
            $rangBuoc = [
                'ten_lop' => $tenLop,
                'phong_chinh' => 'N/A', 'gvcn' => 'N/A',
                'tong_tiet_da_xep' => 0, 'tong_tiet_ke_hoach' => 0,
                'mon_hoc' => []
            ];
        }

        $phongHocChinhID = $this->tkbModel->getPhongHocChinhID($ma_lop);
        $danhSachTatCaLop = $this->tkbModel->getDanhSachLop($school_id); 

        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'ma_lop' => $ma_lop,
            'nam_hoc' => '2025-2026', // Tạm fix hoặc lấy từ session
            'rang_buoc' => $rangBuoc,
            'tkb_data' => $tkbData,
            'phong_hoc_chinh_id' => $phongHocChinhID,
            'danh_sach_lop' => $danhSachTatCaLop,
            'selected_date' => $selected_date->format('Y-m-d'),
            'week_dates' => $week_dates,
            'prev_week_link' => $base_url_tkb . '?date=' . $prev_week_date,
            'next_week_link' => $base_url_tkb . '?date=' . $next_week_date,
            'current_date_param' => $current_date_param,
            'ma_hoc_ky' => $ma_hoc_ky,
            'ten_hoc_ky' => $ten_hoc_ky
        ];
        
        if (isset($_SESSION['flash_message'])) {
            $data['flash_message'] = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        }

        $content = $this->loadView('QuanTri/chi_tiet_tkb', $data);
        echo $content;
    }

    /**
     * URL: /tkb/luuTietHoc (POST)
     * Lưu hoặc xóa 1 tiết học
     */
    
    public function luuTietHoc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_lop       = filter_input(INPUT_POST, 'ma_lop', FILTER_VALIDATE_INT);
            $thu          = filter_input(INPUT_POST, 'thu', FILTER_VALIDATE_INT);
            $tiet         = filter_input(INPUT_POST, 'tiet', FILTER_VALIDATE_INT);
            $ma_hoc_ky    = filter_input(INPUT_POST, 'ma_hoc_ky', FILTER_VALIDATE_INT);
            
            $kieu_luu     = $_POST['kieu_luu'] ?? 'hoc_ky';
            $ngay_chon    = $_POST['ngay_chon'] ?? ''; 
            
            $loai_tiet    = $_POST['loai_tiet'] ?? 'hoc';
            $ghi_chu      = trim($_POST['ghi_chu'] ?? '');
            $ma_phan_cong = filter_input(INPUT_POST, 'ma_phan_cong', FILTER_VALIDATE_INT);
            
            $date_param   = $_SESSION['last_date_param'] ?? '';
            $redirect_url = BASE_URL . '/tkb/chiTietTkb/' . ($ma_lop ?? '') . $date_param;

            if (!$ma_lop || !$thu || !$tiet || !$ma_hoc_ky) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Dữ liệu không hợp lệ.'];
                header('Location: ' . $redirect_url); exit;
            }

            // CHẶN LOGIC: Lịch cứng chỉ được là 'hoc'
            if ($kieu_luu == 'hoc_ky' && $loai_tiet !== 'hoc') {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lịch cố định chỉ áp dụng cho "Tiết học chính".'];
                header('Location: ' . $redirect_url); exit;
            }

            // XỬ LÝ XÓA
            if (isset($_POST['delete']) && $_POST['delete'] == '1') {
                if ($kieu_luu == 'ngay') {
                    $ok = $this->tkbModel->xoaThayDoiTietHoc($ma_lop, $ngay_chon, $tiet);
                    $msg = $ok ? 'Đã xóa thay đổi ngày.' : 'Lỗi xóa thay đổi.';
                } else {
                    $ok = $this->tkbModel->xoaTietHoc($ma_lop, $ma_hoc_ky, $thu, $tiet);
                    $msg = $ok ? 'Đã xóa tiết cố định.' : 'Lỗi xóa tiết.';
                }
                $_SESSION['flash_message'] = $ok ? ['type' => 'success', 'message' => $msg] : ['type' => 'danger', 'message' => $msg];
                header('Location: ' . $redirect_url); exit;
            }

            // XỬ LÝ LƯU
            if (isset($_POST['save']) && $_POST['save'] == '1') {
                if (in_array($loai_tiet, ['hoc', 'day_bu']) && !$ma_phan_cong) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Vui lòng chọn Môn học.'];
                    header('Location: ' . $redirect_url); exit;
                }
                $ma_phan_cong = $ma_phan_cong ?: null;

                // --- CASE 1: LƯU THEO NGÀY ---
                if ($kieu_luu == 'ngay') {
                    if ($ma_phan_cong) {
                        // Gọi hàm kiểm tra với tham số $ngay_chon
                        $kiemTra = $this->tkbModel->kiemTraRangBuoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong, null, $ngay_chon);
                        if ($kiemTra !== true) {
                            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi xếp lịch ngày: ' . $kiemTra];
                            header('Location: ' . $redirect_url); exit;
                        }
                    }
                    $ok = $this->tkbModel->luuThayDoiTietHoc($ma_lop, $ngay_chon, $tiet, $ma_phan_cong, $loai_tiet, $ghi_chu);
                    $msg = $ok ? "Đã lưu thay đổi ngày $ngay_chon." : 'Lỗi lưu thay đổi.';
                } 
                // --- CASE 2: LƯU CỐ ĐỊNH ---
                else {
                    if ($ma_phan_cong) {
                         $ma_tkb_chi_tiet_dang_sua = $this->tkbModel->getMaTkbChiTietBySlot($ma_lop, $ma_hoc_ky, $thu, $tiet);
                         $kiemTra = $this->tkbModel->kiemTraRangBuoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong, $ma_tkb_chi_tiet_dang_sua);
                         if ($kiemTra !== true) {
                            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi xếp lịch cứng: ' . $kiemTra];
                            header('Location: ' . $redirect_url); exit;
                         }
                    }
                    $ok = $this->tkbModel->luuTietHoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong, $loai_tiet, $ghi_chu);
                    $msg = $ok ? 'Đã lưu lịch cố định.' : 'Lưu thất bại.';
                }

                $_SESSION['flash_message'] = $ok ? ['type' => 'success', 'message' => $msg] : ['type' => 'danger', 'message' => $msg];
                header('Location: ' . $redirect_url); exit;
            }
        }
        header('Location: ' . BASE_URL . '/tkb/xeptkb'); exit;
    }


    /**
     * API: /tkb/getDanhSachMonHocGV/{ma_lop}/{thu}/{tiet} (GET)
     * Trả danh sách GV+môn có sẵn cho 1 ô tiết
     */
    // public function getDanhSachMonHocGV($ma_lop = 0, $thu = 0, $tiet = 0) {
    //     // Buffer output to prevent accidental HTML (PHP warnings/notices) leaking into JSON
    //     ob_start();
    //     $ma_lop = (int)$ma_lop; $thu = (int)$thu; $tiet = (int)$tiet;
    //     if ($ma_lop <= 0 || $thu < 2 || $thu > 8 || $tiet < 1 || $tiet > 7) {
    //         $body = ['error' => 'Thiếu thông tin (lớp, thứ, tiết).'];
    //         $extra = ob_get_clean();
    //         if (!empty($extra)) error_log("getDanhSachMonHocGV - unexpected output: " . $extra);
    //         http_response_code(400);
    //         header('Content-Type: application/json');
    //         echo json_encode($body);
    //         return;
    //     }

    //     // Kiểm tra lớp có thuộc trường của admin không
    //     $school_id = $_SESSION['admin_school_id'] ?? null;
    //     if (!$school_id && isset($_SESSION['user_id'])) {
    //         $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id']);
    //         if ($school_id) $_SESSION['admin_school_id'] = $school_id;
    //     }

    //     if ($school_id) {
    //         // Kiểm tra xem lớp có thuộc trường này không
    //         $lop_ma_truong = $this->tkbModel->getMaTruongByLop($ma_lop);
    //         if (!$lop_ma_truong || $lop_ma_truong != $school_id) {
    //             $body = ['error' => 'Bạn không có quyền truy cập lớp này.'];
    //             $extra = ob_get_clean();
    //             if (!empty($extra)) error_log("getDanhSachMonHocGV - unexpected output: " . $extra);
    //             http_response_code(403);
    //             header('Content-Type: application/json');
    //             echo json_encode($body);
    //             return;
    //         }
    //     }

    //     // Lấy danh sách GV+môn
    //     $ds_mon_gv_phan_cong = $this->tkbModel->getDanhSachMonHocGV($ma_lop);
    //     $phong_hoc_chinh_id = $this->tkbModel->getPhongHocChinhID($ma_lop);
    //     $result = ['mon_hoc_gv' => []];

    //     foreach ($ds_mon_gv_phan_cong as $item) {
    //         $ma_giao_vien = $item['ma_giao_vien'];
    //         $ma_phan_cong = $item['ma_phan_cong'];
    //         $ma_phong_du_kien = $item['ma_phong_dac_biet'] ?? $phong_hoc_chinh_id;
            
    //         // Kiểm tra GV bận
    //         $gv_ban_lich = $this->tkbModel->getGVBan($ma_giao_vien);
    //         $is_gv_ban = isset($gv_ban_lich[$thu][$tiet]);
            
    //         $is_phong_ban = false;
    //         if ($ma_phong_du_kien !== null && $ma_phong_du_kien != $phong_hoc_chinh_id) {
    //             // Kiểm tra Phòng bận (NHƯ NG: Nếu là phòng chính của lớp thì không check bận vì là phòng riêng của lớp)
    //             $phong_ban_lich = $this->tkbModel->getPhongBan($ma_phong_du_kien);
    //             $is_phong_ban = isset($phong_ban_lich[$thu][$tiet]);
    //         }

    //         $is_option_ban = $is_gv_ban || $is_phong_ban;
    //         $ly_do_ban = $is_gv_ban ? '(GV bận)' : ($is_phong_ban ? '(Phòng bận)' : '');
            
    //         $result['mon_hoc_gv'][] = [
    //             'ma_phan_cong' => $ma_phan_cong,
    //             'ten_hien_thi' => $item['ten_mon_hoc'] . ' - (GV: ' . $item['ten_giao_vien'] . ')',
    //             'is_ban' => $is_option_ban,
    //             'ly_do' => $ly_do_ban
    //         ];
    //     }
    //     $extra = ob_get_clean();
    //     if (!empty($extra)) error_log("getDanhSachMonHocGV - unexpected output: " . $extra);
    //     header('Content-Type: application/json');
    //     echo json_encode($result);
    // }
    /**
     * API: Lấy danh sách môn + Check GV bận (Có xét ngày cụ thể)
     */
    public function getDanhSachMonHocGV($ma_lop = 0, $thu = 0, $tiet = 0) {
        // 1. Validate cơ bản
        $ma_lop = (int)$ma_lop; $thu = (int)$thu; $tiet = (int)$tiet;
        
        // 2. Lấy ngày check từ Request (Frontend gửi lên)
        $date_check = $_GET['date'] ?? null;

        // 3. Xác định Học Kỳ (Để check lịch cứng)
        // Nếu không có ngày check thì lấy ngày hiện tại
        $date_for_hocky = $date_check ? $date_check : date('Y-m-d');
        $hoc_ky_info = $this->tkbModel->getHocKyTuNgay($date_for_hocky);
        $ma_hoc_ky = $hoc_ky_info['ma_hoc_ky'] ?? 0;

        // 4. Gọi Model xử lý logic Check Bận "Kép" (Cả cứng & mềm)
        // Lưu ý: Ta dùng hàm mới tên là getDanhSachMonHocGV_CheckBan
        $result = $this->tkbModel->getDanhSachMonHocGV_CheckBan($ma_lop, $thu, $tiet, $date_check, $ma_hoc_ky);

        // 5. Trả về JSON
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
?>
