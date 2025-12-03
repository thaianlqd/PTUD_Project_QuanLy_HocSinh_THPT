<?php
/**
 * AccountModel: Xử lý logic CSDL cho CRUD tài khoản
 */
class AccountModel {
    private $db;

    public function __construct() {
        // Danh sách các port cần thử (Ưu tiên 3307 trước, nếu lỗi thì thử 3306)
        $ports = [3307, 3306]; 
        $connected = false;

        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                
                // Cấu hình PDO chuẩn
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // QUAN TRỌNG: Để true để dùng lại được tham số (ví dụ :username dùng 3 lần)
                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); 
                
                $this->db->exec("SET NAMES 'utf8mb4'");
                
                $connected = true;
                break; 
            } catch (PDOException $e) {
                continue;
            }
        }

        if (!$connected) {
            die("Lỗi: Không thể kết nối CSDL (Port 3306/3307). Kiểm tra XAMPP.");
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

    /**
     * HÀM MỚI (private): Kiểm tra email/sdt đã tồn tại chưa
     */
    private function checkDuplicates($email, $so_dien_thoai) {
        try {
            // 1. Kiểm tra username (email)
            $stmt = $this->db->prepare("SELECT 1 FROM tai_khoan WHERE username = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return "Email (Tên đăng nhập) đã tồn tại.";
            }

            // 2. Kiểm tra email (trong nguoi_dung)
            $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return "Email đã tồn tại.";
            }

            // 3. Kiểm tra SĐT (nếu có)
            if (!empty($so_dien_thoai)) {
                $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE so_dien_thoai = ?");
                $stmt->execute([$so_dien_thoai]);
                if ($stmt->fetch()) {
                    return "Số điện thoại đã tồn tại.";
                }
            }
            return true; // Không trùng
        } catch (PDOException $e) {
            error_log("Lỗi checkDuplicates: " . $e->getMessage());
            return "Lỗi máy chủ khi kiểm tra dữ liệu.";
        }
    }

    /**
     * HÀM MỚI: Cấp tài khoản mới (Sử dụng Transaction)
     */
    public function createAccount($data) {
        if ($this->db === null) return "Lỗi kết nối CSDL.";

        // 1. Kiểm tra trùng lặp trước
        $check = $this->checkDuplicates($data['email'], $data['so_dien_thoai']);
        if ($check !== true) {
            return $check; // Trả về thông báo lỗi
        }

        $this->db->beginTransaction();
        try {
            // Bảng 1: Tạo tai_khoan
            $sql_tk = "INSERT INTO tai_khoan (username, password, vai_tro) 
                       VALUES (:username, MD5(:password), :vai_tro)";
            $stmt_tk = $this->db->prepare($sql_tk);
            $stmt_tk->execute([
                ':username' => $data['email'], // Dùng email làm username
                ':password' => $data['password'],
                ':vai_tro' => $data['vai_tro']
            ]);
            $ma_tai_khoan = $this->db->lastInsertId();

            // Bảng 2: Tạo nguoi_dung
            $sql_nd = "INSERT INTO nguoi_dung (ma_tai_khoan, ho_ten, email, so_dien_thoai, dia_chi, ngay_sinh, gioi_tinh) 
                       VALUES (:ma_tai_khoan, :ho_ten, :email, :so_dien_thoai, :dia_chi, :ngay_sinh, :gioi_tinh)";
            $stmt_nd = $this->db->prepare($sql_nd);
            $stmt_nd->execute([
                ':ma_tai_khoan' => $ma_tai_khoan,
                ':ho_ten' => $data['ho_ten'],
                ':email' => $data['email'],
                ':so_dien_thoai' => $data['so_dien_thoai'],
                ':dia_chi' => $data['dia_chi'] ?? null,
                ':ngay_sinh' => $data['ngay_sinh'] ?? null,
                ':gioi_tinh' => $data['gioi_tinh'] ?? null
            ]);
            $ma_nguoi_dung = $this->db->lastInsertId();

            // 
            // Bảng 3: Tạo vai trò cụ thể
            $sql_role = "";
            switch ($data['vai_tro']) {
                case 'GiaoVien':
                    $sql_role = "INSERT INTO giao_vien (ma_giao_vien) VALUES (?)";
                    break;
                case 'HocSinh':
                    $sql_role = "INSERT INTO hoc_sinh (ma_hoc_sinh) VALUES (?)";
                    break;
                case 'PhuHuynh':
                    $sql_role = "INSERT INTO phu_huynh (ma_phu_huynh) VALUES (?)";
                    break;
                case 'NhanVienSoGD':
                    $sql_role = "INSERT INTO nhan_vien_so_gd (ma_nv_so) VALUES (?)";
                    break;
                case 'ThiSinh':
                    $sql_role = "INSERT INTO thi_sinh (ma_nguoi_dung) VALUES (?)";
                    break;
            }

            if (!empty($sql_role)) {
                $stmt_role = $this->db->prepare($sql_role);
                $stmt_role->execute([$ma_nguoi_dung]);
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi createAccount: " . $e->getMessage());
            
            // Sửa lỗi "vàng" (tương thích PHP 7)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return "Dữ liệu (Email hoặc SĐT) bị trùng lặp.";
            }
            return "Lỗi hệ thống khi tạo tài khoản.";
        }
    }
}
?>
