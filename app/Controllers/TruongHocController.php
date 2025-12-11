<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class TruongHocController extends Controller {
    private $model;

    private function ensureModel() {
        if (!$this->model) {
            $this->model = $this->loadModel('TruongHocModel');
        }
        if (!$this->model) {
            die('Không thể load model TruongHocModel');
        }
    }

    /**
     * Trang quản lý trường học (Super Admin hoặc Admin trường xem phạm vi của mình).
     */
    public function index() {
        if (!isset($_SESSION['user_role'])) {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }

        if ($_SESSION['user_role'] !== 'QuanTriVien') {
            http_response_code(403);
            echo 'Bạn không có quyền truy cập chức năng này.';
            return;
        }

        $this->ensureModel();

        $adminSchoolId = $_SESSION['admin_school_id'] ?? null;
        $selectedId = $adminSchoolId ?: ($_GET['ma_truong'] ?? null);
        $selectedId = ($selectedId === '' ? null : $selectedId);

        $data = [
            'selected_school' => $selectedId,
            'school_options' => $this->model->getDanhSachTruongCoBan(),
            'schools' => $this->model->getThongKeTruong($selectedId)
        ];

        echo $this->loadView('SuperAdmin/quan_ly_truong_hoc', $data);
    }

    /**
     * API trả JSON cho frontend fetch.
     */
    public function api() {
        if (!$this->is_ajax()) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad request']);
            return;
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'QuanTriVien') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $this->ensureModel();
        $adminSchoolId = $_SESSION['admin_school_id'] ?? null;
        $selectedId = $adminSchoolId ?: ($_GET['ma_truong'] ?? null);
        $selectedId = ($selectedId === '' ? null : $selectedId);

        $rows = $this->model->getThongKeTruong($selectedId);

        header('Content-Type: application/json');
        echo json_encode(['data' => $rows]);
    }
}
