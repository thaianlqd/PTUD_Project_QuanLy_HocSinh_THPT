<?php
/**
 * GiaoVienModel: Xử lý logic CSDL cho CRUD Giáo viên
 */
class GiaoVienModel {
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
            error_log('DB Connection failed (GiaoVienModel): ' . $e->getMessage());
            $this->db = null;
            die("Không thể kết nối CSDL (GiaoVienModel): " . $e->getMessage());
        }
    }

    /**
     * Lấy tất cả giáo viên (JOIN 3 bảng)
     */
    public function getDanhSachGiaoVien() {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    nd.ma_nguoi_dung,
                    tk.ma_tai_khoan,
                    tk.username,
                    tk.trang_thai,
                    nd.ho_ten,
                    nd.so_dien_thoai,
                    nd.email,
                    nd.dia_chi,
                    nd.ngay_sinh,
                    nd.gioi_tinh,
                    gv.chuc_vu,
                    gv.trinh_do_chuyen_mon,
                    gv.ngay_vao_truong
                FROM giao_vien gv
                JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                JOIN tai_khoan tk ON nd.ma_tai_khoan = tk.ma_tai_khoan
                ORDER BY nd.ho_ten";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachGiaoVien: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy chi tiết 1 giáo viên
     */
    public function getGiaoVienById($ma_nguoi_dung) {
         if ($this->db === null) return null;
         $sql = "SELECT 
                    nd.ma_nguoi_dung,
                    tk.ma_tai_khoan,
                    tk.username,
                    tk.trang_thai,
                    nd.ho_ten,
                    nd.so_dien_thoai,
                    nd.email,
                    nd.dia_chi,
                    nd.ngay_sinh,
                    nd.gioi_tinh,
                    gv.chuc_vu,
                    gv.trinh_do_chuyen_mon,
                    gv.ngay_vao_truong
                FROM giao_vien gv
                JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                JOIN tai_khoan tk ON nd.ma_tai_khoan = tk.ma_tai_khoan
                WHERE nd.ma_nguoi_dung = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_nguoi_dung]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getGiaoVienById: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Thêm mới 1 giáo viên (3 Bảng)
     */
    public function addGiaoVien($data) {
        if ($this->db === null) return false;
        
        $this->db->beginTransaction();
        try {
            // 1. Thêm vào tai_khoan
            $sql_tk = "INSERT INTO tai_khoan (username, password, vai_tro, trang_thai) 
                       VALUES (:email, MD5(:password), 'GiaoVien', 'HoatDong')";
            $stmt_tk = $this->db->prepare($sql_tk);
            $stmt_tk->execute([
                ':email' => $data['email'],
                ':password' => $data['password']
            ]);
            $ma_tai_khoan = $this->db->lastInsertId();

            // 2. Thêm vào nguoi_dung
            $sql_nd = "INSERT INTO nguoi_dung (ma_tai_khoan, ho_ten, email, so_dien_thoai, dia_chi, ngay_sinh, gioi_tinh) 
                       VALUES (:ma_tai_khoan, :ho_ten, :email, :so_dien_thoai, :dia_chi, :ngay_sinh, :gioi_tinh)";
            $stmt_nd = $this->db->prepare($sql_nd);
            $stmt_nd->execute([
                ':ma_tai_khoan' => $ma_tai_khoan,
                ':ho_ten' => $data['ho_ten'],
                ':email' => $data['email'],
                ':so_dien_thoai' => $data['so_dien_thoai'],
                ':dia_chi' => $data['dia_chi'],
                ':ngay_sinh' => $data['ngay_sinh'],
                ':gioi_tinh' => $data['gioi_tinh']
            ]);
            $ma_nguoi_dung = $this->db->lastInsertId();

            // 3. Thêm vào giao_vien
            $sql_gv = "INSERT INTO giao_vien (ma_giao_vien, chuc_vu, ngay_vao_truong, trinh_do_chuyen_mon) 
                       VALUES (:ma_giao_vien, :chuc_vu, :ngay_vao_truong, :trinh_do)";
            $stmt_gv = $this->db->prepare($sql_gv);
            $stmt_gv->execute([
                ':ma_giao_vien' => $ma_nguoi_dung,
                ':chuc_vu' => $data['chuc_vu'],
                ':ngay_vao_truong' => $data['ngay_vao_truong'],
                ':trinh_do' => $data['trinh_do']
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi addGiaoVien: " . $e->getMessage());
            // Trả về thông báo lỗi cụ thể (ví dụ: trùng SĐT/Email)
             if ($e->errorInfo[1] == 1062) { // Lỗi Duplicate entry
                 if (str_contains($e->getMessage(), 'so_dien_thoai')) return "Lỗi: Số điện thoại đã tồn tại.";
                 if (str_contains($e->getMessage(), 'email')) return "Lỗi: Email đã tồn tại.";
                 if (str_contains($e->getMessage(), 'username')) return "Lỗi: Tên đăng nhập (Email) đã tồn tại.";
             }
            return "Lỗi CSDL: " . $e->getMessage();
        }
    }

    /**
     * Cập nhật thông tin giáo viên (3 Bảng)
     */
    public function updateGiaoVien($data) {
        if ($this->db === null) return false;
        
        $this->db->beginTransaction();
        try {
            // 1. Cập nhật nguoi_dung
            $sql_nd = "UPDATE nguoi_dung SET 
                         ho_ten = :ho_ten, 
                         email = :email, 
                         so_dien_thoai = :so_dien_thoai, 
                         dia_chi = :dia_chi, 
                         ngay_sinh = :ngay_sinh, 
                         gioi_tinh = :gioi_tinh 
                       WHERE ma_nguoi_dung = :ma_nguoi_dung";
            $stmt_nd = $this->db->prepare($sql_nd);
            $stmt_nd->execute([
                ':ho_ten' => $data['ho_ten'],
                ':email' => $data['email'],
                ':so_dien_thoai' => $data['so_dien_thoai'],
                ':dia_chi' => $data['dia_chi'],
                ':ngay_sinh' => $data['ngay_sinh'],
                ':gioi_tinh' => $data['gioi_tinh'],
                ':ma_nguoi_dung' => $data['ma_nguoi_dung']
            ]);

            // 2. Cập nhật giao_vien
            $sql_gv = "UPDATE giao_vien SET 
                         chuc_vu = :chuc_vu, 
                         ngay_vao_truong = :ngay_vao_truong, 
                         trinh_do_chuyen_mon = :trinh_do 
                       WHERE ma_giao_vien = :ma_nguoi_dung";
            $stmt_gv = $this->db->prepare($sql_gv);
             $stmt_gv->execute([
                ':chuc_vu' => $data['chuc_vu'],
                ':ngay_vao_truong' => $data['ngay_vao_truong'],
                ':trinh_do' => $data['trinh_do'],
                ':ma_nguoi_dung' => $data['ma_nguoi_dung']
            ]);

            // 3. Cập nhật tai_khoan (username)
            $sql_tk = "UPDATE tai_khoan SET username = :email WHERE ma_tai_khoan = :ma_tai_khoan";
            $stmt_tk = $this->db->prepare($sql_tk);
            $stmt_tk->execute([
                ':email' => $data['email'],
                ':ma_tai_khoan' => $data['ma_tai_khoan']
            ]);

            // 4. (Tùy chọn) Cập nhật mật khẩu NẾU được cung cấp
            if (!empty($data['password'])) {
                $sql_pass = "UPDATE tai_khoan SET password = MD5(:password) WHERE ma_tai_khoan = :ma_tai_khoan";
                $stmt_pass = $this->db->prepare($sql_pass);
                $stmt_pass->execute([
                    ':password' => $data['password'],
                    ':ma_tai_khoan' => $data['ma_tai_khoan']
                ]);
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi updateGiaoVien: " . $e->getMessage());
             if ($e->errorInfo[1] == 1062) {
                 if (str_contains($e->getMessage(), 'so_dien_thoai')) return "Lỗi: Số điện thoại đã tồn tại.";
                 if (str_contains($e->getMessage(), 'email')) return "Lỗi: Email đã tồn tại.";
             }
            return "Lỗi CSDL: " . $e->getMessage();
        }
    }

    /**
     * Xóa giáo viên
     * Do CSDL có ON DELETE CASCADE, chỉ cần xóa ở bảng 'tai_khoan'
     */
    public function deleteGiaoVien($ma_tai_khoan) {
        if ($this->db === null) return false;
        
        // Chỉ cần xóa ở bảng cha, các bảng con (nguoi_dung, giao_vien) sẽ tự xóa
        $sql = "DELETE FROM tai_khoan WHERE ma_tai_khoan = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ma_tai_khoan]);
        } catch (PDOException $e) {
            error_log("Lỗi deleteGiaoVien: " . $e->getMessage());
             // Lỗi 1451: Không thể xóa vì GV đã được phân công TKB
             if ($e->errorInfo[1] == 1451) {
                 return "Lỗi: Không thể xóa giáo viên này vì họ đã được phân công giảng dạy. Vui lòng gỡ phân công trước.";
             }
            return "Lỗi CSDL: " . $e->getMessage();
        }
    }
}
?>
