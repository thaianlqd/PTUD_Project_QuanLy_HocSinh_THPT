<?php
// File này không cần thay đổi
class HomeController extends Controller {
    
    public function index() {
        $content = $this->loadView('index');
        echo $content;
    }
}
?>
