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
                -- Liên kết Phụ huynh
                LEFT JOIN phu_huynh ph ON nd.ma_nguoi_dung = ph.ma_phu_huynh
                WHERE (lh_hs.ma_truong = :sid OR lh_bpc.ma_truong = :sid OR qtv.ma_truong = :sid OR ph.ma_truong = :sid)
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
                LEFT JOIN phu_huynh ph ON nd.ma_nguoi_dung = ph.ma_phu_huynh
                WHERE (lh_hs.ma_truong = :sid OR lh_bpc.ma_truong = :sid OR qtv.ma_truong = :sid OR ph.ma_truong = :sid)";
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
                LEFT JOIN phu_huynh ph ON nd.ma_nguoi_dung = ph.ma_phu_huynh
                WHERE (lh_hs.ma_truong = :sid OR lh_bpc.ma_truong = :sid OR qtv.ma_truong = :sid OR ph.ma_truong = :sid)
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
     * Cập nhật thông tin tài khoản + người dùng
     * - Cập nhật bảng tai_khoan: username, vai_tro, password (nếu có)
     * - Cập nhật bảng nguoi_dung: họ tên, email, SĐT, địa chỉ, ngày sinh, giới tính
     * 
     * @param array $data Chứa: ma_tai_khoan, ho_ten, email, so_dien_thoai, dia_chi, ngay_sinh, gioi_tinh, vai_tro, password (optional)
     * @return bool true nếu thành công, false nếu thất bại
     */
    public function updateAccount($data) {
        if ($this->db === null) return "Lỗi kết nối CSDL.";

        // ✅ Kiểm tra trùng lặp TRƯỚC KHI UPDATE
        $check = $this->checkDuplicatesForUpdate(
            $data['ma_tai_khoan'], 
            $data['email'], 
            $data['so_dien_thoai'] ?? ''
        );
        if ($check !== true) {
            return $check; // Trả về thông báo lỗi
        }

        $this->db->beginTransaction();
        try {
            // 1. UPDATE BẢNG tai_khoan
            $updateFields = [];
            $params = [];
            
            // ✅ THÊM: Cho phép đổi email (username)
            if (isset($data['email'])) {
                $updateFields[] = "username = ?";
                $params[] = $data['email'];
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $updateFields[] = "password = ?";
                $params[] = md5($data['password']);
            }
            
            if (isset($data['vai_tro'])) {
                $updateFields[] = "vai_tro = ?";
                $params[] = $data['vai_tro'];
            }

            if (!empty($updateFields)) {
                $sql = "UPDATE tai_khoan SET " . implode(", ", $updateFields) . " WHERE ma_tai_khoan = ?";
                $params[] = $data['ma_tai_khoan'];
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            // 2. UPDATE BẢNG nguoi_dung
            $updateNguoiDungFields = [];
            $updateNguoiDungParams = [];

            if (isset($data['ho_ten'])) {
                $updateNguoiDungFields[] = "ho_ten = ?";
                $updateNguoiDungParams[] = $data['ho_ten'];
            }
            if (isset($data['email'])) {
                $updateNguoiDungFields[] = "email = ?";
                $updateNguoiDungParams[] = $data['email'];
            }
            if (isset($data['so_dien_thoai'])) {
                $updateNguoiDungFields[] = "so_dien_thoai = ?";
                $updateNguoiDungParams[] = $data['so_dien_thoai'];
            }
            if (isset($data['dia_chi'])) {
                $updateNguoiDungFields[] = "dia_chi = ?";
                $updateNguoiDungParams[] = $data['dia_chi'];
            }
            if (isset($data['ngay_sinh'])) {
                $updateNguoiDungFields[] = "ngay_sinh = ?";
                $updateNguoiDungParams[] = $data['ngay_sinh'];
            }
            if (isset($data['gioi_tinh'])) {
                $updateNguoiDungFields[] = "gioi_tinh = ?";
                $updateNguoiDungParams[] = $data['gioi_tinh'];
            }

            if (!empty($updateNguoiDungFields)) {
                $sql = "UPDATE nguoi_dung SET " . implode(", ", $updateNguoiDungFields) . " WHERE ma_tai_khoan = ?";
                $updateNguoiDungParams[] = $data['ma_tai_khoan'];
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($updateNguoiDungParams);
            }

            // ✅ 3. XỬ LÝ VAI TRÒ PHỤ (giữ nguyên phần này)
            $vaiTro = $data['vai_tro'] ?? '';
            $maLop = $data['ma_lop'] ?? null;

            if ($vaiTro === 'HocSinh' && $maLop) {
                $stmt = $this->db->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE ma_tai_khoan = ?");
                $stmt->execute([$data['ma_tai_khoan']]);
                $nguoiDung = $stmt->fetch();
                
                if ($nguoiDung) {
                    $maHocSinh = $nguoiDung['ma_nguoi_dung'];
                    
                    $stmt = $this->db->prepare("SELECT ma_hoc_sinh FROM hoc_sinh WHERE ma_hoc_sinh = ?");
                    $stmt->execute([$maHocSinh]);
                    $hsExists = $stmt->fetch();

                    if ($hsExists) {
                        $stmt = $this->db->prepare("UPDATE hoc_sinh SET ma_lop = ? WHERE ma_hoc_sinh = ?");
                        $stmt->execute([$maLop, $maHocSinh]);
                    } else {
                        $stmt = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ma_lop) VALUES (?, ?)");
                        $stmt->execute([$maHocSinh, $maLop]);
                    }
                }
            }
            else if (($vaiTro === 'GiaoVien' || $vaiTro === 'BanGiamHieu') && $maLop) {
                $stmt = $this->db->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE ma_tai_khoan = ?");
                $stmt->execute([$data['ma_tai_khoan']]);
                $nguoiDung = $stmt->fetch();
                
                if ($nguoiDung) {
                    $maGiaoVien = $nguoiDung['ma_nguoi_dung'];
                    $monChuyen = $data['mon_chuyen_mon'] ?? null;
                    $school_id = $_SESSION['admin_school_id'] ?? null;

                    if ($vaiTro === 'BanGiamHieu') {
                        $stmt = $this->db->prepare("SELECT 1 FROM ban_giam_hieu WHERE ma_bgh = ?");
                        $stmt->execute([$maGiaoVien]);
                        if (!$stmt->fetch()) {
                            if ($school_id) {
                                $ins = $this->db->prepare("INSERT INTO ban_giam_hieu (ma_bgh, ma_truong) VALUES (?, ?)");
                                $ins->execute([$maGiaoVien, $school_id]);
                            } else {
                                $ins = $this->db->prepare("INSERT INTO ban_giam_hieu (ma_bgh) VALUES (?)");
                                $ins->execute([$maGiaoVien]);
                            }
                        }
                    }

                    $stmt = $this->db->prepare("SELECT 1 FROM giao_vien WHERE ma_giao_vien = ?");
                    $stmt->execute([$maGiaoVien]);
                    if (!$stmt->fetch()) {
                        $stmt = $this->db->prepare("INSERT INTO giao_vien (ma_giao_vien) VALUES (?)");
                        $stmt->execute([$maGiaoVien]);
                    }

                    $stmt = $this->db->prepare("SELECT ma_phan_cong FROM bang_phan_cong WHERE ma_giao_vien = ? LIMIT 1");
                    $stmt->execute([$maGiaoVien]);
                    $bpcExists = $stmt->fetch();

                    if ($bpcExists) {
                        $sql = "UPDATE bang_phan_cong SET ma_lop = ?, ma_mon_hoc = ? WHERE ma_giao_vien = ?";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([$maLop, $monChuyen, $maGiaoVien]);
                    } else {
                        $stmt = $this->db->prepare("INSERT INTO bang_phan_cong (ten_bang_phan_cong, ma_giao_vien, ma_lop, ma_mon_hoc, so_tiet_tuan) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute(['Phân công ban đầu', $maGiaoVien, $maLop, $monChuyen, 3]);
                    }
                }
            }
            else if ($vaiTro === 'PhuHuynh' && $maLop) {
                $stmt = $this->db->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE ma_tai_khoan = ?");
                $stmt->execute([$data['ma_tai_khoan']]);
                $nguoiDung = $stmt->fetch();
                
                if ($nguoiDung) {
                    $maPhuHuynh = $nguoiDung['ma_nguoi_dung'];
                    $maHocSinh = $data['ma_hoc_sinh'] ?? null;

                    $stmt = $this->db->prepare("SELECT 1 FROM phu_huynh WHERE ma_phu_huynh = ?");
                    $stmt->execute([$maPhuHuynh]);
                    if (!$stmt->fetch()) {
                        $stmt = $this->db->prepare("INSERT INTO phu_huynh (ma_phu_huynh) VALUES (?)");
                        $stmt->execute([$maPhuHuynh]);
                    }

                    if ($maHocSinh) {
                        $stmt = $this->db->prepare("SELECT 1 FROM phu_huynh_hoc_sinh WHERE ma_phu_huynh = ? AND ma_hoc_sinh = ?");
                        $stmt->execute([$maPhuHuynh, $maHocSinh]);
                        
                        if (!$stmt->fetch()) {
                            $stmt = $this->db->prepare("INSERT INTO phu_huynh_hoc_sinh (ma_phu_huynh, ma_hoc_sinh) VALUES (?, ?)");
                            $stmt->execute([$maPhuHuynh, $maHocSinh]);
                        }
                    }
                }
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi updateAccount: " . $e->getMessage());
            return "Lỗi cập nhật tài khoản: " . $e->getMessage();
        }
    }

    /**
     * ✅ HÀM MỚI: Kiểm tra trùng lặp khi UPDATE (loại trừ chính tài khoản đang sửa)
     */
    private function checkDuplicatesForUpdate($ma_tai_khoan, $email, $so_dien_thoai = '') {
        try {
            // 1. Kiểm tra email (username) - trừ chính mình
            $stmt = $this->db->prepare("SELECT 1 FROM tai_khoan WHERE username = ? AND ma_tai_khoan != ?");
            $stmt->execute([$email, $ma_tai_khoan]);
            if ($stmt->fetch()) {
                return "Email (Tên đăng nhập) đã được sử dụng bởi tài khoản khác.";
            }

            // 2. Kiểm tra email trong nguoi_dung - trừ chính mình
            $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE email = ? AND ma_tai_khoan != ?");
            $stmt->execute([$email, $ma_tai_khoan]);
            if ($stmt->fetch()) {
                return "Email đã được sử dụng bởi người dùng khác.";
            }

            // 3. Kiểm tra SĐT (nếu có) - trừ chính mình
            if (!empty($so_dien_thoai)) {
                $stmt = $this->db->prepare("SELECT 1 FROM nguoi_dung WHERE so_dien_thoai = ? AND ma_tai_khoan != ?");
                $stmt->execute([$so_dien_thoai, $ma_tai_khoan]);
                if ($stmt->fetch()) {
                    return "Số điện thoại đã được sử dụng bởi người dùng khác.";
                }
            }

            return true; // Không trùng
        } catch (PDOException $e) {
            error_log("Lỗi checkDuplicatesForUpdate: " . $e->getMessage());
            return "Lỗi máy chủ khi kiểm tra dữ liệu.";
        }
    }

    /**
     * ✅ HÀM MỚI: Lấy thông tin HỌC SINH (ma_lop cũ)
     */
    public function getHocSinhInfo($ma_tai_khoan) {
        if ($this->db === null) return null;
        
        $sql = "SELECT hs.ma_hoc_sinh, hs.ma_lop, lh.khoi
                FROM hoc_sinh hs
                JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
                WHERE hs.ma_hoc_sinh = (
                    SELECT ma_nguoi_dung FROM nguoi_dung WHERE ma_tai_khoan = :ma_tk
                )";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_tk' => $ma_tai_khoan]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getHocSinhInfo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ SỬA: Lấy thông tin GIÁO VIÊN (ma_lop + ma_mon_hoc từ bang_phan_cong)
     */
    public function getGiaoVienInfo($ma_tai_khoan) {
        if ($this->db === null) return null;
        
        // ✅ SỬA: Lấy từ bang_phan_cong (nơi lưu thông tin phân công thực tế)
        $sql = "SELECT 
                    bpc.ma_lop, 
                    bpc.ma_mon_hoc, 
                    mh.ten_mon_hoc,
                    gv.ma_giao_vien
                FROM giao_vien gv
                LEFT JOIN bang_phan_cong bpc ON gv.ma_giao_vien = bpc.ma_giao_vien
                LEFT JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                WHERE gv.ma_giao_vien = (
                    SELECT ma_nguoi_dung FROM nguoi_dung WHERE ma_tai_khoan = :ma_tk
                )
                LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_tk' => $ma_tai_khoan]);
            $result = $stmt->fetch();
            error_log("getGiaoVienInfo($ma_tai_khoan): " . json_encode($result));
            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi getGiaoVienInfo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ HÀM MỚI: Lấy thông tin PHỤ HUYNH (ma_lop + ma_hoc_sinh cũ)
     */
    public function getPhuHuynhInfo($ma_tai_khoan) {
        if ($this->db === null) return null;
        
        $sql = "SELECT ph.ma_phu_huynh, phs.ma_hoc_sinh, hs.ma_lop, nd.ho_ten as ten_hs
                FROM phu_huynh ph
                LEFT JOIN phu_huynh_hoc_sinh phs ON ph.ma_phu_huynh = phs.ma_phu_huynh
                LEFT JOIN hoc_sinh hs ON phs.ma_hoc_sinh = hs.ma_hoc_sinh
                LEFT JOIN nguoi_dung nd ON phs.ma_hoc_sinh = nd.ma_nguoi_dung
                WHERE ph.ma_phu_huynh = (
                    SELECT ma_nguoi_dung FROM nguoi_dung WHERE ma_tai_khoan = :ma_tk
                )
                LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_tk' => $ma_tai_khoan]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getPhuHuynhInfo: " . $e->getMessage());
            return null;
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
     * HÀM MỚI: Lấy danh sách lớp theo trường nhé
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
     * HÀM MỚI: Lấy danh sách môn học (loại trừ Chào cờ và Sinh hoạt lớp)
     */
    public function getDanhSachMonHoc() {
        if ($this->db === null) return [];
        
        $sql = "SELECT ma_mon_hoc, ten_mon_hoc, loai_mon
                FROM mon_hoc 
                WHERE loai_mon != 'Hoạt động'
                ORDER BY 
                    CASE 
                        WHEN loai_mon = 'Bắt buộc' THEN 1
                        WHEN loai_mon LIKE 'Tự chọn%' THEN 2
                        ELSE 3
                    END,
                    ten_mon_hoc ASC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachMonHoc: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HÀM MỚI: Lấy danh sách khối (10, 11, 12)
     */
    // public function getDanhSachKhoi($ma_truong) {
    //     if ($this->db === null) return [];
        
    //     $sql = "SELECT DISTINCT khoi FROM lop_hoc 
    //             WHERE ma_truong = :ma_truong AND khoi IS NOT NULL 
    //             ORDER BY khoi ASC";
                
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([':ma_truong' => $ma_truong]);
    //         $result = $stmt->fetchAll();
    //         return array_map(function($row) { return $row['khoi']; }, $result);
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getDanhSachKhoi: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getDanhSachKhoi($ma_truong = null) {
        if ($this->db === null) return [];
        
        // ✅ Nếu không truyền ma_truong, lấy từ SESSION
        if (!$ma_truong) {
            $ma_truong = $_SESSION['admin_school_id'] ?? null;
        }
        
        if (!$ma_truong) return [];
        
        $sql = "SELECT DISTINCT khoi FROM lop_hoc 
                WHERE ma_truong = :ma_truong AND khoi IS NOT NULL 
                ORDER BY khoi ASC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_truong' => $ma_truong]);
            $result = $stmt->fetchAll();
            return array_map(function($row) { return $row['khoi']; }, $result);
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachKhoi: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HÀM MỚI: Lấy danh sách lớp theo khối
     */
    // public function getDanhSachLopTheoKhoi($ma_truong, $khoi) {
    //     if ($this->db === null) return [];
        
    //     $sql = "SELECT ma_lop, ten_lop, khoi 
    //             FROM lop_hoc 
    //             WHERE ma_truong = :ma_truong AND khoi = :khoi
    //             ORDER BY ten_lop ASC";
                
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([':ma_truong' => $ma_truong, ':khoi' => $khoi]);
    //         return $stmt->fetchAll();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getDanhSachLopTheoKhoi: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getDanhSachLopTheoKhoi($khoi = null, $ma_truong = null) {
        if ($this->db === null) return [];
        
        // ✅ Nếu không truyền ma_truong, lấy từ SESSION
        if (!$ma_truong) {
            $ma_truong = $_SESSION['admin_school_id'] ?? null;
        }
        
        if (!$ma_truong || !$khoi) return [];
        
        $sql = "SELECT ma_lop, ten_lop, khoi 
                FROM lop_hoc 
                WHERE ma_truong = :ma_truong AND khoi = :khoi
                ORDER BY ten_lop ASC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_truong' => $ma_truong, ':khoi' => $khoi]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachLopTheoKhoi: " . $e->getMessage());
            return [];
        }
    }


    /**
     * HÀM MỚI: Lấy tất cả lớp của trường (cho GV/BGH không cần chọn khối)
     */
    // public function getDanhSachLopAll($ma_truong) {
    //     if ($this->db === null) return [];
        
    //     $sql = "SELECT ma_lop, ten_lop, khoi 
    //             FROM lop_hoc 
    //             WHERE ma_truong = :ma_truong
    //             ORDER BY khoi ASC, ten_lop ASC";
                
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([':ma_truong' => $ma_truong]);
    //         return $stmt->fetchAll();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getDanhSachLopAll: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getDanhSachLopAll($ma_truong = null) {
        if ($this->db === null) return [];
        
        // ✅ Nếu không truyền ma_truong, lấy từ SESSION
        if (!$ma_truong) {
            $ma_truong = $_SESSION['admin_school_id'] ?? null;
        }
        
        if (!$ma_truong) return [];
        
        $sql = "SELECT ma_lop, ten_lop, khoi 
                FROM lop_hoc 
                WHERE ma_truong = :ma_truong
                ORDER BY khoi ASC, ten_lop ASC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_truong' => $ma_truong]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachLopAll: " . $e->getMessage());
            return [];
        }
    }


    /**
     * HÀM MỚI: Lấy danh sách học sinh theo trường (dùng cho phụ huynh nếu cần)
     */
    public function getDanhSachHocSinh($ma_truong) {
        if ($this->db === null) return [];
        
        $sql = "SELECT hs.ma_hoc_sinh, nd.ho_ten, lh.ten_lop
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
                WHERE hs.ma_truong = :ma_truong
                ORDER BY nd.ho_ten ASC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_truong' => $ma_truong]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachHocSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HÀM MỚI: Lấy danh sách học sinh CHƯA CÓ PHỤ HUYNH trong lớp
     */
    public function getDanhSachHocSinhChuaCoPhuHuynh($ma_lop) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    hs.ma_hoc_sinh, 
                    nd.ho_ten,
                    nd.ngay_sinh,
                    lh.ten_lop
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung 
                JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
                WHERE 
                    lh.ma_lop = :ma_lop
                    AND hs.ma_hoc_sinh NOT IN (
                        SELECT DISTINCT ma_hoc_sinh 
                        FROM phu_huynh_hoc_sinh
                    )
                ORDER BY nd.ho_ten ASC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_lop' => $ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachHocSinhChuaCoPhuHuynh: " . $e->getMessage());
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
    /**
     * TẠO TÀI KHOẢN MỚI (FULL LOGIC)
     */
    /**
     * TẠO TÀI KHOẢN MỚI (ĐÃ FIX LỖI COLUMN NOT FOUND)
     */
    /**
     * TẠO TÀI KHOẢN MỚI (FINAL FIX - CHUẨN CẤU TRÚC BẢNG)
     */
    public function createAccount($data) {
        $check = $this->checkDuplicates($data['email'], $data['so_dien_thoai'] ?? '');
        if ($check !== true) return $check;

        $school_id = $_SESSION['admin_school_id'] ?? ($data['ma_truong'] ?? null);

        // Validate đơn giản: GV/BGH phải chọn môn chuyên môn
        if ($data['vai_tro'] == 'GiaoVien' || $data['vai_tro'] == 'BanGiamHieu') {
            if (empty($data['mon_chuyen_mon'])) {
                return "Vui lòng chọn Môn chuyên môn.";
            }
        }

        $this->db->beginTransaction();
        try {
            // 1. Tạo Tài khoản
            $stmt = $this->db->prepare("INSERT INTO tai_khoan (username, password, vai_tro, trang_thai) VALUES (?, MD5(?), ?, 'HoatDong')");
            $stmt->execute([$data['email'], $data['password'], $data['vai_tro']]);
            $ma_tai_khoan = $this->db->lastInsertId();

            // 2. Tạo Người dùng
            $stmt = $this->db->prepare("INSERT INTO nguoi_dung (ma_tai_khoan, ho_ten, email, so_dien_thoai, dia_chi, ngay_sinh, gioi_tinh) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ma_tai_khoan, $data['ho_ten'], $data['email'], $data['so_dien_thoai'] ?? null, $data['dia_chi'] ?? null, $data['ngay_sinh'] ?? null, $data['gioi_tinh'] ?? null]);
            $ma_nguoi_dung = $this->db->lastInsertId();

            // 3. Xử lý Vai trò
            switch ($data['vai_tro']) {
                case 'HocSinh':
                    if (empty($data['ma_lop'])) { $this->db->rollBack(); return "Vui lòng chọn lớp."; }
                    if (!$school_id) { $this->db->rollBack(); return "Lỗi: Không xác định trường."; }
                    $stmt = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ma_lop, ma_truong) VALUES (?, ?, ?)");
                    $stmt->execute([$ma_nguoi_dung, $data['ma_lop'], $school_id]);
                    break;

                case 'GiaoVien':
                case 'BanGiamHieu':
                    // Validate
                    if (empty($data['ma_lop'])) {
                        $this->db->rollBack();
                        return "Lỗi: Vui lòng chọn lớp cho Giáo Viên/Ban Giám Hiệu.";
                    }

                    // 1. Xử lý riêng cho Ban Giám Hiệu
                    if ($data['vai_tro'] == 'BanGiamHieu') {
                        if ($school_id) {
                            $stmt = $this->db->prepare("INSERT INTO ban_giam_hieu (ma_bgh, ma_truong) VALUES (?, ?)");
                            $stmt->execute([$ma_nguoi_dung, $school_id]);
                        } else {
                            $stmt = $this->db->prepare("INSERT INTO ban_giam_hieu (ma_bgh) VALUES (?)");
                            $stmt->execute([$ma_nguoi_dung]);
                        }
                    }

                    // 2. ✅ SỬA: Chỉ INSERT ma_giao_vien vào bảng giao_vien
                    $stmt = $this->db->prepare("INSERT INTO giao_vien (ma_giao_vien) VALUES (?)");
                    $stmt->execute([$ma_nguoi_dung]);

                    // 3. ✅ INSERT vào bang_phan_cong (nơi lưu thông tin lớp + môn)
                    $stmt = $this->db->prepare("INSERT INTO bang_phan_cong (ten_bang_phan_cong, ma_giao_vien, ma_lop, ma_mon_hoc, so_tiet_tuan) 
                                                VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        'Phân công ban đầu', 
                        $ma_nguoi_dung, 
                        $data['ma_lop'], 
                        $data['mon_chuyen_mon'] ?? null,
                        3
                    ]);
                    break;

                case 'PhuHuynh':
                    if (!$school_id) { $this->db->rollBack(); return "Lỗi: Không xác định trường."; }
                    
                    // Tạo phụ huynh
                    $stmt = $this->db->prepare("INSERT INTO phu_huynh (ma_phu_huynh, ma_truong) VALUES (?, ?)");
                    $stmt->execute([$ma_nguoi_dung, $school_id]);
                    
                    // Liên kết với học sinh (nếu có chọn)
                    if (!empty($data['ma_hoc_sinh'])) {
                        $stmt = $this->db->prepare("INSERT INTO phu_huynh_hoc_sinh (ma_phu_huynh, ma_hoc_sinh) VALUES (?, ?)");
                        $stmt->execute([$ma_nguoi_dung, $data['ma_hoc_sinh']]);
                    }
                    break;
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
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

    /**
     * Hàm kiểm tra xem Lớp có học Môn này không (Dựa trên Tổ Hợp Môn)
     * Trả về: Số tiết học kỳ 1 (int) nếu hợp lệ, FALSE nếu không học.
     */
    /**
     * Hàm kiểm tra xem môn này có hợp lệ với lớp không
     * Hợp lệ khi: Nằm trong tổ hợp của lớp HOẶC là môn Bắt buộc
     * Trả về: Số tiết (int) hoặc false
     */
    /**
     * Kiểm tra chặt chẽ: Môn này có nằm trong Tổ hợp của lớp không?
     * Trả về: Số tiết (int) hoặc false
     */
    /**
     * Kiểm tra hợp lệ: Chấp nhận cả môn Tổ hợp và môn Bắt buộc
     */
    private function checkMonTrongToHop($ma_lop, $ma_mon_hoc) {
    // 1. SQL Check môn tổ hợp (Ưu tiên lấy số tiết chuẩn)
    $sql1 = "SELECT thmmh.so_tiet_hk1 
             FROM lop_hoc lh
             JOIN to_hop_mon thm ON lh.ma_to_hop_mon = thm.ma_to_hop_mon
             JOIN to_hop_mon_mon_hoc thmmh ON thm.ma_to_hop_mon = thmmh.ma_to_hop_mon
             WHERE lh.ma_lop = :ma_lop AND thmmh.ma_mon_hoc = :ma_mon_hoc";
             
    // 2. SQL Check thông tin môn để xem có phải bắt buộc không
    $sql2 = "SELECT loai_mon, so_tiet_hk1 FROM mon_hoc WHERE ma_mon_hoc = :ma_mon_hoc";

    try {
        // --- BƯỚC 1: CHECK TRONG TỔ HỢP TỰ CHỌN ---
        $stmt = $this->db->prepare($sql1);
        $stmt->execute([':ma_lop' => $ma_lop, ':ma_mon_hoc' => $ma_mon_hoc]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return (int)$result['so_tiet_hk1'];
        }

        // --- BƯỚC 2: CHECK MÔN BẮT BUỘC (QUỐC DÂN) ---
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([':ma_mon_hoc' => $ma_mon_hoc]);
        $monInfo = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($monInfo) {
            // Lấy số tiết mặc định, nếu null thì gán tạm bằng 3 (hoặc 4 tùy môn)
            $so_tiet = isset($monInfo['so_tiet_hk1']) ? (int)$monInfo['so_tiet_hk1'] : 3;

            // Check theo cột loai_mon (đã sửa từ loai_mon_hoc -> loai_mon)
            $loai_mon = $monInfo['loai_mon'] ?? '';
            
            // Nếu là môn "Bắt buộc" thì tất cả lớp đều học -> Cho phép luôn!
            if ($loai_mon === 'Bắt buộc') {
                return $so_tiet;
            }
        }

        // Không thuộc cả 2 trường hợp -> Lỗi thật
        return false; 

        } catch (PDOException $e) {
            error_log("Lỗi checkMonTrongToHop: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách môn học dựa theo lớp (thông qua Tổ hợp môn)
     */
    /**
     * Lấy danh sách môn: Bao gồm môn Tổ hợp của lớp VÀ môn Bắt buộc chung
     * (Trừ Chào cờ, Sinh hoạt lớp)
     */
    /**
     * Lấy danh sách môn CHUẨN XÁC theo Tổ hợp môn của lớp
     * (Không cộng gộp môn bắt buộc ngoài luồng)
     */
    /**
     * Lấy danh sách môn: Gộp cả Môn Tổ hợp (theo lớp) VÀ Môn Bắt buộc (Toán, Văn, Anh...)
     */
    public function getDanhSachMonTheoLop($ma_lop) {
        if ($this->db === null) return [];

        // Câu lệnh SQL:
        // Phần 1: Lấy môn theo tổ hợp của lớp (Lý, Hóa, Sử, Địa...)
        // Phần 2: Lấy môn Bắt buộc chung (Toán, Văn, Anh, GDQP...)
        // Cuối cùng loại bỏ các môn hoạt động không cần phân công
        $sql = "SELECT mh.ma_mon_hoc, mh.ten_mon_hoc 
                FROM mon_hoc mh
                WHERE 
                (
                    -- Điều kiện 1: Nằm trong tổ hợp của lớp
                    mh.ma_mon_hoc IN (
                        SELECT thmh.ma_mon_hoc
                        FROM lop_hoc lh
                        JOIN to_hop_mon_mon_hoc thmh ON lh.ma_to_hop_mon = thmh.ma_to_hop_mon
                        WHERE lh.ma_lop = :ma_lop
                    )
                    -- Điều kiện 2: Hoặc là môn bắt buộc
                    OR mh.loai_mon LIKE 'Bắt buộc%' 
                    OR mh.loai_mon LIKE 'BatBuoc%'
                )
                -- Loại trừ các môn không xếp thời khóa biểu
                AND mh.ten_mon_hoc NOT LIKE '%Chào cờ%' 
                AND mh.ten_mon_hoc NOT LIKE '%Sinh hoạt%'
                AND mh.ten_mon_hoc NOT LIKE '%Hoạt động trải nghiệm%'
                ORDER BY mh.ten_mon_hoc ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_lop' => $ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachMonTheoLop: " . $e->getMessage());
            return [];
        }
    }
}
?>
