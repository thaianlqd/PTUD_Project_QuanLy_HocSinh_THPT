<?php
session_start();

// --- CẤU HÌNH TỰ ĐỘNG (AUTO CONFIG) ---
// 1. Tự động lấy giao thức (http hoặc https)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";

// 2. Tự động lấy domain (localhost:88 hoặc ...ngrok-free.app)
$host = $_SERVER['HTTP_HOST'];

// 3. Đường dẫn thư mục gốc (ĐÃ SỬA CHUẨN)
// Phải bao gồm cả 2 cấp thư mục thì link mới chạy đúng
$rootFolder = '/PTUD_Project_QLHS_THPT/PTUD_Project_QuanLy_HocSinh_THPT/public';

// 4. Định nghĩa BASE_URL
define('BASE_URL', $protocol . $host . $rootFolder);

// Tải các file lõi của ứng dụng
require_once '../app/Core/App.php';
require_once '../app/Core/Controller.php';
require_once '../app/Core/Router.php';

// Khởi động ứng dụng
$app = new App();