<?php
class UserModel {
    private $db; // PDO connection

    public function __construct() {
        $ports = [3307, 3306]; 
        $connected = false;

        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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

    public function checkLogin($username, $password) {
        if ($this->db === null) return false;
        $hashed_password = md5($password);

        // SQL SUPER VIP: Tự động dò tìm tên trường cho mọi loại tài khoản
        $sql = "SELECT 
                    tk.ma_tai_khoan, tk.vai_tro, nd.ho_ten, nd.ma_nguoi_dung,
                    
                    -- Lấy chức vụ
                    COALESCE(gv.chuc_vu, qtv.chuc_vu) AS chuc_vu,
                    hs.ma_lop,
                    
                    -- 1. Tên trường cho Admin (qua quan_tri_vien)
                    t_admin.ten_truong AS ten_truong_admin,
                    
                    -- 2. Tên trường cho Học Sinh (qua lop_hoc)
                    t_hs.ten_truong AS ten_truong_hs,
                    
                    -- 3. Tên trường cho Giáo Viên (qua bang_phan_cong -> lop_hoc -> truong)
                    (SELECT t.ten_truong 
                     FROM bang_phan_cong bpc 
                     JOIN lop_hoc l ON bpc.ma_lop = l.ma_lop 
                     JOIN truong_thpt t ON l.ma_truong = t.ma_truong
                     WHERE bpc.ma_giao_vien = nd.ma_nguoi_dung 
                     LIMIT 1) AS ten_truong_gv

                FROM tai_khoan AS tk
                JOIN nguoi_dung AS nd ON tk.ma_tai_khoan = nd.ma_tai_khoan
                
                -- Join các bảng phụ
                LEFT JOIN quan_tri_vien AS qtv ON nd.ma_nguoi_dung = qtv.ma_qtv
                LEFT JOIN truong_thpt AS t_admin ON qtv.ma_truong = t_admin.ma_truong
                
                LEFT JOIN hoc_sinh AS hs ON nd.ma_nguoi_dung = hs.ma_hoc_sinh
                LEFT JOIN lop_hoc AS lh ON hs.ma_lop = lh.ma_lop
                LEFT JOIN truong_thpt AS t_hs ON lh.ma_truong = t_hs.ma_truong
                
                LEFT JOIN giao_vien AS gv ON nd.ma_nguoi_dung = gv.ma_giao_vien

                WHERE (tk.username = :username OR nd.email = :username OR nd.so_dien_thoai = :username)
                  AND tk.password = :password AND tk.trang_thai = 'HoatDong'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username, ':password' => $hashed_password]);
        $user = $stmt->fetch();

