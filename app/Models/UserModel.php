<?php
class UserModel {
    private $db; // PDO connection

    public function __construct() {
        // Hãy chắc chắn port 3307 là đúng với XAMPP của bạn
        $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
        try {
            $this->db = new PDO($dsn, 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->exec("SET NAMES 'utf8mb4'"); // Thêm SET NAMES
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            $this->db = null;
            die("Không thể kết nối CSDL (UserModel): " . $e->getMessage()); // Hiển thị lỗi rõ ràng
        }
    }

    public function checkLogin($username, $password) {
        if ($this->db === null) return false;

        $hashed_password = md5($password);

        $sql = "SELECT 
                    tk.ma_tai_khoan, 
                    tk.vai_tro, 
                    nd.ho_ten,
                    nd.ma_nguoi_dung,

                    -- Lấy chức vụ từ giáo viên hoặc quản trị viên
                    COALESCE(gv.chuc_vu, qtv.chuc_vu) AS chuc_vu,

                    -- Lấy mã lớp của học sinh
                    hs.ma_lop
                FROM 
                    tai_khoan AS tk
                JOIN 
                    nguoi_dung AS nd ON tk.ma_tai_khoan = nd.ma_tai_khoan

                -- Join bảng giáo viên
                LEFT JOIN 
                    giao_vien AS gv ON nd.ma_nguoi_dung = gv.ma_giao_vien

                -- Join bảng quản trị viên
                LEFT JOIN 
                    quan_tri_vien AS qtv ON nd.ma_nguoi_dung = qtv.ma_qtv

                -- Join bảng học sinh
                LEFT JOIN
                    hoc_sinh AS hs ON nd.ma_nguoi_dung = hs.ma_hoc_sinh

                WHERE 
                    (tk.username = :username 
                    OR nd.email = :username 
                    OR nd.so_dien_thoai = :username)
                    AND tk.password = :password
                    AND tk.trang_thai = 'HoatDong'";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password
        ]);

        $user = $stmt->fetch();

        if ($user) {
            return [
                'id' => $user['ma_nguoi_dung'],
                'ma_tai_khoan' => $user['ma_tai_khoan'],
                'name' => $user['ho_ten'],
                'role' => $user['vai_tro'],
                'chuc_vu' => $user['chuc_vu'],
                'ma_lop' => $user['ma_lop']
            ];
        }

        return false;
    }

    
    /**
     * Lấy thông tin cơ bản của Học Sinh (cho dashboard)
     */
    public function getStudentInfo($ma_hoc_sinh) {
         if ($this->db === null) return null;
         $sql = "SELECT 
                    hs.ma_hoc_sinh, 
                    nd.ho_ten, 
                    l.ten_lop 
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                WHERE hs.ma_hoc_sinh = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_hoc_sinh]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getStudentInfo: " . $e->getMessage());
            return null;
        }
    }

    // Các hàm này giả lập dữ liệu, cần được thay thế bằng query CSDL thật
    public function getHocSinhData($ma_nguoi_dung) {
        if ($this->db === null) return [];
        // TODO: Viết query CSDL thật cho các hàm này
        $diem_tb = 8.5; 
        $bai_chua_nop = 2; 
        $lich_tuan = 5; 
        $diem_mon = ['Toán' => 8.0, 'Văn' => 7.5]; 
        return [
            'diem_tb' => $diem_tb,
            'bai_chua_nop' => $bai_chua_nop,
            'lich_tuan' => $lich_tuan,
            'diem_mon' => $diem_mon
        ];
    }
    
    // --- CÁC HÀM CHO DASHBOARD QUẢN TRỊ (ĐÃ SỬA) ---

    public function getTotalUsers() {
        if ($this->db === null) return 0;
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM tai_khoan WHERE trang_thai = 'HoatDong'");
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getTotalLop() {
        if ($this->db === null) return 0;
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM lop_hoc WHERE trang_thai_lop = 'HoatDong'");
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getTotalHs() {
        if ($this->db === null) return 0;
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM hoc_sinh WHERE trang_thai = 'DangHoc'");
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getTkByRole() {
        if ($this->db === null) return [];
        $stmt = $this->db->query("SELECT vai_tro, COUNT(*) as count FROM tai_khoan GROUP BY vai_tro");
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['vai_tro']] = $row['count'];
        }
        return $result;
    }

    public function getSiSoKhoi() {
        if ($this->db === null) return [];
        // Sửa: Thêm WHERE trang_thai_lop
        $stmt = $this->db->query("SELECT khoi, SUM(si_so) as si_so FROM lop_hoc WHERE trang_thai_lop = 'HoatDong' GROUP BY khoi");
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $khoi = 'Khối ' . $row['khoi'];
            $result[$khoi] = $row['si_so'];
        }
        return $result;
    }


    /**
     * === PHẦN SỬA LỖI (QUAN TRỌNG) ===
     * Sửa câu query để trả về đúng các key mà View (dashboard.php) cần
     */
    public function getAllUsers($limit = 10) {
        if ($this->db === null) return [];
        
        $limit_int = (int)$limit; // Đảm bảo $limit là số nguyên

        // Sửa câu SQL:
        // 1. SELECT nd.ho_ten (View cần 'ho_ten')
        // 2. SELECT nd.so_dien_thoai (View cần 'so_dien_thoai')
        // 3. SELECT tk.vai_tro (View cần 'vai_tro')
        // 4. SELECT tk.trang_thai (View cần 'trang_thai')
        // 5. SELECT tk.ngay_tao_tai_khoan (View cần 'ngay_tao_tai_khoan')
        $sql = "
            SELECT 
                tk.username, 
                tk.vai_tro, 
                tk.trang_thai, 
                tk.ngay_tao_tai_khoan, 
                nd.ho_ten,
                nd.so_dien_thoai,
                nd.ma_nguoi_dung AS id 
            FROM tai_khoan tk
            JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan
            ORDER BY tk.ngay_tao_tai_khoan DESC 
            LIMIT :limit_val
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit_val', $limit_int, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Đảm bảo trả về mảng kết hợp
        } catch (PDOException $e) {
            error_log("Lỗi getAllUsers: " . $e->getMessage());
            return []; // Trả về mảng rỗng nếu có lỗi
        }
    }
    // --- (Kết thúc phần sửa) ---


    // --- CÁC HÀM KHÁC (STUBS - Cần được phát triển) ---
    public function getPhuHuynhData($user_id) { return []; }
    public function getThiSinhData($user_id) { return []; }
    public function getGiaoVienData($user_id) { return []; }
    public function getNhanVienSoGDData($user_id) { return []; }
}
?>

