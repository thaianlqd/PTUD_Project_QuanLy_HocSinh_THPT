<?php
class HocSinhCNModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL: Thử port 3307 trước, nếu lỗi thì thử 3306
        $ports = [3307, 3306];
        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                break;
            } catch (PDOException $e) { continue; }
        }

        if (!$this->db) {
            throw new PDOException('Không thể kết nối CSDL ở các port 3307/3306');
        }
    }

    // 1. Lấy danh sách học sinh theo trường
    public function getHocSinhBySchool($school_id, $keyword = '') {
        $sql = "SELECT 
                    hs.ma_hoc_sinh, 
                    hs.ngay_nhap_hoc, 
                    hs.trang_thai, -- Đã alias đúng tên cột
                    hs.ma_lop, 
                    nd.ho_ten, nd.ngay_sinh, nd.gioi_tinh, nd.so_dien_thoai, nd.dia_chi, nd.email,
                    lh.ten_lop, lh.khoi,
                    tk.username, tk.trang_thai as trang_thai_tk
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                JOIN tai_khoan tk ON nd.ma_tai_khoan = tk.ma_tai_khoan
                LEFT JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
                WHERE (hs.ma_truong = :sid OR lh.ma_truong = :sid)";

        // Giữ lại phần tìm kiếm (Search)
        if (!empty($keyword)) {
            $sql .= " AND (nd.ho_ten LIKE :kw OR nd.so_dien_thoai LIKE :kw OR tk.username LIKE :kw)";
        }
        
        $sql .= " ORDER BY hs.ma_hoc_sinh DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $params = [':sid' => $school_id];
            if (!empty($keyword)) {
                $params[':kw'] = "%$keyword%";
            }

            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getHocSinhBySchool error: ' . $e->getMessage());
            return [];
        }
    }

    // 2. Lấy danh sách Lớp (để hiện trong dropdown)
    public function getDanhSachLop($school_id) {
        $sql = "SELECT ma_lop, ten_lop, khoi FROM lop_hoc WHERE ma_truong = ? ORDER BY khoi ASC, ten_lop ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$school_id]);
        return $stmt->fetchAll();
    }

    // 3. THÊM HỌC SINH
    public function addStudent($data) {
        $maLop = $data['ma_lop'] ?? null;
        $maTruong = $data['ma_truong'] ?? null;
        $gioiTinh = $data['gioi_tinh'] ?? null;
        $ngaySinh = $data['ngay_sinh'] ?? null;
        $sdt = $data['so_dien_thoai'] ?? null;
        $email = $data['email'] ?? null;

        if (!$this->isClassBelongToSchool($maLop, $maTruong)) return "Lớp không thuộc trường!";
        if (!empty($gioiTinh) && !$this->isValidGender($gioiTinh)) return "Giới tính không hợp lệ!";
        if (!empty($ngaySinh) && !$this->isValidDate($ngaySinh)) return "Ngày sinh không hợp lệ!";
        if (!empty($sdt) && !$this->isValidPhone($sdt)) return "SĐT không hợp lệ!";
        if ($email && $this->checkDuplicate($email)) return "Email/Tên đăng nhập đã tồn tại!";

        $this->db->beginTransaction();
        try {
            // B1: Tạo Tài Khoản
            $sqlTK = "INSERT INTO tai_khoan (username, password, vai_tro, trang_thai) VALUES (?, MD5(?), 'HocSinh', 'HoatDong')";
            $stmt = $this->db->prepare($sqlTK);
            $stmt->execute([$email, $data['password']]);
            $ma_tk = $this->db->lastInsertId();

            // B2: Tạo Người Dùng
            $sqlND = "INSERT INTO nguoi_dung (ma_tai_khoan, ho_ten, email, so_dien_thoai, ngay_sinh, gioi_tinh, dia_chi) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sqlND);
            $stmt->execute([
                $ma_tk, $data['ho_ten'], $email, $sdt, 
                $ngaySinh, $gioiTinh, $data['dia_chi']
            ]);
            $ma_nd = $this->db->lastInsertId();

            // B3: Tạo Học Sinh
            $sqlHS = "INSERT INTO hoc_sinh (ma_hoc_sinh, ma_lop, ma_truong, trang_thai, ngay_nhap_hoc) 
                      VALUES (?, ?, ?, 'DangHoc', NOW())";
            $stmt = $this->db->prepare($sqlHS);
            $stmt->execute([$ma_nd, $maLop, $maTruong]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return "Lỗi hệ thống: " . $e->getMessage();
        }
    }

    // 4. SỬA HỌC SINH
    public function updateStudent($data) {
        $maHocSinh = $data['ma_hoc_sinh'] ?? null;
        $maLop = $data['ma_lop'] ?? null;
        $trangThai = $data['trang_thai'] ?? null;
        $gioiTinh = $data['gioi_tinh'] ?? null;
        $ngaySinh = $data['ngay_sinh'] ?? null;
        $sdt = $data['so_dien_thoai'] ?? null;
        $hoTen = $data['ho_ten'] ?? null;
        $diaChi = $data['dia_chi'] ?? null;

        $schoolId = $this->getSchoolIdByStudent($maHocSinh);
        if ($schoolId && !$this->isClassBelongToSchool($maLop, $schoolId)) {
            return "Lớp không thuộc trường!";
        }
        if (!empty($gioiTinh) && !$this->isValidGender($gioiTinh)) return "Giới tính không hợp lệ!";
        if (!empty($ngaySinh) && !$this->isValidDate($ngaySinh)) return "Ngày sinh không hợp lệ!";
        if (!empty($sdt) && !$this->isValidPhone($sdt)) return "SĐT không hợp lệ!";

        $emailUpdate = $data['email'] ?? null;
        if (!empty($emailUpdate)) {
            $maTk = $this->getAccountIdByStudent($maHocSinh);
            if ($maTk && $this->checkDuplicateExcept($emailUpdate, $maTk)) return "Email/Tên đăng nhập đã tồn tại!";
        }

        $this->db->beginTransaction();
        try {
            // Update Người dùng
            $sqlND = "UPDATE nguoi_dung SET ho_ten=?, ngay_sinh=?, gioi_tinh=?, dia_chi=?, so_dien_thoai=? WHERE ma_nguoi_dung=?";
            $stmt = $this->db->prepare($sqlND);
            $stmt->execute([$hoTen, $ngaySinh, $gioiTinh, $diaChi, $sdt, $maHocSinh]);

            if (!empty($emailUpdate)) {
                $this->db->prepare("UPDATE nguoi_dung SET email=? WHERE ma_nguoi_dung=?")
                    ->execute([$emailUpdate, $maHocSinh]);
                $maTk = $maTk ?? $this->getAccountIdByStudent($maHocSinh);
                if ($maTk) {
                    $this->db->prepare("UPDATE tai_khoan SET username=? WHERE ma_tai_khoan=?")
                        ->execute([$emailUpdate, $maTk]);
                }
            }

            // Update Học sinh
            $sqlHS = "UPDATE hoc_sinh SET ma_lop=?, trang_thai=? WHERE ma_hoc_sinh=?";
            $stmt = $this->db->prepare($sqlHS);
            $stmt->execute([$maLop, $trangThai, $maHocSinh]);

            // Đổi mật khẩu nếu có
            if (!empty($data['password'])) {
                $stmt = $this->db->prepare("SELECT ma_tai_khoan FROM nguoi_dung WHERE ma_nguoi_dung = ?");
                $stmt->execute([$maHocSinh]);
                $tk = $stmt->fetch();
                if ($tk) {
                    $this->db->prepare("UPDATE tai_khoan SET password = MD5(?) WHERE ma_tai_khoan = ?")
                             ->execute([$data['password'], $tk['ma_tai_khoan']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return "Lỗi: " . $e->getMessage();
        }
    }

    // 5. XÓA HỌC SINH
    public function deleteStudent($ma_hoc_sinh) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT ma_tai_khoan FROM nguoi_dung WHERE ma_nguoi_dung = ?");
            $stmt->execute([$ma_hoc_sinh]);
            $user = $stmt->fetch();

            if ($user) {
                // Xóa bảng con trước tránh mồ côi
                $this->db->prepare("DELETE FROM hoc_sinh WHERE ma_hoc_sinh = ?")
                    ->execute([$ma_hoc_sinh]);
                $this->db->prepare("DELETE FROM nguoi_dung WHERE ma_nguoi_dung = ?")
                    ->execute([$ma_hoc_sinh]);
                $this->db->prepare("DELETE FROM tai_khoan WHERE ma_tai_khoan = ?")
                    ->execute([$user['ma_tai_khoan']]);
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function checkDuplicate($email) {
        $stmt = $this->db->prepare("SELECT 1 FROM tai_khoan WHERE username = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn();
    }

    private function checkDuplicateExcept($email, $ma_tai_khoan) {
        $stmt = $this->db->prepare("SELECT 1 FROM tai_khoan WHERE username = ? AND ma_tai_khoan <> ?");
        $stmt->execute([$email, $ma_tai_khoan]);
        return $stmt->fetchColumn();
    }

    private function isClassBelongToSchool($ma_lop, $ma_truong) {
        $stmt = $this->db->prepare("SELECT 1 FROM lop_hoc WHERE ma_lop = ? AND ma_truong = ?");
        $stmt->execute([$ma_lop, $ma_truong]);
        return (bool)$stmt->fetchColumn();
    }

    private function getSchoolIdByStudent($ma_hoc_sinh) {
        $stmt = $this->db->prepare("SELECT ma_truong FROM hoc_sinh WHERE ma_hoc_sinh = ?");
        $stmt->execute([$ma_hoc_sinh]);
        return $stmt->fetchColumn() ?: null;
    }

    private function getAccountIdByStudent($ma_hoc_sinh) {
        $stmt = $this->db->prepare("SELECT ma_tai_khoan FROM nguoi_dung WHERE ma_nguoi_dung = ?");
        $stmt->execute([$ma_hoc_sinh]);
        $row = $stmt->fetch();
        return $row['ma_tai_khoan'] ?? null;
    }

    private function isValidDate($d) {
        if (empty($d)) return true;
        $t = strtotime($d);
        return $t && date('Y-m-d', $t) === $d;
    }

    private function isValidPhone($p) {
        return preg_match('/^[0-9]{8,15}$/', $p);
    }

    private function isValidGender($g) {
        return in_array($g, ['Nam', 'Nu', 'Khac'], true);
    }
}
?>