        if ($user) {
            $schoolName = 'THPT MANAGER'; // Mặc định

            // LOGIC ƯU TIÊN
            if (!empty($user['ten_truong_admin'])) {
                $schoolName = $user['ten_truong_admin']; // Admin trường
            } elseif (!empty($user['ten_truong_hs'])) {
                $schoolName = $user['ten_truong_hs'];    // Học sinh
            } elseif (!empty($user['ten_truong_gv'])) {
                $schoolName = $user['ten_truong_gv'];    // Giáo viên (Lấy từ lớp dạy)
            } elseif ($user['vai_tro'] == 'QuanTriVien') {
                $schoolName = 'QUẢN TRỊ VIÊN HỆ THỐNG';  // Super Admin
            } 
            // Fallback: Nếu là Hiệu trưởng chưa có lịch dạy, check chức vụ
            elseif (strpos($user['chuc_vu'], 'THPT') !== false) {
                 $schoolName = substr($user['chuc_vu'], strpos($user['chuc_vu'], 'THPT'));
            }

            // Lưu Session
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['school_name'] = $schoolName;

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

    // --- LOGIC PHÂN QUYỀN TRƯỜNG HỌC ---

    /**
     * Lấy ID trường của Admin đang đăng nhập
     * @return int|null (NULL = Super Admin, Int = Admin Trường)
     */
    public function getAdminSchoolId($user_id) {
        if ($this->db === null) return null;
        $sql = "SELECT ma_truong FROM quan_tri_vien WHERE ma_qtv = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $user_id]);
        $result = $stmt->fetch();
        return $result['ma_truong'] ?? null;
    }

    // 1. Đếm Tổng User (Có lọc theo trường)
    public function getTotalUsers($school_id = null) {
        if ($this->db === null) return 0;
        
        if ($school_id) {
            // Nếu là Admin Trường: Đếm HS trường đó + GV có dạy trường đó + Admin trường đó
            // (Query đơn giản hóa: Đếm HS + GV chủ nhiệm tại trường)
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM hoc_sinh hs JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop WHERE lh.ma_truong = :sid) 
                        + 
                        (SELECT COUNT(*) FROM giao_vien gv JOIN bang_phan_cong bpc ON gv.ma_giao_vien = bpc.ma_giao_vien JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop WHERE lh.ma_truong = :sid)
                    as total";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $school_id]);
        } else {
            // Super Admin: Đếm tất cả tài khoản
            $sql = "SELECT COUNT(*) as total FROM tai_khoan WHERE trang_thai = 'HoatDong'";
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetch()['total'] ?? 0;
    }

    // 2. Đếm Tổng Lớp (Có lọc)
    public function getTotalLop($school_id = null) {
        if ($this->db === null) return 0;
        
        if ($school_id) {
            $sql = "SELECT COUNT(*) as total FROM lop_hoc WHERE trang_thai_lop = 'HoatDong' AND ma_truong = :sid";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $school_id]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM lop_hoc WHERE trang_thai_lop = 'HoatDong'";
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetch()['total'] ?? 0;
    }

    // 3. Đếm Tổng Học Sinh (Có lọc)
    public function getTotalHs($school_id = null) {
        if ($this->db === null) return 0;
        
        if ($school_id) {
            $sql = "SELECT COUNT(*) as total 
                    FROM hoc_sinh hs 
                    JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop 
                    WHERE hs.trang_thai = 'DangHoc' AND lh.ma_truong = :sid";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $school_id]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM hoc_sinh WHERE trang_thai = 'DangHoc'";
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetch()['total'] ?? 0;
    }

    // 4. Biểu đồ tròn: Tài khoản theo vai trò (Có lọc theo trường)
    public function getTkByRole($school_id = null) {
        if ($this->db === null) return [];
        
        if ($school_id) {
            // Filter theo trường: Đếm HS trường + GV dạy trường + Admin trường + Phụ huynh
            $sql = "
                SELECT 'HocSinh' as vai_tro, COUNT(*) as count
                FROM hoc_sinh hs 
                JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
                WHERE lh.ma_truong = :sid AND hs.trang_thai = 'DangHoc'
                
                UNION ALL
                
                SELECT 'GiaoVien' as vai_tro, COUNT(DISTINCT gv.ma_giao_vien) as count
                FROM giao_vien gv
                JOIN bang_phan_cong bpc ON gv.ma_giao_vien = bpc.ma_giao_vien
                JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                WHERE lh.ma_truong = :sid
                
                UNION ALL
                
                SELECT 'PhuHuynh' as vai_tro, COUNT(DISTINCT phhs.ma_phu_huynh) as count
                FROM phu_huynh_hoc_sinh phhs
                JOIN hoc_sinh hs ON phhs.ma_hoc_sinh = hs.ma_hoc_sinh
                JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
                WHERE lh.ma_truong = :sid
                
                UNION ALL
                
                SELECT 'QuanTriVien' as vai_tro, COUNT(*) as count
                FROM quan_tri_vien
                WHERE ma_truong = :sid
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $school_id]);
        } else {
            // Super Admin: Query tất cả
            $sql = "SELECT vai_tro, COUNT(*) as count FROM tai_khoan WHERE trang_thai = 'HoatDong' GROUP BY vai_tro";
            $stmt = $this->db->query($sql);
        }
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['vai_tro']] = $row['count'];
        }
        return $result;
    }

    // 5. Biểu đồ cột: Sĩ số khối (Có lọc)
    public function getSiSoKhoi($school_id = null) {
        if ($this->db === null) return [];
        
        if ($school_id) {
            $sql = "SELECT khoi, SUM(si_so) as si_so FROM lop_hoc WHERE trang_thai_lop = 'HoatDong' AND ma_truong = :sid GROUP BY khoi";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $school_id]);
        } else {
            $sql = "SELECT khoi, SUM(si_so) as si_so FROM lop_hoc WHERE trang_thai_lop = 'HoatDong' GROUP BY khoi";
            $stmt = $this->db->query($sql);
        }

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result['Khối ' . $row['khoi']] = $row['si_so'];
        }
        return $result;
    }

    // 6. Danh sách User mới nhất (Có lọc)
    public function getAllUsers($limit = 10, $school_id = null) {
        if ($this->db === null) return [];
        $limit_int = (int)$limit;

        if ($school_id) {
            // Lọc User thuộc trường: HS của trường, GV có phân công dạy tại trường,
            // Phụ huynh có con ở trường, và QTV thuộc trường.
            $sql = "SELECT DISTINCT tk.username, tk.vai_tro, tk.trang_thai, tk.ngay_tao_tai_khoan, nd.ho_ten, nd.so_dien_thoai
                FROM tai_khoan tk
                JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan
                LEFT JOIN hoc_sinh hs ON nd.ma_nguoi_dung = hs.ma_hoc_sinh
                LEFT JOIN lop_hoc lh_hs ON hs.ma_lop = lh_hs.ma_lop
                LEFT JOIN phu_huynh_hoc_sinh phhs ON phhs.ma_hoc_sinh = hs.ma_hoc_sinh
                LEFT JOIN bang_phan_cong bpc ON bpc.ma_giao_vien = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc lh_bpc ON bpc.ma_lop = lh_bpc.ma_lop
                LEFT JOIN quan_tri_vien qtv ON nd.ma_nguoi_dung = qtv.ma_qtv
                WHERE (lh_hs.ma_truong = :sid OR lh_bpc.ma_truong = :sid OR qtv.ma_truong = :sid OR phhs.ma_phu_huynh IS NOT NULL AND lh_hs.ma_truong = :sid)
                ORDER BY tk.ngay_tao_tai_khoan DESC LIMIT :limit_val";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':sid', $school_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit_val', $limit_int, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT tk.username, tk.vai_tro, tk.trang_thai, tk.ngay_tao_tai_khoan, nd.ho_ten, nd.so_dien_thoai
                    FROM tai_khoan tk
                    JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan
                    ORDER BY tk.ngay_tao_tai_khoan DESC LIMIT :limit_val";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit_val', $limit_int, PDO::PARAM_INT);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    // Cập nhật vai trò người dùng (Dùng khi chuyển từ ThiSinh -> HocSinh)
    public function updateRole($user_id, $new_role) {
        $sql = "UPDATE tai_khoan tk 
                JOIN nguoi_dung nd ON tk.ma_tai_khoan = nd.ma_tai_khoan 
                SET tk.vai_tro = ? 
                WHERE nd.ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$new_role, $user_id]);
    }


    public function getTotalTruong() {
        if ($this->db === null) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM truong_thpt";
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            
            return (int)($result['total'] ?? 0);
            
        } catch (Exception $e) {
            error_log("getTotalTruong Error: " . $e->getMessage());
            return 0;
        }
    }



}
?>