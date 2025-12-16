<?php
/**
 * GiaoVienModel: Xử lý logic CSDL cho CRUD Giáo viên
 */
class GiaoVienModel {
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
     * Lấy tất cả giáo viên (JOIN 3 bảng) - Có lọc theo trường
     */
    public function getDanhSachGiaoVien($school_id = null) {
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
                WHERE tk.trang_thai = 'HoatDong'";
        
        $params = [];
        if ($school_id) {
            $sql .= " AND gv.ma_truong = ?";
            $params[] = $school_id;
        }
        
        $sql .= " ORDER BY nd.ho_ten";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
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
            $sql_gv = "INSERT INTO giao_vien (ma_giao_vien, ma_truong, chuc_vu, ngay_vao_truong, trinh_do_chuyen_mon) 
                       VALUES (:ma_giao_vien, :ma_truong, :chuc_vu, :ngay_vao_truong, :trinh_do)";
            $stmt_gv = $this->db->prepare($sql_gv);
            $stmt_gv->execute([
                ':ma_giao_vien' => $ma_nguoi_dung,
                ':ma_truong' => $data['ma_truong'] ?? $_SESSION['admin_school_id'] ?? 1,
                ':chuc_vu' => $data['chuc_vu'],
                ':ngay_vao_truong' => $data['ngay_vao_truong'],
                ':trinh_do' => $data['trinh_do']
            ]);

            $this->db->commit();
            return $ma_nguoi_dung; // Return ID của GV vừa thêm

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
    // public function updateGiaoVien($data) {
    //     if ($this->db === null) return false;

    //     // Kiểm tra trùng số điện thoại (loại trừ chính giáo viên đang sửa)
    //     $sqlCheckSDT = "SELECT 1 FROM nguoi_dung WHERE so_dien_thoai = ? AND ma_nguoi_dung <> ?";
    //     $stmtCheckSDT = $this->db->prepare($sqlCheckSDT);
    //     $stmtCheckSDT->execute([$data['so_dien_thoai'], $data['ma_nguoi_dung']]);
    //     if ($stmtCheckSDT->fetchColumn()) {
    //         return "Lỗi: Số điện thoại đã tồn tại.";
    //     }

    //     // Kiểm tra trùng email (loại trừ chính giáo viên đang sửa)
    //     $sqlCheckEmail = "SELECT 1 FROM nguoi_dung WHERE email = ? AND ma_nguoi_dung <> ?";
    //     $stmtCheckEmail = $this->db->prepare($sqlCheckEmail);
    //     $stmtCheckEmail->execute([$data['email'], $data['ma_nguoi_dung']]);
    //     if ($stmtCheckEmail->fetchColumn()) {
    //         return "Lỗi: Email đã tồn tại.";
    //     }

    //     // Kiểm tra trùng username (loại trừ chính tài khoản đang sửa)
    //     $sqlCheckUsername = "SELECT 1 FROM tai_khoan WHERE username = ? AND ma_tai_khoan <> ?";
    //     $stmtCheckUsername = $this->db->prepare($sqlCheckUsername);
    //     $stmtCheckUsername->execute([$data['email'], $data['ma_tai_khoan']]);
    //     if ($stmtCheckUsername->fetchColumn()) {
    //         return "Lỗi: Tên đăng nhập (Email) đã tồn tại.";
    //     }

    //     $this->db->beginTransaction();
    //     try {
    //         // 1. Cập nhật nguoi_dung
    //         $sql_nd = "UPDATE nguoi_dung SET 
    //                     ho_ten = :ho_ten, 
    //                     email = :email, 
    //                     so_dien_thoai = :so_dien_thoai, 
    //                     dia_chi = :dia_chi, 
    //                     ngay_sinh = :ngay_sinh, 
    //                     gioi_tinh = :gioi_tinh 
    //                 WHERE ma_nguoi_dung = :ma_nguoi_dung";
    //         $stmt_nd = $this->db->prepare($sql_nd);
    //         $stmt_nd->execute([
    //             ':ho_ten' => $data['ho_ten'],
    //             ':email' => $data['email'],
    //             ':so_dien_thoai' => $data['so_dien_thoai'],
    //             ':dia_chi' => $data['dia_chi'],
    //             ':ngay_sinh' => $data['ngay_sinh'],
    //             ':gioi_tinh' => $data['gioi_tinh'],
    //             ':ma_nguoi_dung' => $data['ma_nguoi_dung']
    //         ]);

    //         // 2. Cập nhật giao_vien
    //         $sql_gv = "UPDATE giao_vien SET 
    //                     chuc_vu = :chuc_vu, 
    //                     ngay_vao_truong = :ngay_vao_truong, 
    //                     trinh_do_chuyen_mon = :trinh_do 
    //                 WHERE ma_giao_vien = :ma_nguoi_dung";
    //         $stmt_gv = $this->db->prepare($sql_gv);
    //         $stmt_gv->execute([
    //             ':chuc_vu' => $data['chuc_vu'],
    //             ':ngay_vao_truong' => $data['ngay_vao_truong'],
    //             ':trinh_do' => $data['trinh_do'],
    //             ':ma_nguoi_dung' => $data['ma_nguoi_dung']
    //         ]);

    //         // 3. Cập nhật tai_khoan (username)
    //         $sql_tk = "UPDATE tai_khoan SET username = :email WHERE ma_tai_khoan = :ma_tai_khoan";
    //         $stmt_tk = $this->db->prepare($sql_tk);
    //         $stmt_tk->execute([
    //             ':email' => $data['email'],
    //             ':ma_tai_khoan' => $data['ma_tai_khoan']
    //         ]);

    //         // 4. (Tùy chọn) Cập nhật mật khẩu NẾU được cung cấp
    //         if (!empty($data['password'])) {
    //             $sql_pass = "UPDATE tai_khoan SET password = MD5(:password) WHERE ma_tai_khoan = :ma_tai_khoan";
    //             $stmt_pass = $this->db->prepare($sql_pass);
    //             $stmt_pass->execute([
    //                 ':password' => $data['password'],
    //                 ':ma_tai_khoan' => $data['ma_tai_khoan']
    //             ]);
    //         }

    //         $this->db->commit();
    //         return true;

    //     } catch (PDOException $e) {
    //         $this->db->rollBack();
    //         error_log("Lỗi updateGiaoVien: " . $e->getMessage());
    //         return "Lỗi CSDL: " . $e->getMessage();
    //     }
    // }

    public function updateGiaoVien($data) {
        if ($this->db === null) return false;

        // Kiểm tra trùng số điện thoại (loại trừ chính giáo viên đang sửa)
        $sqlCheckSDT = "SELECT 1 FROM nguoi_dung WHERE so_dien_thoai = ? AND ma_nguoi_dung <> ?";
        $stmtCheckSDT = $this->db->prepare($sqlCheckSDT);
        $stmtCheckSDT->execute([$data['so_dien_thoai'], $data['ma_nguoi_dung']]);
        if ($stmtCheckSDT->fetchColumn()) {
            return "Lỗi: Số điện thoại đã tồn tại.";
        }

        // Kiểm tra trùng email (loại trừ chính giáo viên đang sửa)
        $sqlCheckEmail = "SELECT 1 FROM nguoi_dung WHERE email = ? AND ma_nguoi_dung <> ?";
        $stmtCheckEmail = $this->db->prepare($sqlCheckEmail);
        $stmtCheckEmail->execute([$data['email'], $data['ma_nguoi_dung']]);
        if ($stmtCheckEmail->fetchColumn()) {
            return "Lỗi: Email đã tồn tại.";
        }

        // Kiểm tra trùng username (loại trừ chính tài khoản đang sửa)
        $sqlCheckUsername = "SELECT 1 FROM tai_khoan WHERE username = ? AND ma_tai_khoan <> ?";
        $stmtCheckUsername = $this->db->prepare($sqlCheckUsername);
        $stmtCheckUsername->execute([$data['email'], $data['ma_tai_khoan']]);
        if ($stmtCheckUsername->fetchColumn()) {
            return "Lỗi: Tên đăng nhập (Email) đã tồn tại.";
        }

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

            // 5. Cập nhật phân công dạy học (nếu có truyền lên)
            if (!empty($data['ma_lop']) && !empty($data['ma_mon_hoc'])) {
                // XÓA TOÀN BỘ phân công cũ của giáo viên này (tất cả lớp, tất cả môn)
                $sqlDel = "DELETE FROM bang_phan_cong WHERE ma_giao_vien = ?";
                $this->db->prepare($sqlDel)->execute([$data['ma_nguoi_dung']]);

                // Thêm phân công mới (chỉ 1 lớp, 1 môn)
                $sqlIns = "INSERT INTO bang_phan_cong (ma_giao_vien, ma_lop, ma_mon_hoc, so_tiet_tuan, trang_thai) 
                        VALUES (:ma_giao_vien, :ma_lop, :ma_mon_hoc, :so_tiet_tuan, 'HoatDong')";
                $stmtIns = $this->db->prepare($sqlIns);
                $stmtIns->execute([
                    ':ma_giao_vien' => $data['ma_nguoi_dung'],
                    ':ma_lop' => $data['ma_lop'],
                    ':ma_mon_hoc' => $data['ma_mon_hoc'],
                    ':so_tiet_tuan' => isset($data['so_tiet_tuan']) ? (int)$data['so_tiet_tuan'] : 3
                ]);
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi updateGiaoVien: " . $e->getMessage());
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

    /**
     * Lấy danh sách giáo viên có phân trang - Cho QuanTriController
     */
    public function getDanhSachGiaoVienPaginated($school_id, $page = 1, $limit = 10) {
    if ($this->db === null) return [];
    
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT DISTINCT
                nd.ma_nguoi_dung,
                gv.ma_giao_vien,
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
            WHERE tk.trang_thai = 'HoatDong' 
            AND (
                gv.ma_truong = ?
                OR gv.ma_giao_vien IN (
                    SELECT DISTINCT bpc.ma_giao_vien
                    FROM bang_phan_cong bpc
                    JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                    WHERE lh.ma_truong = ?
                )
            )
            ORDER BY nd.ma_nguoi_dung DESC
            LIMIT ? OFFSET ?";
    
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $school_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $school_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $limit, PDO::PARAM_INT);
        $stmt->bindParam(4, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Lỗi getDanhSachGiaoVienPaginated: " . $e->getMessage());
        return [];
    }
}


    /**
     * Đếm tổng số giáo viên của trường
     */
    public function countGiaoVien($school_id) {
    if ($this->db === null) return 0;
    
    $sql = "SELECT COUNT(DISTINCT gv.ma_giao_vien)
            FROM giao_vien gv
            JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
            JOIN tai_khoan tk ON nd.ma_tai_khoan = tk.ma_tai_khoan
            WHERE tk.trang_thai = 'HoatDong' 
            AND (
                gv.ma_truong = ?
                OR gv.ma_giao_vien IN (
                    SELECT DISTINCT bpc.ma_giao_vien
                    FROM bang_phan_cong bpc
                    JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                    WHERE lh.ma_truong = ?
                )
            )";
    
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$school_id, $school_id]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Lỗi countGiaoVien: " . $e->getMessage());
        return 0;
    }
}

