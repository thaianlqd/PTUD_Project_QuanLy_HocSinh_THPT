<?php
class Router {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // --- FIXED: ĐÃ XÓA SPECIAL ROUTE cho 'xeptkb' ---
        // Router chỉ nên điều hướng, không nên tự load view.
        // Việc này đã được chuyển cho QuanTriController@xeptkb
        // --------------------------------------------------

        // 1. Xử lý Controller
        if (!empty($url[0])) {
            $controllerName = ucwords($url[0]) . 'Controller';
            $controllerPath = '../app/Controllers/' . $controllerName . '.php';
            if (file_exists($controllerPath)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }
        
        $controllerPath = '../app/Controllers/' . $this->controller . '.php';
        if (!file_exists($controllerPath)) {
            die('Error: Controller file "' . $controllerPath . '" not found!');
        }
        require_once $controllerPath;
        $this->controller = new $this->controller;

        // 2. Xử lý Method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // 3. Xử lý Params
        $this->params = $url ? array_values($url) : [];

        // Gọi method
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return array_filter($url, 'strlen');
        }
        return [];
    }

    // FIXED: Xóa 'loadView' - Nó đã có trong Base Controller
}
?>
