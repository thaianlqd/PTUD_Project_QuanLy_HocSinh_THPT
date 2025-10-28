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
}
?>
