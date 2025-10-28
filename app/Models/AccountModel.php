<?php
/**
 * AccountModel: Xử lý logic CSDL cho CRUD tài khoản
 */
class AccountModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL (Sử dụng port 3307 như CSDL của bạn)
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
            $this->db = new PDO($dsn, 'root', ''); // Sửa user/pass nếu cần
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->exec("SET NAMES 'utf8mb4'");
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            $this->db = null;
            die("Không thể kết nối CSDL: " . $e->getMessage());
        }
    }

    /**
     * Lấy tất cả tài khoản (JOIN tai_khoan và nguoi_dung)
     */
    public function getAllAccounts() {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    tk.ma_tai_khoan,
                    tk.username,
                    tk.vai_tro,
                    tk.trang_thai,
                    nd.ho_ten,
                    nd.so_dien_thoai,
                    nd.email,
                    nd.dia_chi,
                    nd.ngay_sinh,
                    nd.gioi_tinh
                FROM tai_khoan tk
                JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan
                WHERE tk.vai_tro <> 'QuanTriVien' -- Không cho admin tự sửa/xóa mình
                ORDER BY nd.ho_ten";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getAllAccounts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật thông tin tài khoản (cả 2 bảng)
     */
    public function updateAccount($data) {
        if ($this->db === null) return false;
        
        $this->db->beginTransaction();
        try {
            // 1. Cập nhật bảng nguoi_dung
            $sql_nd = "UPDATE nguoi_dung SET 
                         ho_ten = :ho_ten, 
                         email = :email, 
                         dia_chi = :dia_chi 
                       WHERE ma_tai_khoan = :ma_tai_khoan";
            
            $stmt_nd = $this->db->prepare($sql_nd);
            $stmt_nd->execute([
                ':ho_ten' => $data['ho_ten'],
                ':email' => $data['email'],
                ':dia_chi' => $data['dia_chi'],
                ':ma_tai_khoan' => $data['ma_tai_khoan']
            ]);

            // 2. Cập nhật bảng tai_khoan (username, vai_tro)
            $sql_tk = "UPDATE tai_khoan SET 
                         username = :username, 
                         vai_tro = :vai_tro
                       WHERE ma_tai_khoan = :ma_tai_khoan";
            
            $stmt_tk = $this->db->prepare($sql_tk);
            $stmt_tk->execute([
                ':username' => $data['email'], // Dùng email làm username
                ':vai_tro' => $data['vai_tro'],
                ':ma_tai_khoan' => $data['ma_tai_khoan']
            ]);

            // 3. (Tùy chọn) Cập nhật mật khẩu NẾU được cung cấp
            if (!empty($data['password'])) {
                $sql_pass = "UPDATE tai_khoan SET password = MD5(:password) WHERE ma_tai_khoan = :ma_tai_khoan";
                $stmt_pass = $this->db->prepare($sql_pass);
                $stmt_pass->execute([
                    ':password' => $data['password'],
                    ':ma_tai_khoan' => $data['ma_tai_khoan']
                ]);
            }

            $this->db->commit();
            return true; // Thành công

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi updateAccount: " . $e->getMessage());
            return false; // Thất bại
        }
    }

    /**
     * Xóa tài khoản (chỉ cần xóa ở bảng tai_khoan, CSDL sẽ tự xóa ở nguoi_dung)
     */
    public function deleteAccount($ma_tai_khoan) {
        if ($this->db === null) return false;
        
        // CSDL đã có ON DELETE CASCADE, nên chỉ cần xóa ở bảng cha (tai_khoan)
        $sql = "DELETE FROM tai_khoan WHERE ma_tai_khoan = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ma_tai_khoan]); // Trả về true nếu thành công
        } catch (PDOException $e) {
            error_log("Lỗi deleteAccount: " . $e->getMessage());
            return false;
        }
    }
}
?>
