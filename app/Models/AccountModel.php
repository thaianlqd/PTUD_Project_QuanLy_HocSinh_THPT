<?php
/**
 * AccountModel: Xử lý logic CSDL cho CRUD tài khoảnn
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
     * Lấy tài khoản nhưng chỉ thuộc về 1 trường cụ thể (lọc theo ma_truong)
     * Hiển thị HS của trường, GV có phân công dạy tại trường, và QTV của trường.
     */
    public function getAccountsBySchool($school_id) {
        if ($this->db === null) return [];
        $sql = "SELECT DISTINCT
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
                -- Liên kết HS -> Lớp -> Trường
                LEFT JOIN hoc_sinh hs ON nd.ma_nguoi_dung = hs.ma_hoc_sinh
                LEFT JOIN lop_hoc lh_hs ON hs.ma_lop = lh_hs.ma_lop
                -- Liên kết GV qua bảng phân công -> Lớp -> Trường
                LEFT JOIN bang_phan_cong bpc ON bpc.ma_giao_vien = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc lh_bpc ON bpc.ma_lop = lh_bpc.ma_lop
                -- Liên kết QTV
                LEFT JOIN quan_tri_vien qtv ON nd.ma_nguoi_dung = qtv.ma_qtv
                                WHERE (lh_hs.ma_truong = :sid OR lh_bpc.ma_truong = :sid OR qtv.ma_truong = :sid)
                ORDER BY nd.ho_ten";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $school_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getAccountsBySchool: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số tài khoản (không phân trang)
     */
    public function countAllAccounts() {
        if ($this->db === null) return 0;
        $sql = "SELECT COUNT(*) FROM tai_khoan WHERE vai_tro <> 'QuanTriVien'";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi countAllAccounts: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Đếm tổng số tài khoản theo trường
     */
    public function countAccountsBySchool($school_id) {
        if ($this->db === null) return 0;
        $sql = "SELECT COUNT(DISTINCT tk.ma_tai_khoan)
                FROM tai_khoan tk
                JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan
                LEFT JOIN hoc_sinh hs ON nd.ma_nguoi_dung = hs.ma_hoc_sinh
                LEFT JOIN lop_hoc lh_hs ON hs.ma_lop = lh_hs.ma_lop
                LEFT JOIN bang_phan_cong bpc ON bpc.ma_giao_vien = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc lh_bpc ON bpc.ma_lop = lh_bpc.ma_lop
                LEFT JOIN quan_tri_vien qtv ON nd.ma_nguoi_dung = qtv.ma_qtv
                WHERE (lh_hs.ma_truong = :sid OR lh_bpc.ma_truong = :sid OR qtv.ma_truong = :sid)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $school_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi countAccountsBySchool: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy tất cả tài khoản với phân trang (mới nhất trước)
     */
    public function getAllAccountsPaginated($page = 1, $limit = 10) {
        if ($this->db === null) return [];
        $offset = ($page - 1) * $limit;
        
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
                WHERE tk.vai_tro <> 'QuanTriVien'
                ORDER BY tk.ma_tai_khoan DESC
                LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getAllAccountsPaginated: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tài khoản theo trường với phân trang (mới nhất trước)
     */
    public function getAccountsBySchoolPaginated($school_id, $page = 1, $limit = 10) {
        if ($this->db === null) return [];
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT DISTINCT
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
                LEFT JOIN hoc_sinh hs ON nd.ma_nguoi_dung = hs.ma_hoc_sinh
                LEFT JOIN lop_hoc lh_hs ON hs.ma_lop = lh_hs.ma_lop
                LEFT JOIN bang_phan_cong bpc ON bpc.ma_giao_vien = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc lh_bpc ON bpc.ma_lop = lh_bpc.ma_lop
                LEFT JOIN quan_tri_vien qtv ON nd.ma_nguoi_dung = qtv.ma_qtv
                WHERE (lh_hs.ma_truong = :sid OR lh_bpc.ma_truong = :sid OR qtv.ma_truong = :sid)
                ORDER BY tk.ma_tai_khoan DESC
                LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':sid', $school_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getAccountsBySchoolPaginated: " . $e->getMessage());
            return [];
        }
    }

    /**
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
    // private function checkDuplicates($email, $so_dien_thoai) {
    //     try {
    //         // 1. Kiểm tra username (email)
    //         $stmt = $this->db->prepare("SELECT 1 FROM tai_khoan WHERE username = ?");
    //         $stmt->execute([$email]);
    //         if ($stmt->fetch()) {
    //             return "Email (Tên đăng nhập) đã tồn tại.";
    //         }

    //         // 2. Kiểm tra email (trong nguoi_dung)
    //         $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE email = ?");
    //         $stmt->execute([$email]);
    //         if ($stmt->fetch()) {
    //             return "Email đã tồn tại.";
    //         }

    //         // 3. Kiểm tra SĐT (nếu có)
    //         if (!empty($so_dien_thoai)) {
    //             $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE so_dien_thoai = ?");
    //             $stmt->execute([$so_dien_thoai]);
    //             if ($stmt->fetch()) {
    //                 return "Số điện thoại đã tồn tại.";
    //             }
    //         }
    //         return true; // Không trùng
    //     } catch (PDOException $e) {
    //         error_log("Lỗi checkDuplicates: " . $e->getMessage());
    //         return "Lỗi máy chủ khi kiểm tra dữ liệu.";
    //     }
    // }
    private function checkDuplicates($email, $so_dien_thoai = '') {
        $stmt = $this->db->prepare("SELECT 1 FROM tai_khoan WHERE username = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) return "Email (tên đăng nhập) đã tồn tại.";

        $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) return "Email đã được sử dụng.";

        if (!empty($so_dien_thoai)) {
            $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE so_dien_thoai = ?");
            $stmt->execute([$so_dien_thoai]);
            if ($stmt->fetch()) return "Số điện thoại đã tồn tại.";
        }
        return true;
    }

    /**
     * HÀM MỚI: Lấy danh sách lớp theo trường
     * (Chuyển từ TuyenSinhModel sang đây cho đúng nghiệp vụ cấp tài khoản)
     */
    public function getDanhSachLop($ma_truong) {
        if ($this->db === null) return [];
        
        $sql = "SELECT ma_lop, ten_lop 
                FROM lop_hoc 
                WHERE ma_truong = :id 
                ORDER BY ten_lop ASC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $ma_truong]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HÀM MỚI: Cấp tài khoản mới (Sử dụng Transaction)
     * $school_id: Nếu có, sẽ ghi ma_truong vào bảng role
     */
    // public function createAccount($data, $school_id = null) {
    //     if ($this->db === null) return "Lỗi kết nối CSDL.";

    //     // 1. Kiểm tra trùng lặp trước
    //     $check = $this->checkDuplicates($data['email'], $data['so_dien_thoai']);
    //     if ($check !== true) {
    //         return $check; // Trả về thông báo lỗi
    //     }

    //     $this->db->beginTransaction();
    //     try {
    //         // Bảng 1: Tạo tai_khoan
    //         $sql_tk = "INSERT INTO tai_khoan (username, password, vai_tro) 
    //                    VALUES (:username, MD5(:password), :vai_tro)";
    //         $stmt_tk = $this->db->prepare($sql_tk);
    //         $stmt_tk->execute([
    //             ':username' => $data['email'], // Dùng email làm username
    //             ':password' => $data['password'],
    //             ':vai_tro' => $data['vai_tro']
    //         ]);
    //         $ma_tai_khoan = $this->db->lastInsertId();

    //         // Bảng 2: Tạo nguoi_dung
    //         $sql_nd = "INSERT INTO nguoi_dung (ma_tai_khoan, ho_ten, email, so_dien_thoai, dia_chi, ngay_sinh, gioi_tinh) 
    //                    VALUES (:ma_tai_khoan, :ho_ten, :email, :so_dien_thoai, :dia_chi, :ngay_sinh, :gioi_tinh)";
    //         $stmt_nd = $this->db->prepare($sql_nd);
    //         $stmt_nd->execute([
    //             ':ma_tai_khoan' => $ma_tai_khoan,
    //             ':ho_ten' => $data['ho_ten'],
    //             ':email' => $data['email'],
    //             ':so_dien_thoai' => $data['so_dien_thoai'],
    //             ':dia_chi' => $data['dia_chi'] ?? null,
    //             ':ngay_sinh' => $data['ngay_sinh'] ?? null,
    //             ':gioi_tinh' => $data['gioi_tinh'] ?? null
    //         ]);
    //         $ma_nguoi_dung = $this->db->lastInsertId();

    //         // 
    //         // Bảng 3: Tạo vai trò cụ thể (và ghi ma_truong nếu có)
    //         $sql_role = "";
    //         $params_role = [$ma_nguoi_dung];
    //         switch ($data['vai_tro']) {
    //             case 'GiaoVien':
    //                 if ($school_id) {
    //                     $sql_role = "INSERT INTO giao_vien (ma_giao_vien, ma_truong) VALUES (?, ?)";
    //                     $params_role[] = $school_id;
    //                 } else {
    //                     $sql_role = "INSERT INTO giao_vien (ma_giao_vien) VALUES (?)";
    //                 }
    //                 break;
    //             case 'HocSinh':
    //                 // HocSinh không có ma_truong trực tiếp, nhưng qua ma_lop → ma_truong
    //                 // Không ghi ở đây, để sau khi tạo admin sẽ gán học sinh vào lớp
    //                 $sql_role = "INSERT INTO hoc_sinh (ma_hoc_sinh) VALUES (?)";
    //                 break;
    //             case 'PhuHuynh':
    //                 $sql_role = "INSERT INTO phu_huynh (ma_phu_huynh) VALUES (?)";
    //                 break;
    //             case 'NhanVienSoGD':
    //                 $sql_role = "INSERT INTO nhan_vien_so_gd (ma_nv_so) VALUES (?)";
    //                 break;
    //             case 'ThiSinh':
    //                 $sql_role = "INSERT INTO thi_sinh (ma_nguoi_dung) VALUES (?)";
    //                 break;
    //             case 'BanGiamHieu':
    //                 if ($school_id) {
    //                     $sql_role = "INSERT INTO ban_giam_hieu (ma_bgd, ma_truong) VALUES (?, ?)";
    //                     $params_role[] = $school_id;
    //                 } else {
    //                     $sql_role = "INSERT INTO ban_giam_hieu (ma_bgd) VALUES (?)";
    //                 }
    //                 break;
    //         }

    //         if (!empty($sql_role)) {
    //             $stmt_role = $this->db->prepare($sql_role);
    //             $stmt_role->execute($params_role);
    //         }

    //         $this->db->commit();
    //         return true;

    //     } catch (PDOException $e) {
    //         $this->db->rollBack();
    //         error_log("Lỗi createAccount: " . $e->getMessage());
            
    //         // Sửa lỗi "vàng" (tương thích PHP 7)
    //         if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
    //             return "Dữ liệu (Email hoặc SĐT) bị trùng lặp.";
    //         }
    //         return "Lỗi hệ thống khi tạo tài khoản.";
    //     }
    // }
    public function createAccount($data) {
        $school_id = $_SESSION['admin_school_id'] ?? null;
        if (!$school_id) return "Không xác định được trường của admin.";

        $check = $this->checkDuplicates($data['email'], $data['so_dien_thoai'] ?? '');
        if ($check !== true) return $check;

        $this->db->beginTransaction();
        try {
            // 1. Tạo tài khoản
            $stmt = $this->db->prepare("INSERT INTO tai_khoan (username, password, vai_tro, trang_thai) 
                                        VALUES (?, MD5(?), ?, 'HoatDong')");
            $stmt->execute([$data['email'], $data['password'], $data['vai_tro']]);
            $ma_tai_khoan = $this->db->lastInsertId();

            // 2. Tạo người dùng
            $stmt = $this->db->prepare("INSERT INTO nguoi_dung 
                (ma_tai_khoan, ho_ten, email, so_dien_thoai, dia_chi, ngay_sinh, gioi_tinh) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $ma_tai_khoan,
                $data['ho_ten'],
                $data['email'],
                $data['so_dien_thoai'] ?? null,
                $data['dia_chi'] ?? null,
                $data['ngay_sinh'] ?? null,
                $data['gioi_tinh'] ?? null
            ]);
            $ma_nguoi_dung = $this->db->lastInsertId();

            // 3. Xử lý theo vai trò
            switch ($data['vai_tro']) {
                case 'HocSinh':
                    if (empty($data['ma_lop'])) return "Vui lòng chọn lớp cho học sinh.";
                    $stmt = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ma_lop, ma_truong) VALUES (?, ?, ?)");
                    $stmt->execute([$ma_nguoi_dung, $data['ma_lop'], $school_id]);
                    break;

                case 'GiaoVien':
                    $stmt = $this->db->prepare("INSERT INTO giao_vien (ma_giao_vien, ma_truong) VALUES (?, ?)");
                    $stmt->execute([$ma_nguoi_dung, $school_id]);

                    if (!empty($data['mon_chuyen_mon'])) {
                        $stmt = $this->db->prepare("INSERT INTO giao_vien_mon_hoc (ma_giao_vien, ma_mon_hoc) VALUES (?, ?)");
                        $stmt->execute([$ma_nguoi_dung, $data['mon_chuyen_mon']]);
                    }
                    break;

                case 'BanGiamHieu':
                    $stmt = $this->db->prepare("INSERT INTO ban_giam_hieu (ma_bgd, ma_truong) VALUES (?, ?)");
                    $stmt->execute([$ma_nguoi_dung, $school_id]);
                    break;

                case 'PhuHuynh':
                    if (empty($data['hoc_sinh_con']) || !is_array($data['hoc_sinh_con'])) {
                        return "Vui lòng chọn ít nhất một học sinh.";
                    }
                    $stmt = $this->db->prepare("INSERT INTO phu_huynh (ma_phu_huynh, ma_truong) VALUES (?, ?)");
                    $stmt->execute([$ma_nguoi_dung, $school_id]);

                    $stmt_rel = $this->db->prepare("INSERT INTO phu_huynh_hoc_sinh (ma_phu_huynh, ma_hoc_sinh) VALUES (?, ?)");
                    foreach ($data['hoc_sinh_con'] as $ma_hs) {
                        $stmt_rel->execute([$ma_nguoi_dung, $ma_hs]);
                    }
                    break;

                default:
                    return "Vai trò không được hỗ trợ.";
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi createAccount: " . $e->getMessage());
            return "Lỗi hệ thống: " . $e->getMessage();
        }
    }
    /**
     * Lấy danh sách vai trò có sẵn cho admin cấp trường
     * Chỉ trả về: HocSinh, PhuHuynh, GiaoVien, BanGiamHieu
     * Loại bỏ: NhanVienSoGD, ThiSinh, QuanTriVien
     */
    public function getAvailableRolesForSchoolAdmin() {
        return [
            'HocSinh' => 'Học Sinh',
            'PhuHuynh' => 'Phụ Huynh',
            'GiaoVien' => 'Giáo Viên',
            'BanGiamHieu' => 'Ban Giám Hiệu'
        ];
    }
}
?>
