<?php
session_start();

// ĐỊNH NGHĨA BASE URL (Sửa GD_Full_ChucNang nếu tên thư mục của bạn khác)
// define('BASE_URL', 'http://localhost:88/GD_Full_ChucNang/public'); // <--- SỬA DÒNG NÀY
define('BASE_URL', 'https://unentwined-johanne-biasedly.ngrok-free.dev/GD_Full_ChucNang/public');

// Tải các file lõi của ứng dụng
require_once '../app/Core/App.php';
require_once '../app/Core/Controller.php';
require_once '../app/Core/Router.php';

// Khởi động ứng dụng
$app = new App();