    /**
     * Lấy danh sách môn học mà giáo viên đang dạy
     */
    public function getMonDayByGiaoVien($ma_nguoi_dung) {
        if ($this->db === null) return [];
        
        $sql = "SELECT DISTINCT mh.ma_mon_hoc, mh.ten_mon_hoc
                FROM bang_phan_cong bpc
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                WHERE bpc.ma_giao_vien = ?
                AND mh.ten_mon_hoc NOT IN ('Chào cờ', 'Sinh hoạt lớp')
                ORDER BY mh.ten_mon_hoc";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_nguoi_dung]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi getMonDayByGiaoVien: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách lớp học mà giáo viên đang dạy
     */
    //NOTE: HÀM CŨ Á :))) đừng xóa
    // public function getLopDayByGiaoVien($ma_giao_vien) {
    //     if ($this->db === null) return [];
        
    //     $sql = "SELECT DISTINCT lh.ma_lop, lh.ten_lop
    //             FROM bang_phan_cong bpc
    //             JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
    //             WHERE bpc.ma_giao_vien = ?
    //             ORDER BY lh.ten_lop";
        
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_giao_vien]);
    //         return $stmt->fetchAll();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getLopDayByGiaoVien: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getLopDayByGiaoVien($ma_giao_vien) {
        if ($this->db === null) return [];
        
        $sql = "SELECT DISTINCT 
                    lh.ma_lop, 
                    lh.ten_lop,
                    lh.si_so,
                    lh.khoi
                FROM bang_phan_cong bpc
                JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                WHERE bpc.ma_giao_vien = ?
                AND lh.trang_thai_lop = 'HoatDong'
                ORDER BY lh.khoi DESC, lh.ten_lop";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_giao_vien]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getLopDayByGiaoVien: " . $e->getMessage());
            return [];
        }
    }


    public function addPhanCongGiaoVien($ma_giao_vien, $ma_lop, $ma_mon_hoc, $so_tiet_tuan = 4) {
        if ($this->db === null) return false;
        
        try {
            $sql = "INSERT INTO bang_phan_cong (ma_giao_vien, ma_lop, ma_mon_hoc, so_tiet_tuan, trang_thai) 
                    VALUES (:ma_giao_vien, :ma_lop, :ma_mon_hoc, :so_tiet_tuan, 'HoatDong')";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':ma_giao_vien' => $ma_giao_vien,
                ':ma_lop' => $ma_lop,
                ':ma_mon_hoc' => $ma_mon_hoc,
                ':so_tiet_tuan' => $so_tiet_tuan
            ]);
        } catch (PDOException $e) {
            error_log("Lỗi addPhanCongGiaoVien: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách lớp của trường
     */
    public function getDanhSachLopByTruong($school_id) {
        if ($this->db === null) return [];
        
        $sql = "SELECT ma_lop, ten_lop FROM lop_hoc 
                WHERE ma_truong = ? AND trang_thai_lop = 'HoatDong'
                ORDER BY ten_lop";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$school_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachLopByTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách môn học
     */
    public function getDanhSachMonHoc() {
        if ($this->db === null) return [];
        
        $sql = "SELECT ma_mon_hoc, ten_mon_hoc FROM mon_hoc 
                WHERE ten_mon_hoc NOT IN ('Chào cờ', 'Sinh hoạt lớp')
                ORDER BY ten_mon_hoc";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachMonHoc: " . $e->getMessage());
            return [];
        }
    }

    //note: phần tkb giáo viên ở đây
    /**
     * ✅ HÀM 1 (MỚI): Lấy TKB chi tiết của GV cho 1 lớp (theo học kỳ)
     * ⚠️ TÊN KHÁC: getTkbGVByLop() - KHÔNG TRÙNG
     */
    // public function getTkbGVByLop($ma_giao_vien, $ma_lop, $ma_hoc_ky = 1) {
    //     if ($this->db === null) return [];
    //     $ma_hoc_ky = $this->normalizeHocKy($ma_hoc_ky);

    //     $sql = "SELECT 
    //                 t.ma_tkb_chi_tiet,
    //                 t.thu,
    //                 t.tiet,
    //                 th.gio_bat_dau,
    //                 th.gio_ket_thuc,
    //                 mh.ten_mon_hoc AS mon,
    //                 lh.ten_lop AS lop,
    //                 p.ten_phong AS phong,
    //                 lh.ma_lop,
    //                 mh.ma_mon_hoc
    //             FROM tkb_chi_tiet t
    //             JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
    //             JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
    //             JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
    //             LEFT JOIN tiet_hoc th ON t.tiet = th.ma_tiet_hoc
    //             LEFT JOIN phong_hoc p ON t.ma_phong_hoc = p.ma_phong
    //             WHERE bpc.ma_giao_vien = ?
    //               AND lh.ma_lop = ?
    //               AND t.ma_hoc_ky = ?
    //             ORDER BY t.thu+0, t.tiet";
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_giao_vien, $ma_lop, $ma_hoc_ky]);
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getTkbGVByLop: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getTkbGVByLop($ma_giao_vien, $ma_lop, $ma_hoc_ky = 1) {
        if ($this->db === null) return [];
        $ma_hoc_ky = $this->normalizeHocKy($ma_hoc_ky);

        $sql = "SELECT 
                    t.ma_tkb_chi_tiet,
                    t.thu,
                    t.tiet,
                    th.gio_bat_dau,
                    th.gio_ket_thuc,
                    mh.ten_mon_hoc AS mon,
                    lh.ten_lop AS lop,
                    p.ten_phong AS phong,
                    lh.ma_lop,
                    mh.ma_mon_hoc,
                    t.loai_tiet,
                    t.ghi_chu
                FROM tkb_chi_tiet t
                LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                LEFT JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                LEFT JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                LEFT JOIN tiet_hoc th ON t.tiet = th.ma_tiet_hoc
                LEFT JOIN phong_hoc p ON t.ma_phong_hoc = p.ma_phong
                WHERE t.ma_lop = ?
                  AND t.ma_hoc_ky = ?
                  AND (bpc.ma_giao_vien = ? OR bpc.ma_phan_cong IS NULL)
                ORDER BY t.thu+0, t.tiet";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop, $ma_hoc_ky, $ma_giao_vien]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi getTkbGVByLop: " . $e->getMessage());
            return [];
        }
    }


    // --- [LOGIC MỚI: OVERLAY TKB CHO GIÁO VIÊN] ---

    /**
     * A. Lấy Lịch Cứng của GV (Tất cả lớp)
     */
    private function getTkbCungGV($ma_giao_vien, $ma_hoc_ky) {
        $sql = "SELECT 
                    t.thu, t.tiet, 
                    th.gio_bat_dau, th.gio_ket_thuc,
                    mh.ten_mon_hoc AS mon,
                    lh.ten_lop AS lop,
                    p.ten_phong AS phong,
                    t.loai_tiet, t.ghi_chu
                FROM tkb_chi_tiet t
                JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                LEFT JOIN tiet_hoc th ON t.tiet = th.ma_tiet_hoc
                LEFT JOIN phong_hoc p ON t.ma_phong_hoc = p.ma_phong
                WHERE bpc.ma_giao_vien = ? AND t.ma_hoc_ky = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_giao_vien, $ma_hoc_ky]);
        
        $tkb = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tkb[$row['thu']][$row['tiet']] = $row;
            $tkb[$row['thu']][$row['tiet']]['is_changed'] = false;
        }
        return $tkb;
    }

    /**
     * B. Lấy Lịch Thay Đổi của GV trong tuần (Tất cả lớp)
     */
    private function getThayDoiGVTrongTuan($ma_giao_vien, $start_date, $end_date) {
        $sql = "SELECT 
                    t.ngay_thay_doi, t.tiet, t.loai_tiet, t.ghi_chu,
                    mh.ten_mon_hoc AS mon,
                    lh.ten_lop AS lop,
                    -- Có thể join thêm phòng nếu cần
                    'Xem chi tiết' as phong
                FROM tkb_thay_doi t
                JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                WHERE bpc.ma_giao_vien = ? 
                AND t.ngay_thay_doi BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_giao_vien, $start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * C. HÀM CHÍNH: Lấy TKB Chính thức cho GV (Theo tuần cụ thể)
     * Thay thế cho getTkbGVAll cũ
     */
    public function getTkbGVChinhThuc($ma_giao_vien, $ma_hoc_ky, $start_date, $end_date) {
        // 1. Lấy lịch cứng
        $tkb = $this->getTkbCungGV($ma_giao_vien, $ma_hoc_ky);

        // 2. Lấy thay đổi
        $changes = $this->getThayDoiGVTrongTuan($ma_giao_vien, $start_date, $end_date);

        // 3. Trộn (Merge)
        foreach ($changes as $c) {
            $timestamp = strtotime($c['ngay_thay_doi']);
            $thu = date('N', $timestamp) + 1; // 2-8
            $tiet = $c['tiet'];

            // Chuẩn bị data hiển thị
            $mon_hien_thi = $c['mon'];
            if ($c['loai_tiet'] == 'tam_nghi') {
                $mon_hien_thi = "(Nghỉ) " . $c['mon'];
            }

            // Ghi đè vào mảng TKB
            // Lưu ý: GV có thể có lịch trống ở lịch cứng nhưng lại có lịch dạy bù -> Tự tạo ô mới
            if (!isset($tkb[$thu][$tiet])) {
                $tkb[$thu][$tiet] = []; // Init nếu chưa có
            }

            $tkb[$thu][$tiet]['mon'] = $mon_hien_thi;
            $tkb[$thu][$tiet]['lop'] = $c['lop'];
            $tkb[$thu][$tiet]['phong'] = $c['phong']; // Hoặc logic phòng riêng
            $tkb[$thu][$tiet]['loai_tiet'] = $c['loai_tiet'];
            $tkb[$thu][$tiet]['ghi_chu'] = $c['ghi_chu'];
            $tkb[$thu][$tiet]['is_changed'] = true;
        }

        // Chuyển đổi định dạng mảng về list phẳng (như API cũ trả về) để View dễ render
        $resultList = [];
        foreach ($tkb as $thu => $cac_tiet) {
            foreach ($cac_tiet as $tiet => $data) {
                // Chỉ cần thêm trường thu, tiet vào data để khớp format cũ
                $data['thu'] = $thu;
                $data['tiet'] = $tiet;
                $resultList[] = $data;
            }
        }
        
        // Sắp xếp lại cho đẹp
        usort($resultList, function($a, $b) {
            if ($a['thu'] == $b['thu']) return $a['tiet'] - $b['tiet'];
            return $a['thu'] - $b['thu'];
        });

        return $resultList;
    }

    /**
     * ✅ HÀM 2 (MỚI): Lấy TKB tất cả lớp của GV (để xem tất cả lịch dạy)
     * ⚠️ TÊN KHÁC: getTkbGVAll() - KHÔNG TRÙNG
     */
    // public function getTkbGVAll($ma_giao_vien, $ma_hoc_ky = 'HK1') {
    //     if ($this->db === null) return [];
    //     $ma_hoc_ky = $this->normalizeHocKy($ma_hoc_ky);

    //     // ✅ FIX: Lấy cả tiết môn học + tiết GVCN (chào cờ, sinh hoạt)
    //     $sql = "SELECT 
    //                 t.ma_tkb_chi_tiet,
    //                 t.thu,
    //                 t.tiet,
    //                 th.gio_bat_dau,
    //                 th.gio_ket_thuc,
    //                 mh.ten_mon_hoc AS mon,
    //                 lh.ten_lop AS lop,
    //                 p.ten_phong AS phong,
    //                 lh.ma_lop,
    //                 mh.ma_mon_hoc
    //             FROM tkb_chi_tiet t
    //             JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
    //             JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
    //             JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
    //             LEFT JOIN tiet_hoc th ON t.tiet = th.ma_tiet_hoc
    //             LEFT JOIN phong_hoc p ON t.ma_phong_hoc = p.ma_phong
    //             WHERE bpc.ma_giao_vien = ?
    //               AND t.ma_hoc_ky = ?
    //             ORDER BY t.thu+0, t.tiet, lh.ten_lop";
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_giao_vien, $ma_hoc_ky]);
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getTkbGVAll: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getTkbGVAll($ma_giao_vien, $ma_hoc_ky = 'HK1') {
        if ($this->db === null) return [];
        $ma_hoc_ky = $this->normalizeHocKy($ma_hoc_ky);

        $sql = "SELECT 
                    t.ma_tkb_chi_tiet,
                    t.thu,
                    t.tiet,
                    th.gio_bat_dau,
                    th.gio_ket_thuc,
                    mh.ten_mon_hoc AS mon,
                    lh.ten_lop AS lop,
                    p.ten_phong AS phong,
                    lh.ma_lop,
                    mh.ma_mon_hoc,
                    t.loai_tiet,
                    t.ghi_chu
                FROM tkb_chi_tiet t
                LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                LEFT JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                LEFT JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                LEFT JOIN tiet_hoc th ON t.tiet = th.ma_tiet_hoc
                LEFT JOIN phong_hoc p ON t.ma_phong_hoc = p.ma_phong
                WHERE t.ma_hoc_ky = ?
                  AND (bpc.ma_giao_vien = ? OR bpc.ma_phan_cong IS NULL)
                ORDER BY t.thu+0, t.tiet, lh.ten_lop";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_hoc_ky, $ma_giao_vien]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi getTkbGVAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ HÀM 3 (MỚI): Lấy thống kê TKB của GV
     * ⚠️ TÊN KHÁC: getThongKeTkbGV() - KHÔNG TRÙNG
     */
    public function getThongKeTkbGV($ma_giao_vien, $ma_hoc_ky = 'HK1') {
        if ($this->db === null) return null;
        
        $sql = "SELECT 
                    COUNT(DISTINCT t.ma_tkb_chi_tiet) AS tong_tiet_da_xep,
                    COUNT(DISTINCT bpc.ma_lop) AS so_lop,
                    COUNT(DISTINCT bpc.ma_mon_hoc) AS so_mon,
                    MIN(th.gio_bat_dau) AS gio_vao_som_nhat,
                    MAX(th.gio_ket_thuc) AS gio_tan_muon_nhat
                FROM bang_phan_cong bpc
                LEFT JOIN tkb_chi_tiet t ON bpc.ma_phan_cong = t.ma_phan_cong 
                    AND t.ma_hoc_ky = ?
                LEFT JOIN tiet_hoc th ON t.tiet = th.so_tiet
                WHERE bpc.ma_giao_vien = ?
                AND bpc.trang_thai = 'HoatDong'";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_hoc_ky, $ma_giao_vien]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getThongKeTkbGV: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ HÀM 4 (MỚI): Lấy danh sách tiết học (để hiển thị header bảng)
     * ⚠️ TÊN KHÁC: getDanhSachTietHoc() - KHÔNG TRÙNG
     */
    public function getDanhSachTietHoc() {
        if ($this->db === null) return [];
        $sql = "SELECT 
                    ma_tiet_hoc AS so_tiet,
                    gio_bat_dau,
                    gio_ket_thuc
                FROM tiet_hoc
                ORDER BY ma_tiet_hoc ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachTietHoc: " . $e->getMessage());
            return [];
        }
    }  

    private function normalizeHocKy($ma_hoc_ky) {
        if ($ma_hoc_ky === 'HK1') return 1;
        if ($ma_hoc_ky === 'HK2') return 2;
        return $ma_hoc_ky ?: 1;
    }




}
?>
