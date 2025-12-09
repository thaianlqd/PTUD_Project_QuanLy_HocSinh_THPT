<?php
// ThanhToanController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/VNPAYHelper.php'; // đảm bảo file này tồn tại và có các hàm tĩnh cần thiết

class ThanhToanController extends Controller
{
    private $thanhToanModel;
    private $ma_phu_huynh;
    private $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    private $vnp_TmnCode = "MEBLXEDU";
    private $vnp_HashSecret = "T718SPDGIGQSKGM98VCSNAF70M9X93MC";


    // CẤU HÌNH SEPAY/QR
    private $qr_AccNum = "025452790502"; // SỐ TÀI KHOẢN MBBank demo
    private $qr_BankCode = "MBBank";
    private $qr_Prefix = "HOCPHI"; // Tiền tố mã thanh toán (ví dụ: HOCPHI_20)

    public function __construct()
    {
        // ===== SỬA LỖI: Đã xóa kiểm tra session khỏi __construct =====
        // Việc kiểm tra session ở đây đã chặn VNPAY gọi ipn_callback
        
        $this->ma_phu_huynh = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        
        // Load model (giả sử Controller::loadModel trả về instance model)
        $this->thanhToanModel = $this->loadModel('ThanhToanModel');
    }

    /**
     * ===== HÀM MỚI: Kiểm tra quyền đăng nhập của Phụ Huynh =====
     * Chỉ gọi ở những hàm CẦN user đăng nhập (index, taoYeuCau,...)
     */
    private function checkAuth()
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'PhuHuynh') {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }
        
        // Sau khi checkAuth, chúng ta chắc chắn $ma_phu_huynh là hợp lệ
        if ($this->ma_phu_huynh <= 0) {
            die("Lỗi: Phiên đăng nhập không hợp lệ.");
        }
    }

    // Trang danh sách hóa đơn
    public function index()
    {
        $this->checkAuth();

        try {
            // Lấy danh sách
            $hoa_don_chua_tt = $this->thanhToanModel->getHoaDonChuaThanhToan($this->ma_phu_huynh);
            $hoa_don_da_tt = $this->thanhToanModel->getHoaDonDaThanhToan($this->ma_phu_huynh);
            $hoa_don_cho_xac_nhan = $this->thanhToanModel->getHoaDonChoXacNhanTaiTruong($this->ma_phu_huynh);

            // --- XỬ LÝ LOGIC QUÁ HẠN TẠI ĐÂY ---
            // Duyệt qua mảng để gán cờ 'qua_han'
            if (is_array($hoa_don_chua_tt)) {
                foreach ($hoa_don_chua_tt as &$hd) {
                    // Logic: Nếu ngày hết hạn < giờ hiện tại => Quá hạn
                    // Model trả về true/false, ta gán thẳng vào mảng
                    $hd['qua_han'] = $this->thanhToanModel->kiemTraQuaHan($hd['ma_hoa_don']);
                }
                unset($hd); // Hủy tham chiếu
            }
            // ------------------------------------

            $data = [
                'user_name' => $_SESSION['user_name'] ?? 'Phụ Huynh',
                'hoa_don_chua_tt' => $hoa_don_chua_tt,
                'hoa_don_cho_xac_nhan' => $hoa_don_cho_xac_nhan,
                'hoa_don_da_tt' => $hoa_don_da_tt,
                'flash_message' => $_SESSION['flash_message'] ?? null
            ];
            unset($_SESSION['flash_message']);

            $content = $this->loadView('PhuHuynh/thanh_toan_hoc_phi', $data);
            echo $content;
        } catch (Exception $e) {
            error_log("Lỗi index: " . $e->getMessage());
            die("Lỗi tải trang.");
        }
    }

    // Tạo yêu cầu thanh toán (sử dụng VNPAY)
    public function taoYeuCau()
    {
        $this->checkAuth(); // <-- SỬA LỖI: Thêm kiểm tra ở đây
        
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        $ma_hoa_don = filter_input(INPUT_POST, 'ma_hoa_don', FILTER_VALIDATE_INT);
        $phuong_thuc = isset($_POST['phuong_thuc']) ? trim($_POST['phuong_thuc']) : '';

        if (!$ma_hoa_don || $phuong_thuc === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn hóa đơn và phương thức thanh toán.']);
            exit;
        }

        // Lấy chi tiết hóa đơn
        $hoa_don = $this->thanhToanModel->getHoaDonChiTiet($ma_hoa_don, $this->ma_phu_huynh);
        if (!$hoa_don || (isset($hoa_don['trang_thai_hoa_don']) && $hoa_don['trang_thai_hoa_don'] === 'DaThanhToan')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Hóa đơn không hợp lệ hoặc đã thanh toán.']);
            exit;
        }

        $vnp_ReturnUrl = rtrim(BASE_URL, '/') . '/thanhtoan/ketQua';
        $noi_dung = isset($hoa_don['ghi_chu']) ? $hoa_don['ghi_chu'] : 'Thanh toán học phí';

        // ===== XỬ LÝ SỐ TIỀN AN TOÀN (PHIÊN BẢN ĐÃ SỬA LỖI SỐ TIỀN) =====
        $so_tien_raw = (string)($hoa_don['thanh_tien'] ?? '0'); // VD: "560000.00"
        
        // Lấy phần số nguyên trước dấu thập phân
        $so_tien_parts = explode('.', $so_tien_raw);
        $so_tien = (int)$so_tien_parts[0]; // -> 560000

        if ($so_tien <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Số tiền thanh toán không hợp lệ: ' . $so_tien_raw]);
            exit;
        }
        // ===== KẾT THÚC XỬ LÝ SỐ TIỀN =====

        // Lấy IP client (hỗ trợ proxy)
        $ip_addr = $this->getClientIP();

        // Tạo redirect url VNPAY
        try {
            $redirect_url = VNPAYHelper::taoUrlThanhToan(
                $this->vnp_TmnCode,
                $this->vnp_HashSecret,
                $this->vnp_Url,
                $ma_hoa_don,
                $so_tien, // Đã là số int (560000)
                $noi_dung,
                $vnp_ReturnUrl,
                $ip_addr
            );

            echo json_encode(['success' => true, 'redirect_url' => $redirect_url]);
            exit;
        } catch (Exception $e) {
            error_log("[ThanhToanController@taoYeuCau] Lỗi tạo URL VNPAY: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi khi khởi tạo thanh toán.']);
            exit;
        }
    }

    // Ghi nhận yêu cầu thanh toán tiền mặt (in phiếu)
    public function taoYeuCauTienMat()
    {
        $this->checkAuth(); // <-- SỬA LỖI: Thêm kiểm tra ở đây

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        $ma_hoa_don = filter_input(INPUT_POST, 'ma_hoa_don', FILTER_VALIDATE_INT);
        if (!$ma_hoa_don) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn hóa đơn.']);
            exit;
        }

        $hoa_don = $this->thanhToanModel->getHoaDonChiTiet($ma_hoa_don, $this->ma_phu_huynh);
        if (!$hoa_don || (isset($hoa_don['trang_thai_hoa_don']) && $hoa_don['trang_thai_hoa_don'] === 'DaThanhToan')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Hóa đơn không hợp lệ.']);
            exit;
        }

        // Cập nhật trạng thái tạm (ví dụ: DangChoThanhToanTienMat)
        $result = $this->thanhToanModel->capNhatTrangThaiTam($ma_hoa_don);
        if (!$result) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật trạng thái.']);
            exit;
        }

        $stt_phieu = date('Ymd') . str_pad($ma_hoa_don, 4, '0', STR_PAD_LEFT);
        $dia_diem = 'Phòng Tài vụ - Tầng 1, Trường THPT [Tên Trường]';
        $thoi_han = isset($hoa_don['ngay_het_han']) ? date('d/m/Y', strtotime($hoa_don['ngay_het_han'])) : '';

        // Chuyển tiền sang định dạng hiển thị
        $so_tien_sach = isset($hoa_don['thanh_tien']) ? preg_replace('/[^0-9]/', '', (string)$hoa_don['thanh_tien']) : '0';
        $so_tien_formatted = number_format((int)$so_tien_sach, 0, ',', '.');

        $_SESSION['phieu_thong_bao'] = [
            'ma_hoa_don' => $ma_hoa_don,
            'stt_phieu' => $stt_phieu,
            'ten_phu_huynh' => $_SESSION['user_name'] ?? 'Phụ huynh',
            'ten_con' => $hoa_don['ten_hoc_sinh'] ?? 'Tên Học Sinh', // nếu có
            'noi_dung' => $hoa_don['ghi_chu'] ?? '',
            'so_tien' => $so_tien_formatted,
            'thoi_han' => $thoi_han,
            'dia_diem' => $dia_diem,
            'ngay_tao' => date('d/m/Y H:i:s')
        ];

        $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Đã ghi nhận yêu cầu thanh toán tiền mặt. In phiếu để mang theo.'];
        echo json_encode(['success' => true, 'print_url' => rtrim(BASE_URL, '/') . '/thanhtoan/inPhieu?ma_hoa_don=' . $ma_hoa_don]);
        exit;
    }

    // In phiếu (dựa trên session)
    // public function inPhieu()
    // {
    //     $this->checkAuth(); // <-- SỬA LỖI: Thêm kiểm tra ở đây

    //     $ma_hoa_don = filter_input(INPUT_GET, 'ma_hoa_don', FILTER_VALIDATE_INT);
    //     if (!$ma_hoa_don || !isset($_SESSION['phieu_thong_bao']) || $_SESSION['phieu_thong_bao']['ma_hoa_don'] != $ma_hoa_don) {
    //         header('Location: ' . BASE_URL . '/thanhtoan/index');
    //         exit;
    //     }

    //     $data = $_SESSION['phieu_thong_bao'];
    //     unset($_SESSION['phieu_thong_bao']); // chỉ in 1 lần
    //     $content = $this->loadView('PhuHuynh/phieu_thong_bao', $data);
    //     echo $content;
    // }
    // public function inPhieu()
    // {
    //     $this->checkAuth();

    //     $ma_hoa_don = filter_input(INPUT_GET, 'ma_hoa_don', FILTER_VALIDATE_INT);
    //     if (!$ma_hoa_don) die("Mã hóa đơn không hợp lệ");

    //     // 1. Lấy chi tiết hóa đơn từ DB (phải đảm bảo đúng của phụ huynh này)
    //     $hoa_don = $this->thanhToanModel->getHoaDonChiTiet($ma_hoa_don, $this->ma_phu_huynh);

    //     // 2. Chỉ cho in nếu Đã Thanh Toán hoặc đang Chờ xác nhận tiền mặt
    //     if (!$hoa_don || ($hoa_don['trang_thai_hoa_don'] !== 'DaThanhToan' && $hoa_don['trang_thai_tam'] !== 'ChoThanhToanTaiTruong')) {
    //         die("Hóa đơn này chưa được thanh toán hoặc không tồn tại.");
    //     }

    //     // 3. Chuẩn bị dữ liệu hiển thị (Mapping từ DB sang View)
    //     // Tạo số thứ tự phiếu giả lập từ mã HĐ
    //     $stt_phieu = date('Ymd', strtotime($hoa_don['ngay_lap_hoa_don'])) . str_pad($ma_hoa_don, 4, '0', STR_PAD_LEFT);
        
    //     // Format số tiền
    //     $so_tien_sach = preg_replace('/[^0-9]/', '', (string)$hoa_don['thanh_tien']);
    //     $so_tien_formatted = number_format((int)$so_tien_sach, 0, ',', '.');

    //     $data = [
    //         'ma_hoa_don'    => $ma_hoa_don,
    //         'stt_phieu'     => $stt_phieu,
    //         'ten_phu_huynh' => $_SESSION['user_name'] ?? 'Phụ huynh',
    //         'ten_con'       => $hoa_don['ten_hoc_sinh'] ?? 'Học sinh', // Nếu model chưa join bảng học sinh thì bác check lại query
    //         'noi_dung'      => $hoa_don['ghi_chu'],
    //         'so_tien'       => $so_tien_formatted,
    //         'thoi_han'      => isset($hoa_don['ngay_het_han']) ? date('d/m/Y', strtotime($hoa_don['ngay_het_han'])) : '',
    //         'dia_diem'      => 'Hệ thống thanh toán trực tuyến', // Sửa lại chút vì online
    //         'ngay_tao'      => date('d/m/Y H:i:s')
    //     ];
        
    //     // Nếu thanh toán tiền mặt thì đổi địa điểm
    //     if ($hoa_don['hinh_thuc_thanh_toan'] == 'TienMat' || $hoa_don['trang_thai_tam'] == 'ChoThanhToanTaiTruong') {
    //          $data['dia_diem'] = 'Phòng Tài vụ - Tầng 1, Trường THPT';
    //     }

    //     $content = $this->loadView('PhuHuynh/phieu_thong_bao', $data);
    //     echo $content;
    // }
    public function inPhieu()
    {
        $this->checkAuth();

        $ma_hoa_don = filter_input(INPUT_GET, 'ma_hoa_don', FILTER_VALIDATE_INT);
        if (!$ma_hoa_don) die("Mã hóa đơn không hợp lệ");

        $hoa_don = $this->thanhToanModel->getHoaDonChiTiet($ma_hoa_don, $this->ma_phu_huynh);

        if (!$hoa_don || ($hoa_don['trang_thai_hoa_don'] !== 'DaThanhToan' && $hoa_don['trang_thai_tam'] !== 'ChoThanhToanTaiTruong')) {
            die("Hóa đơn này chưa được thanh toán hoặc không tồn tại.");
        }

        $stt_phieu = date('Ymd', strtotime($hoa_don['ngay_lap_hoa_don'])) . str_pad($ma_hoa_don, 4, '0', STR_PAD_LEFT);
        
        // --- [SỬA LỖI 20 TRIỆU TẠI ĐÂY] ---
        // Cách cũ bị sai do xóa dấu chấm thập phân: preg_replace...
        
        // Cách mới: Ép kiểu float chuẩn chỉ
        $val = floatval($hoa_don['thanh_tien']); 
        $so_tien_formatted = number_format($val, 0, ',', '.');
        // ----------------------------------

        $data = [
            'ma_hoa_don'    => $ma_hoa_don,
            'stt_phieu'     => $stt_phieu,
            'ten_phu_huynh' => $_SESSION['user_name'] ?? 'Phụ huynh',
            // ... (các phần dưới giữ nguyên)
            'ten_con'       => $hoa_don['ten_hoc_sinh'] ?? 'Học sinh',
            'noi_dung'      => $hoa_don['ghi_chu'],
            'so_tien'       => $so_tien_formatted, // Đã sửa
            'thoi_han'      => isset($hoa_don['ngay_het_han']) ? date('d/m/Y', strtotime($hoa_don['ngay_het_han'])) : '',
            'dia_diem'      => 'Hệ thống thanh toán trực tuyến',
            'ngay_tao'      => date('d/m/Y H:i:s')
        ];
        
        if ($hoa_don['hinh_thuc_thanh_toan'] == 'TienMat' || $hoa_don['trang_thai_tam'] == 'ChoThanhToanTaiTruong') {
             $data['dia_diem'] = 'Phòng Tài vụ - Tầng 1, Trường THPT';
        }

        $content = $this->loadView('PhuHuynh/phieu_thong_bao', $data);
        echo $content;
    }

    /**
     * IPN endpoint để VNPAY gọi (server-to-server)
     * ===== SỬA LỖI: HÀM NÀY KHÔNG ĐƯỢC CHECKAUTH =====
     */
    public function ipn_callback()
    {
        // VNPAY thường gửi GET hoặc POST; ta lấy raw query string để tránh router sửa $_GET
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        parse_str($queryString, $params);

        // Nếu không có querystring, thử lấy from POST (phòng trường hợp)
        if (empty($params) && !empty($_POST)) {
            $params = $_POST;
        }

        // Xóa param 'url' nếu VNPAY gửi sai (đã gặp trường hợp này)
        if (isset($params['url'])) {
            unset($params['url']);
        }
        
        // Xác thực chữ ký
        $result = VNPAYHelper::xacThucIPN($this->vnp_HashSecret, $params);

        error_log("[IPN] Nhận callback VNPAY: " . json_encode($params));
        error_log("[IPN] Kết quả xác thực: " . json_encode($result));

        header('Content-Type: application/json; charset=utf-8');

        if ($result['success']) {
            // order_ref có thể là "1234_xyz" -> lấy mã hóa đơn gốc trước dấu gạch dưới
            $order_ref = $result['order_ref'] ?? '';
            $ma_hoa_don_goc = (strpos($order_ref, '_') !== false) ? explode('_', $order_ref)[0] : $order_ref;
            $ma_giao_dich_vnpay = $result['ma_giao_dich'] ?? ($params['vnp_TransactionNo'] ?? null);
            
            // Sửa lỗi: VNPAY trả về vnp_Amount = 56000000 (đã * 100)
            // Model của bạn cần số tiền gốc (560000)
            $so_tien_vnpay = (int)($result['so_tien'] ?? ($params['vnp_Amount'] ?? 0));
            $so_tien_goc = $so_tien_vnpay; // -> 560000

            try {
                $updateResult = $this->thanhToanModel->xacNhanThanhToan($ma_hoa_don_goc, $ma_giao_dich_vnpay, "VNPAY", $so_tien_goc);

                if ($updateResult['success']) {
                    error_log("[IPN] Cập nhật HĐ: $ma_hoa_don_goc THÀNH CÔNG.");
                    echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
                } else {
                    error_log("[IPN] Lỗi CSDL khi cập nhật HĐ: $ma_hoa_don_goc. Lỗi: " . ($updateResult['message'] ?? 'unknown'));
                    // Quan trọng: Báo cho VNPAY biết là có lỗi CSDL (ví dụ: sai tiền)
                    echo json_encode(['RspCode' => '99', 'Message' => $updateResult['message'] ?? 'DB error']);
                }
            } catch (Exception $e) {
                error_log("[IPN] Exception khi cập nhật DB: " . $e->getMessage());
                echo json_encode(['RspCode' => '99', 'Message' => 'Exception: ' . $e->getMessage()]);
            }
        } else {
            error_log("[IPN] Giao dịch không hợp lệ/giả mạo: " . ($result['message'] ?? 'Invalid'));
            echo json_encode(['RspCode' => '01', 'Message' => $result['message'] ?? 'Invalid signature']);
        }
        exit;
    }

    /**
     * Trang trả về khi user quay lại (return URL)
     * ===== SỬA LỖI: HÀM NÀY KHÔNG ĐƯỢC CHECKAUTH =====
     */
    // public function ketQua()
    // {
    //     // Lấy query string gốc
    //     $queryString = $_SERVER['QUERY_STRING'] ?? '';
    //     parse_str($queryString, $params);

    //     // Nếu không có params từ query, thử $_GET
    //     if (empty($params) && !empty($_GET)) {
    //         $params = $_GET;
    //     }

    //     if (isset($params['url'])) {
    //         unset($params['url']);
    //     }

    //     // Chỉ xác thực chữ ký, không vội cập nhật DB (IPN sẽ cập nhật chính thức)
    //     $result = VNPAYHelper::xacThucIPN($this->vnp_HashSecret, $params);

    //     if ($result['success']) {
    //         $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thanh toán thành công! Hệ thống đang xử lý, vui lòng chờ cập nhật.'];
    //     } else {
    //         $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Thanh toán thất bại, bị hủy hoặc chữ ký không hợp lệ.'];
    //     }

    //     header('Location: ' . rtrim(BASE_URL, '/') . '/thanhtoan/index');
    //     exit;
    // }
    // public function ketQua()
    // {
    //     $queryString = $_SERVER['QUERY_STRING'] ?? '';
    //     parse_str($queryString, $params);
    //     if (empty($params) && !empty($_GET)) $params = $_GET;
    //     if (isset($params['url'])) unset($params['url']);

    //     $result = VNPAYHelper::xacThucIPN($this->vnp_HashSecret, $params);

    //     if ($result['success']) {
    //         $vnp_TxnRef = $params['vnp_TxnRef'] ?? '';
    //         $parts = explode('_', $vnp_TxnRef);
    //         $ma_hoa_don = $parts[0];
    //         $ma_giao_dich = $params['vnp_TransactionNo'] ?? 'VNPAY_REF';
    //         $so_tien = ($params['vnp_Amount'] ?? 0) / 100;

    //         // Gọi Model update
    //         $updateDB = $this->thanhToanModel->xacNhanThanhToan(
    //             $ma_hoa_don, 
    //             $ma_giao_dich, 
    //             'VNPAY', 
    //             $so_tien
    //         );

    //         if ($updateDB['success']) {
    //             // Trường hợp chuẩn: Cập nhật thành công
    //             $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thanh toán VNPAY thành công!'];
    //         } else {
    //             // Trường hợp đã thanh toán rồi (do reload trang)
    //             if (strpos($updateDB['message'], 'đã được thanh toán') !== false) {
    //                  $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Giao dịch đã được ghi nhận thành công.'];
    //             } else {
    //                 // Lỗi khác (sai tiền, lỗi DB...)
    //                  $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: ' . $updateDB['message']];
    //             }
    //         }

    //     } else {
    //         $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi xác thực chữ ký VNPAY.'];
    //     }

    //     header('Location: ' . rtrim(BASE_URL, '/') . '/thanhtoan/index');
    //     exit;
    // }
    public function ketQua()
    {
        // 1. Lấy dữ liệu trả về từ VNPAY
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        parse_str($queryString, $params);
        
        // Fallback nếu query string rỗng
        if (empty($params) && !empty($_GET)) {
            $params = $_GET;
        }
        
        // Loại bỏ param 'url' nếu có (do htaccess/router thêm vào)
        if (isset($params['url'])) {
            unset($params['url']);
        }

        // 2. Xác thực chữ ký bảo mật
        $result = VNPAYHelper::xacThucIPN($this->vnp_HashSecret, $params);

        if ($result['success']) {
            // --- Lấy thông tin giao dịch ---
            $vnp_TxnRef = $params['vnp_TxnRef'] ?? '';
            $parts = explode('_', $vnp_TxnRef);
            $ma_hoa_don = $parts[0]; // ID hóa đơn gốc
            
            $ma_giao_dich = $params['vnp_TransactionNo'] ?? 'VNPAY_UNKNOWN';
            $so_tien = ($params['vnp_Amount'] ?? 0) / 100; // VNPAY nhân 100 nên phải chia lại

            // 3. Cập nhật vào Database (Ghi rõ 'VNPAY')
            $updateDB = $this->thanhToanModel->xacNhanThanhToan(
                $ma_hoa_don, 
                $ma_giao_dich, 
                'VNPAY', 
                $so_tien
            );

            if ($updateDB['success']) {
                // ==> TRƯỜNG HỢP 1: Cập nhật thành công lần đầu
                $_SESSION['flash_message'] = [
                    'type' => 'success', 
                    'message' => 'Thanh toán VNPAY thành công! Đang mở phiếu in...'
                ];
                
                // [QUAN TRỌNG] Lưu ID vào session để bên View tự bật tab in
                $_SESSION['print_invoice_id'] = $ma_hoa_don; 
                
            } else {
                // ==> TRƯỜNG HỢP 2: Có lỗi (hoặc đã thanh toán rồi do refresh trang)
                
                // Kiểm tra xem có phải lỗi do "Đã thanh toán trước đó" không
                if (strpos($updateDB['message'], 'đã được thanh toán') !== false) {
                     $_SESSION['flash_message'] = [
                         'type' => 'info', 
                         'message' => 'Giao dịch đã được ghi nhận trước đó.'
                     ];
                     
                     // [QUAN TRỌNG] Vẫn cho in phiếu lại nếu user lỡ refresh
                     $_SESSION['print_invoice_id'] = $ma_hoa_don;
                } else {
                    // Lỗi thật sự (sai tiền, lỗi DB...)
                    $_SESSION['flash_message'] = [
                        'type' => 'danger', 
                        'message' => 'Lỗi cập nhật: ' . $updateDB['message']
                    ];
                }
            }

        } else {
            // Lỗi chữ ký (Hack hoặc sai key)
            $_SESSION['flash_message'] = [
                'type' => 'danger', 
                'message' => 'Lỗi xác thực chữ ký VNPAY. Giao dịch không hợp lệ.'
            ];
        }

        // 4. Quay về trang danh sách
        header('Location: ' . rtrim(BASE_URL, '/') . '/thanhtoan/index');
        exit;
    }

    /**
     * Lấy IP client an toàn (xem xét proxy)
     */
    private function getClientIP()
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($keys as $k) {
            if (!empty($_SERVER[$k])) {
                // HTTP_X_FORWARDED_FOR có thể chứa nhiều IP, lấy IP đầu tiên
                $ipList = explode(',', $_SERVER[$k]);
                $ip = trim($ipList[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }


    // ThanhToanController.php

    //... (Bên trong class ThanhToanController)

    // Tạo yêu cầu thanh toán (sử dụng Sepay QR)
    public function taoYeuCauSepay()
    {
        $this->checkAuth();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        $ma_hoa_don = filter_input(INPUT_POST, 'ma_hoa_don', FILTER_VALIDATE_INT);

        if (!$ma_hoa_don) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn hóa đơn.']);
            exit;
        }

        $hoa_don = $this->thanhToanModel->getHoaDonChiTiet($ma_hoa_don, $this->ma_phu_huynh);
        if (!$hoa_don || (isset($hoa_don['trang_thai_hoa_don']) && $hoa_don['trang_thai_hoa_don'] === 'DaThanhToan')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Hóa đơn không hợp lệ hoặc đã thanh toán.']);
            exit;
        }
        
        // --- Lấy thông tin cần thiết ---
        $so_tien = (float)($hoa_don['thanh_tien'] ?? 0);
        
        // Tạo Mã Tham Chiếu: Tiền tố (HOCPHI) + Mã HĐ
        // Sepay QR chỉ nhận số nguyên, nên làm sạch số tiền
        $amount_int = intval(round($so_tien)); 
        $ref_code = $this->qr_Prefix . $ma_hoa_don;
        
        // URL QR động từ Sepay
        $qr_img_url = "https://qr.sepay.vn/img?bank={$this->qr_BankCode}&acc={$this->qr_AccNum}&template=compact&amount={$amount_int}&des={$ref_code}";
        
        // Cập nhật trạng thái tạm (để người khác không thanh toán cùng lúc)
        $this->thanhToanModel->capNhatTrangThaiTam($ma_hoa_don, 'ChoThanhToanSepayQR');

        echo json_encode([
            'success' => true, 
            'payment_details' => [
                'ma_hoa_don' => $ma_hoa_don,
                'so_tien' => $amount_int,
                'noi_dung' => $hoa_don['ghi_chu'] ?? 'Thanh toán học phí',
                'ref_code' => $ref_code,
                'qr_img_url' => $qr_img_url
            ]
        ]);
        exit;
    }

    // ThanhToanController.php (Thêm vào class ThanhToanController)

    /**
     * Endpoint AJAX để kiểm tra trạng thái thanh toán của hóa đơn Sepay (Polling)
     */
    public function checkSepayStatus()
    {
        // KHÔNG cần checkAuth vì đây là AJAX/Polling ngắn hạn
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ma_hoa_don'])) {
            http_response_code(400);
            echo json_encode(['trang_thai_hoa_don' => 'error', 'message' => 'Invalid request.']);
            exit;
        }

        $ma_hoa_don = filter_input(INPUT_POST, 'ma_hoa_don', FILTER_VALIDATE_INT);

        // Lấy trạng thái hóa đơn chỉ bằng ID (không cần check ma_phu_huynh cho Polling)
        $ma_hoa_don = filter_input(INPUT_POST, 'ma_hoa_don', FILTER_VALIDATE_INT);
    
        try {
            // *** Dùng hàm mới của Model thay vì truy cập $this->thanhToanModel->db ***
            $trang_thai = $this->thanhToanModel->getTrangThaiHoaDon($ma_hoa_don); 

            if ($trang_thai) {
                echo json_encode(['trang_thai_hoa_don' => $trang_thai]);
            } else {
                echo json_encode(['trang_thai_hoa_don' => 'order_not_found']);
            }

        } catch (Exception $e) {
            error_log("[checkSepayStatus] Error: " . $e->getMessage());
            echo json_encode(['trang_thai_hoa_don' => 'error', 'message' => 'DB error.']);
        }
        exit;
    }
}