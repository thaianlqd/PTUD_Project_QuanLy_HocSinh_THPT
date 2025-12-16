<?php
class TruongHocModel {
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

	/**
	 * Lấy danh sách cơ bản của tất cả trường để hiển thị bộ lọc / dropdown.
	 */
	public function getDanhSachTruongCoBan() {
		try {
			$sql = "SELECT ma_truong, ten_truong FROM truong_thpt ORDER BY ten_truong";
			return $this->db->query($sql)->fetchAll();
		} catch (Exception $e) {
			error_log('getDanhSachTruongCoBan error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Lấy thống kê tổng hợp cho tất cả trường hoặc một trường.
	 */
	public function getThongKeTruong($ma_truong = null) {
		$params = [];

		$sql = "
			SELECT 
				t.ma_truong,
				t.ten_truong,
				t.dia_chi,
				t.so_dien_thoai,
				COALESCE(l.so_lop, 0) AS so_lop,
				COALESCE(l.tong_si_so, 0) AS tong_si_so_khai_bao,
				COALESCE(hs.so_hoc_sinh, 0) AS so_hoc_sinh_dang_hoc,
				COALESCE(gv.so_giao_vien, 0) AS so_giao_vien,
				COALESCE(qtv.so_qtv, 0) AS so_admin_truong,
				COALESCE(ph.so_phu_huynh, 0) AS so_phu_huynh,
				COALESCE(pc.so_phan_cong, 0) AS so_phan_cong_day
			FROM truong_thpt t
			LEFT JOIN (
				SELECT ma_truong, COUNT(DISTINCT ma_lop) AS so_lop, SUM(COALESCE(si_so, 0)) AS tong_si_so
				FROM lop_hoc
				WHERE trang_thai_lop = 'HoatDong'
				GROUP BY ma_truong
			) l ON l.ma_truong = t.ma_truong
			LEFT JOIN (
				SELECT lh.ma_truong, COUNT(DISTINCT hs.ma_hoc_sinh) AS so_hoc_sinh
				FROM hoc_sinh hs
				JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
				WHERE hs.trang_thai = 'DangHoc' AND lh.trang_thai_lop = 'HoatDong'
				GROUP BY lh.ma_truong
			) hs ON hs.ma_truong = t.ma_truong
			LEFT JOIN (
				SELECT lh.ma_truong, COUNT(DISTINCT pc.ma_giao_vien) AS so_giao_vien
				FROM bang_phan_cong pc
				JOIN lop_hoc lh ON pc.ma_lop = lh.ma_lop
				GROUP BY lh.ma_truong
			) gv ON gv.ma_truong = t.ma_truong
			LEFT JOIN (
				SELECT ma_truong, COUNT(DISTINCT ma_qtv) AS so_qtv
				FROM quan_tri_vien
				GROUP BY ma_truong
			) qtv ON qtv.ma_truong = t.ma_truong
			LEFT JOIN (
				SELECT lh.ma_truong, COUNT(DISTINCT phhs.ma_phu_huynh) AS so_phu_huynh
				FROM phu_huynh_hoc_sinh phhs
				JOIN hoc_sinh hs ON phhs.ma_hoc_sinh = hs.ma_hoc_sinh
				JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
				WHERE hs.trang_thai = 'DangHoc' AND lh.trang_thai_lop = 'HoatDong'
				GROUP BY lh.ma_truong
			) ph ON ph.ma_truong = t.ma_truong
			LEFT JOIN (
				SELECT lh.ma_truong, COUNT(DISTINCT pc.ma_phan_cong) AS so_phan_cong
				FROM bang_phan_cong pc
				JOIN lop_hoc lh ON pc.ma_lop = lh.ma_lop
				GROUP BY lh.ma_truong
			) pc ON pc.ma_truong = t.ma_truong
		";

		if (!empty($ma_truong)) {
			$sql .= " WHERE t.ma_truong = :ma_truong";
			$params[':ma_truong'] = $ma_truong;
		}

		$sql .= " ORDER BY t.ten_truong";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute($params);
			return $stmt->fetchAll();
		} catch (Exception $e) {
			error_log('getThongKeTruong error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Lấy thông tin chi tiết 1 trường (bao gồm thống kê).
	 */
	public function getChiTietTruong($ma_truong) {
		$rows = $this->getThongKeTruong($ma_truong);
		return $rows ? $rows[0] : null;
	}
}
