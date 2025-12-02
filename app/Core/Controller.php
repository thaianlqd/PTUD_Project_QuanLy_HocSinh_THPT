<?php
// File này không cần thay đổi
class Controller {

    public function loadModel($model) {
        $modelPath = '../app/Models/' . $model . '.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }
        return null;
    }

    public function loadView($view, $data = []) {
        extract($data);
        $viewPath = '../app/Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            ob_start();
            require_once $viewPath;
            return ob_get_clean();
        } else {
            die("View không tồn tại: " . $viewPath);
        }
    }

    /**
     * HÀM MỚI: Kiểm tra xem đây có phải là request AJAX/Fetch không
     * Trả về true nếu là API, false nếu là người dùng gõ URL
     */
    protected function is_ajax() {
        // Kiểm tra header 'X-Requested-With' (thường do jQuery/axios gửi)
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        
        // Kiểm tra header 'Accept' (thường do fetch() gửi)
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }
        
        return false;
    }
}
?>
