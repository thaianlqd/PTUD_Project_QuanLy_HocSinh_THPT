<?php
class HomeController extends Controller {
    
    public function index() {
        // 1. GỌI MODEL LẤY TIN TỨC
        $modelPath = dirname(__DIR__) . '/models/TinTucModel.php';
        $tinTucNoiBat = [];

        if (file_exists($modelPath)) {
            require_once $modelPath;
            try {
                $newsModel = new TinTucModel();
                // Lấy 3 tin mới nhất có trạng thái 'CongKhai'
                $tinTucNoiBat = $newsModel->getBaiVietCongKhai(3);
            } catch (Exception $e) {
                $tinTucNoiBat = [];
            }
        }

        // 2. CHUẨN BỊ DỮ LIỆU
        $data = [
            'tin_tuc' => $tinTucNoiBat,
            'title'   => 'Trang Chủ - Hệ Thống Quản Lý'
        ];

        // 3. BUNG DỮ LIỆU VÀ GỌI VIEW
        extract($data); // Biến $data['tin_tuc'] thành $tin_tuc

        $viewPath = dirname(__DIR__) . '/views/home/index.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Fallback
            $viewPathRoot = dirname(__DIR__) . '/views/index.php';
            if (file_exists($viewPathRoot)) require_once $viewPathRoot;
        }
    }
}
?>