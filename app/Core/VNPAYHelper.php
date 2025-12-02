<?php
/**
 * VNPAY Helper - PHIÊN BẢN 10.0 (Post-Processing Để Làm Sạch Translit Artifacts)
 * - removeVietnameseAccents: Normalizer ưu tiên; fallback iconv + str_replace để loại bỏ ' và ^.
 * - Đảm bảo OrderInfo chỉ chứa a-z, 0-9, space (ASCII thuần).
 */
class VNPAYHelper {

    /**
     * Hàm chuyển tiếng Việt có dấu thành không dấu (Ưu tiên Normalizer, fallback iconv + clean)
     */
    private static function removeVietnameseAccents($str) {
        // Ưu tiên: Normalizer (nếu intl bật)
        if (class_exists('Normalizer')) {
            $str = Normalizer::normalize($str, Normalizer::FORM_D);
            $str = preg_replace('/[\x{0300}-\x{036F}]/u', '', $str);
            // Thêm: Bỏ ký tự Đ, đ
            $str = str_replace(['Đ', 'đ'], ['D', 'd'], $str);
            $str = mb_strtolower($str, 'UTF-8');
            return trim($str);
        }

        // Fallback: iconv translit + clean special chars
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        if ($str === false) {
            $str = preg_replace('/[^\x00-\x7F]/u', '', $str); // Remove non-ASCII nếu iconv fail
        }
        // SỬA MỚI: Loại bỏ artifacts như ' (acute) và ^ (circumflex)
        $str = str_replace(["'", '^', '`'], '', $str); // Thêm `
        $str = preg_replace('/[^a-z0-9 ]/i', '', $str); // Chỉ giữ lại a-z, 0-9 và space
        $str = strtolower($str);
        return trim($str);
    }

    /**
     * HÀM TẠO URL - (Giữ nguyên v9.0, với log input)
     */
    public static function taoUrlThanhToan(
        $vnp_TmnCode,
        $vnp_HashSecret,
        $vnp_Url,
        $ma_hoa_don,
        $so_tien,
        $noi_dung,
        $returnUrl,
        $ip_addr
    ) {
        date_default_timezone_set('Asia/Ho_Chi_Minh'); // ĐÃ SỬA LỖI TYPO
        if ($ip_addr == '::1') { $ip_addr = '127.0.0.1'; }

        error_log("[VNPAY DEBUG - SENDING] Original noi_dung: " . $noi_dung);

        // Chuyển nội dung sang không dấu, ASCII
        $noi_dung_khong_dau = self::removeVietnameseAccents($noi_dung);

        $vnp_Params = [
            "vnp_Version"   => "2.1.0",
            "vnp_Command"   => "pay",
            "vnp_TmnCode"   => $vnp_TmnCode,
            "vnp_Amount"    => $so_tien * 100,
            "vnp_CreateDate"=> date('YmdHis'),
            "vnp_CurrCode"  => "VND",
            "vnp_IpAddr"    => $ip_addr,
            "vnp_Locale"    => "vn",
            "vnp_OrderInfo" => $noi_dung_khong_dau, // Dùng nội dung đã xử lý
            "vnp_OrderType" => "other", 
            "vnp_ReturnUrl" => $returnUrl,
            "vnp_TxnRef"    => $ma_hoa_don . "_" . time(),
            "vnp_ExpireDate"=> date('YmdHis', strtotime('+15 minutes'))
        ];

        ksort($vnp_Params);
        
        $hashData = "";
        $query = "";
        $i = 0;

        foreach ($vnp_Params as $key => $value) {
            if ($value === null || $value === '') { continue; }

            $encodedKey = urlencode($key);
            $encodedValue = urlencode($value); 

            $query .= $encodedKey . "=" . $encodedValue . "&";

            if ($i > 0) { $hashData .= '&'; }
            $hashData .= $encodedKey . "=" . $encodedValue;
            $i = 1;
        }
        
        $query = rtrim($query, '&');
        
        error_log("[VNPAY DEBUG - SENDING] HashData: " . $hashData);
        error_log("[VNPAY DEBUG - SENDING] OrderInfo (no accents): " . $noi_dung_khong_dau);
        error_log("[VNPAY DEBUG - SENDING] OrderInfo (encoded): " . urlencode($noi_dung_khong_dau));

        $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $urlThanhToan = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

        return $urlThanhToan;
    }


    /**
     * HÀM XÁC THỰC - (v11.0: Sửa xử lý ngrok - chỉ unset 'url', không ghi đè)
     */
    public static function xacThucIPN($vnp_HashSecret, $inputData) {
        if (!isset($inputData['vnp_SecureHash'])) {
            return ['success' => false, 'message' => 'Thiếu vnp_SecureHash'];
        }
        
        $receivedHash = $inputData['vnp_SecureHash'];

        // SỬA: Chỉ unset 'url' từ ngrok, KHÔNG ghi đè vnp_ReturnUrl
        // (Vì Controller đã tự chèn lại 'vnp_ReturnUrl' rồi)
        if (isset($inputData['url'])) {
            unset($inputData['url']);
        }
        
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);
        
        ksort($inputData);
        
        $hashData = "";
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            $encodedKey = urlencode($key);
            $encodedValue = urlencode($value);
            
            if ($i > 0) {
                $hashData .= '&';
            }
            $hashData .= $encodedKey . "=" . $encodedValue;
            $i = 1;
        }

        $calculatedHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        global $_SERVER; 
        error_log("[VNPAY DEBUG - RECEIVING] Raw QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'N/A'));

        error_log("[VNPAY DEBUG - RECEIVING] HashData (v11.0): " . $hashData);
        error_log("[VNPAY DEBUG - RECEIVING] vnp_ReturnUrl (raw after parse): " . ($inputData['vnp_ReturnUrl'] ?? 'N/A'));
        error_log("[VNPAY DEBUG - RECEIVING] Received Hash: " . $receivedHash);
        error_log("[VNPAY DEBUG - RECEIVING] Calculated Hash: " . $calculatedHash);
        error_log("[VNPAY DEBUG - RECEIVING] OrderInfo (received): " . ($inputData['vnp_OrderInfo'] ?? 'N/A'));

        if ($receivedHash !== $calculatedHash) {
            return ['success' => false, 'message' => 'Chữ ký không hợp lệ! (Kiểm tra raw QUERY_STRING nếu dùng ngrok)'];
        }

        if ($inputData['vnp_ResponseCode'] === '00') {
            return [
                'success' => true, 'message' => 'Thanh toán thành công',
                'ma_giao_dich' => $inputData['vnp_TransactionNo'],
                'so_tien' => $inputData['vnp_Amount'] / 100,
                'bank' => $inputData['vnp_BankCode'] ?? '',
                'order_ref' => $inputData['vnp_TxnRef']
            ];
        }
        
        return ['success' => false, 'message' => 'Giao dịch thất bại (Mã lỗi: ' . $inputData['vnp_ResponseCode'] . ')'];
    }
}
?>