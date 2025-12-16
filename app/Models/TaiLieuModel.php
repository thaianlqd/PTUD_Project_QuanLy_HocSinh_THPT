<?php
class TaiLieuModel {
    private $db;

    public function __construct() {
        $ports = [3307, 3306];
        $connected = false;

        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->db->exec("SET NAMES 'utf8mb4'");
                $connected = true;
                break;
            } catch (PDOException $e) {
                error_log('DB connection failed: ' . $e->getMessage());
                continue;
            }
        }

        if (!$connected) {
            throw new Exception('Unable to connect to database on ports 3307/3306');
        }
    }

    // public function getDanhSachTaiLieuByMonHoc($ma_mon_hoc) {
    //     if ($this->db === null) return [];
        
    //     try {
    //         $sql = "SELECT 
    //                     tl.ma_tai_lieu,
    //                     tl.ten_tai_lieu,
    //                     tl.mo_ta,
    //                     tl.loai_tai_lieu,
    //                     tl.file_dinh_kem,
    //                     tl.ghi_chu,
    //                     tl.ngay_tao,
    //                     nd.ho_ten as giao_vien_up,
    //                     mh.ten_mon_hoc
    //                 FROM tai_lieu tl
    //                 JOIN giao_vien gv ON tl.ma_giao_vien = gv.ma_giao_vien
    //                 JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
    //                 JOIN mon_hoc mh ON tl.ma_mon_hoc = mh.ma_mon_hoc
    //                 WHERE tl.ma_mon_hoc = ? AND tl.trang_thai = 'Hien'
    //                 ORDER BY tl.ngay_tao DESC";
            
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_mon_hoc]);
    //         return $stmt->fetchAll();
    //     } catch (PDOException $e) {
    //         error_log('getDanhSachTaiLieuByMonHoc error: ' . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getDanhSachTaiLieuByMonHoc($ma_mon_hoc) {
        if ($this->db === null) return [];
        try {
            $sql = "SELECT tl.*, mh.ten_mon_hoc
                    FROM tai_lieu tl
                    LEFT JOIN mon_hoc mh ON tl.ma_mon_hoc = mh.ma_mon_hoc
                    WHERE tl.ma_mon_hoc = ?
                    ORDER BY tl.ma_tai_lieu DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_mon_hoc]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getDanhSachTaiLieuByMonHoc error: ' . $e->getMessage());
            return [];
        }
    }



    public function getDanhSachTaiLieuByGiaoVien($ma_giao_vien) {
        if ($this->db === null) return [];
        
        try {
            $sql = "SELECT 
                        tl.ma_tai_lieu,
                        tl.ten_tai_lieu,
                        tl.mo_ta,
                        tl.loai_tai_lieu,
                        tl.file_dinh_kem,
                        tl.ghi_chu,
                        tl.ngay_tao,
                        tl.ngay_cap_nhat,
                        tl.trang_thai,
                        mh.ten_mon_hoc,
                        mh.ma_mon_hoc
                    FROM tai_lieu tl
                    JOIN mon_hoc mh ON tl.ma_mon_hoc = mh.ma_mon_hoc
                    WHERE tl.ma_giao_vien = ?
                    ORDER BY tl.ngay_tao DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_giao_vien]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getDanhSachTaiLieuByGiaoVien error: ' . $e->getMessage());
            return [];
        }
    }

    public function getChiTietTaiLieu($ma_tai_lieu) {
        if ($this->db === null) return null;
        
        try {
            $sql = "SELECT 
                        tl.ma_tai_lieu,
                        tl.ten_tai_lieu,
                        tl.mo_ta,
                        tl.loai_tai_lieu,
                        tl.file_dinh_kem,
                        tl.ghi_chu,
                        tl.ngay_tao,
                        tl.trang_thai,
                        tl.ma_giao_vien,
                        tl.ma_mon_hoc,
                        nd.ho_ten as giao_vien_up,
                        mh.ten_mon_hoc
                    FROM tai_lieu tl
                    JOIN giao_vien gv ON tl.ma_giao_vien = gv.ma_giao_vien
                    JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                    JOIN mon_hoc mh ON tl.ma_mon_hoc = mh.ma_mon_hoc
                    WHERE tl.ma_tai_lieu = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_tai_lieu]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('getChiTietTaiLieu error: ' . $e->getMessage());
            return null;
        }
    }

    public function getMonGiangDay($ma_giao_vien) {
        if ($this->db === null) return [];
        
        try {
            $sql = "SELECT DISTINCT 
                        m.ma_mon_hoc,
                        m.ten_mon_hoc
                    FROM bang_phan_cong bpc
                    INNER JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                    WHERE bpc.ma_giao_vien = ?
                    AND m.ten_mon_hoc NOT IN ('Chào cờ', 'Sinh hoạt lớp')
                    ORDER BY m.ten_mon_hoc ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_giao_vien]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getMonGiangDay: " . $e->getMessage());
            return [];
        }
    }

    public function addTaiLieu($data) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL'];
        
        try {
            // ✅ Xóa duong_dan_file khỏi INSERT
            $sql = "INSERT INTO tai_lieu 
                    (ten_tai_lieu, mo_ta, loai_tai_lieu, file_dinh_kem, ghi_chu, ma_giao_vien, ma_mon_hoc, ngay_tao, ngay_cap_nhat, trang_thai)
                    VALUES (:ten, :mo_ta, :loai, :file, :ghi_chu, :ma_gv, :ma_mon, NOW(), NOW(), 'Hien')";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':ten' => $data['ten_tai_lieu'],
                ':mo_ta' => $data['mo_ta'] ?? '',
                ':loai' => $data['loai_tai_lieu'],
                ':file' => $data['file_dinh_kem'],
                ':ghi_chu' => $data['ghi_chu'] ?? '',
                ':ma_gv' => $data['ma_giao_vien'],
                ':ma_mon' => $data['ma_mon_hoc']
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Upload tài liệu thành công!',
                    'ma_tai_lieu' => $this->db->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => 'Lỗi khi thêm tài liệu'];
            }
        } catch (PDOException $e) {
            error_log('addTaiLieu error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public function updateTaiLieu($data) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL'];
        
        try {
            $sql = "UPDATE tai_lieu SET 
                    ten_tai_lieu = :ten,
                    mo_ta = :mo_ta,
                    loai_tai_lieu = :loai,
                    ghi_chu = :ghi_chu,
                    ngay_cap_nhat = NOW()
                    WHERE ma_tai_lieu = :ma_tai_lieu AND ma_giao_vien = :ma_gv";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':ten' => $data['ten_tai_lieu'],
                ':mo_ta' => $data['mo_ta'] ?? '',
                ':loai' => $data['loai_tai_lieu'],
                ':ghi_chu' => $data['ghi_chu'] ?? '',
                ':ma_tai_lieu' => $data['ma_tai_lieu'],
                ':ma_gv' => $data['ma_giao_vien']
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Cập nhật tài liệu thành công!'];
            } else {
                return ['success' => false, 'message' => 'Cập nhật thất bại'];
            }
        } catch (PDOException $e) {
            error_log('updateTaiLieu error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public function deleteTaiLieu($ma_tai_lieu, $ma_giao_vien) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL'];
        
        try {
            // Soft delete (không xóa file vì không lưu đường dẫn)
            $sql = "UPDATE tai_lieu SET trang_thai = 'An', ngay_cap_nhat = NOW() WHERE ma_tai_lieu = ? AND ma_giao_vien = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ma_tai_lieu, $ma_giao_vien]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Xóa tài liệu thành công!'];
            } else {
                return ['success' => false, 'message' => 'Bạn không có quyền xóa tài liệu này'];
            }
        } catch (PDOException $e) {
            error_log('deleteTaiLieu error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public function getDanhSachTaiLieuByLoai($ma_mon_hoc, $loai_tai_lieu) {
        if ($this->db === null) return [];
        
        try {
            $sql = "SELECT * FROM tai_lieu 
                    WHERE ma_mon_hoc = ? AND loai_tai_lieu = ? AND trang_thai = 'Hien'
                    ORDER BY ngay_tao DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_mon_hoc, $loai_tai_lieu]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getDanhSachTaiLieuByLoai error: ' . $e->getMessage());
            return [];
        }
    }

    public function countTaiLieuByGiaoVien($ma_giao_vien) {
        if ($this->db === null) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as count FROM tai_lieu WHERE ma_giao_vien = ? AND trang_thai = 'Hien'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_giao_vien]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log('countTaiLieuByGiaoVien error: ' . $e->getMessage());
            return 0;
        }
    }




    /**
     * Lấy tất cả tài liệu đang hiển thị
     */
    public function getTaiLieuHienThiAll() {
        if ($this->db === null) return [];
        try {
            $sql = "SELECT tl.*, mh.ten_mon_hoc
                    FROM tai_lieu tl
                    JOIN mon_hoc mh ON tl.ma_mon_hoc = mh.ma_mon_hoc
                    WHERE tl.trang_thai = 'Hien'
                    ORDER BY tl.ngay_tao DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getTaiLieuHienThiAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tài liệu theo danh sách môn (phù hợp lớp của HS)
     */
    public function getTaiLieuByMonList(array $ds_mon) {
        if ($this->db === null) return [];
        if (empty($ds_mon)) return [];
        try {
            $placeholders = implode(',', array_fill(0, count($ds_mon), '?'));
            $sql = "SELECT tl.*, mh.ten_mon_hoc
                    FROM tai_lieu tl
                    JOIN mon_hoc mh ON tl.ma_mon_hoc = mh.ma_mon_hoc
                    WHERE tl.trang_thai = 'Hien' AND tl.ma_mon_hoc IN ($placeholders)
                    ORDER BY tl.ngay_tao DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($ds_mon);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getTaiLieuByMonList error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin học sinh (kèm tên lớp)
     */
    public function getHocSinhInfo($ma_hoc_sinh) {
        if ($this->db === null) return null;
        try {
            $sql = "SELECT hs.*, l.ten_lop
                    FROM hoc_sinh hs
                    LEFT JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                    WHERE hs.ma_hoc_sinh = ?
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_hoc_sinh]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('getHocSinhInfo error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách môn của một lớp
     */
    public function getMonHocCuaLop($ma_lop) {
        if ($this->db === null) return [];
        try {
            $sql = "SELECT DISTINCT mh.ma_mon_hoc, mh.ten_mon_hoc
                    FROM bang_phan_cong bpc
                    JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                    WHERE bpc.ma_lop = ?
                      AND mh.ten_mon_hoc NOT IN ('Chào cờ','Sinh hoạt lớp')
                    ORDER BY mh.ten_mon_hoc ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getMonHocCuaLop error: ' . $e->getMessage());
            return [];
        }
    }
}
?>