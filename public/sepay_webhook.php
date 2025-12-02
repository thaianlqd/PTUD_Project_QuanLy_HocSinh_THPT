<?php
// File public/sepay_webhook.php
// Đảm bảo file này được đặt trong thư mục public/ và có thể truy cập công khai qua URL
// Ví dụ: https://your-domain.com/public/sepay_webhook.php

// KHÔNG CẦN session_start()

// ===== BẢO ĐẢM PATH (Tùy theo cấu trúc Framework của bạn) =====
// Đảm bảo các file này tồn tại và được load đúng cách từ thư mục gốc
require_once '../app/Core/App.php'; 
require_once '../app/Models/ThanhToanModel.php'; 
// =================================================================

header('Content-Type: application/json; charset=utf-8');
error_log("[SEPAY WEBHOOK] START");

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true); // Decode thành mảng associative

error_log("[SEPAY WEBHOOK] Received RAW: " . $json_data); // Log dữ liệu thô nhận được

// Kiểm tra tính hợp lệ cơ bản
if (!is_array($data) || ($data['transferType'] ?? null) !== "in" || ($data['transferAmount'] ?? 0) <= 0) {
    http_response_code(400);
    echo json_encode(['success' => FALSE, 'message' => 'Invalid data or not an incoming transaction']);
    error_log("[SEPAY WEBHOOK] Invalid data received or type mismatch.");
    die();
}

$transaction_content = $data['content'] ?? '';
$amount_in = floatval($data['transferAmount']); 
$reference_number_sepay = $data['referenceCode'] ?? ''; 

// ======================================================
// === KHẮC PHỤC LỖI REGEX: Xóa dấu gạch dưới (HOCPHI_XX -> HOCPHIXX) ===
// Regex mới tìm 'HOCPHI' liền với một hoặc nhiều số (\d+)
$regex = '/HOCPHI(\d+)/'; 
// ======================================================

preg_match($regex, $transaction_content, $matches);

$ma_hoa_don_goc = null;
if (isset($matches[1])) {
    $ma_hoa_don_goc = intval($matches[1]);
}

if (!$ma_hoa_don_goc) {
    // Không tìm thấy mã HĐ -> Giao dịch không hợp lệ cho việc tự động xác thực
    echo json_encode(['success' => TRUE, 'message' => 'Order reference (HOCPHIxx) not found in content. Ignoring.']);
    error_log("[SEPAY WEBHOOK] Ref code not found in content: " . $transaction_content . ". Stopping processing.");
    
    // TRẢ VỀ TRUE để Sepay không cố gắng gọi lại Webhook này (RspCode 200 OK)
    // vì đây không phải là lỗi tạm thời của Server bạn, mà là lỗi nội dung giao dịch.
    die();
}

// Gọi Model để cập nhật DB
try {
    $thanhToanModel = new ThanhToanModel(); // Khởi tạo Model
    
    // Sử dụng hàm đã sửa đổi trong Model
    $updateResult = $thanhToanModel->xacNhanThanhToanSepay($ma_hoa_don_goc, $amount_in, $reference_number_sepay);

    if ($updateResult['success']) {
        error_log("[SEPAY WEBHOOK] HĐ: $ma_hoa_don_goc Processed OK.");
        // Mã RspCode 200 OK
        echo json_encode(['success' => TRUE, 'message' => 'Order processed successfully.']);
    } else {
        error_log("[SEPAY WEBHOOK] HĐ: $ma_hoa_don_goc Failed. Lỗi: " . $updateResult['message']);
        
        // Sepay KHÔNG sử dụng RspCode VNPAY (00/99). Chỉ cần trả về 200 OK 
        // để họ ngừng gửi lại. Sepay sẽ tự ghi nhận message lỗi trong lịch sử Webhook.
        echo json_encode(['success' => FALSE, 'message' => $updateResult['message']]); 
    }
} catch (Exception $e) {
    http_response_code(500); // Lỗi nghiêm trọng của Server -> Sepay có thể cố gọi lại
    error_log("[SEPAY WEBHOOK] Exception (Internal): " . $e->getMessage());
    echo json_encode(['success' => FALSE, 'message' => 'Internal server error.']);
}
?>