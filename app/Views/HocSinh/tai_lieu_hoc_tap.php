<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tài Liệu Học Tập</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
	<style>
		:root { --sidebar-width: 280px; }
		body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f7fb; }
		.sidebar { width: var(--sidebar-width); position: fixed; inset: 0 auto 0 0; background: #fff; box-shadow: 2px 0 12px rgba(0,0,0,0.06); z-index: 1000; }
		.main-content { margin-left: var(--sidebar-width); padding: 24px; }
		.doc-card { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); transition: 0.2s ease; height: 100%; }
		.doc-card:hover { transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,0.08); }
		.doc-icon { width: 48px; height: 48px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #fff; background: linear-gradient(135deg, #4f8bff 0%, #735bff 100%); }
		.filter-card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-radius: 12px; }
		@media (max-width: 991px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; padding: 16px; } }
	</style>
</head>
<body>

	<!-- SIDEBAR (rút gọn, chỉ giữ liên kết tài liệu) -->
	<div class="sidebar d-flex flex-column">
		<div class="p-3 border-bottom text-center">
			<img src="https://cdn-icons-png.flaticon.com/512/3135/3135823.png" width="64" class="rounded-circle mb-2" alt="Avatar">
			<h6 class="fw-bold mb-0"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Học sinh'); ?></h6>
			<small class="text-muted">Lớp <?php echo htmlspecialchars($data['ten_lop'] ?? '---'); ?></small>
		</div>
		<ul class="nav flex-column mt-2 px-2">
			<li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-grid-fill me-2"></i>Tổng Quan</a></li>
			<li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/baitap/index"><i class="bi bi-journal-text me-2"></i>Bài Tập & Nộp Bài</a></li>
			<li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/hocsinh/diemdanh"><i class="bi bi-calendar-check me-2"></i>Điểm Danh</a></li>
			<li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/hocsinh/bangdiem"><i class="bi bi-bar-chart-line-fill me-2"></i>Bảng Điểm</a></li>
			<li class="nav-item"><a class="nav-link active" href="<?php echo BASE_URL; ?>/tailieu/hienThi"><i class="bi bi-file-earmark-pdf-fill me-2"></i>Tài Liệu Học Tập</a></li>
			<li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/hocsinhTkb/index"><i class="bi bi-calendar-week me-2"></i>Thời Khóa Biểu</a></li>
			<li class="nav-item mt-auto p-3 pt-0"><a class="nav-link text-danger bg-danger bg-opacity-10" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
		</ul>
	</div>

	<!-- MAIN CONTENT -->
	<div class="main-content">
		<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
			<div>
				<h4 class="fw-bold mb-1">📚 Tài Liệu Học Tập</h4>
				<div class="text-muted">Danh sách tài liệu do giáo viên chia sẻ</div>
			</div>
			<span class="badge bg-primary fs-6"><?php echo count($data['tai_lieu_list'] ?? []); ?> tài liệu</span>
		</div>

		<!-- FILTER -->
		<div class="card filter-card mb-4">
			<div class="card-body">
				<div class="row g-3 align-items-center">
					<div class="col-md-6">
						<label class="form-label fw-semibold">Lọc theo môn học</label>
						<select class="form-select" id="filterMon" onchange="filterDocuments()">
							<option value="">-- Tất cả môn học --</option>
							<?php foreach ($data['mon_hoc_list'] ?? [] as $mon): ?>
								<option value="<?php echo htmlspecialchars($mon['ma_mon_hoc']); ?>"><?php echo htmlspecialchars($mon['ten_mon_hoc']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label fw-semibold">Tìm kiếm</label>
						<input type="text" class="form-control" id="searchDoc" placeholder="Nhập tên tài liệu..." oninput="filterDocuments()">
					</div>
				</div>
			</div>
		</div>

		<!-- LIST -->
		<div class="row g-3" id="docList">
			<?php if (empty($data['tai_lieu_list'])): ?>
				<div class="col-12">
					<div class="text-center py-5 bg-white rounded-3 shadow-sm">
						<i class="bi bi-inbox fs-1 text-muted"></i>
						<p class="text-muted mt-2 mb-0">Chưa có tài liệu nào</p>
					</div>
				</div>
			<?php else: ?>
				<?php foreach ($data['tai_lieu_list'] as $tl): ?>
					<div class="col-md-6 col-lg-4 doc-item" data-mon="<?php echo htmlspecialchars($tl['ma_mon_hoc']); ?>" data-name="<?php echo htmlspecialchars(strtolower($tl['ten_tai_lieu'])); ?>">
						<div class="doc-card h-100 d-flex flex-column">
							<div class="d-flex align-items-start gap-3 mb-2">
								<div class="doc-icon"><i class="bi bi-file-earmark-text"></i></div>
								<div class="flex-grow-1">
									<h6 class="fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($tl['ten_tai_lieu']); ?></h6>
									<span class="badge bg-primary bg-opacity-10 text-primary"><?php echo htmlspecialchars($tl['ten_mon_hoc'] ?? 'Môn học'); ?></span>
									<span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($tl['loai_tai_lieu'] ?? 'Tài liệu'); ?></span>
								</div>
							</div>
							<p class="text-muted small mb-3 flex-grow-1"><?php echo htmlspecialchars($tl['mo_ta'] ?: 'Không có mô tả'); ?></p>
							<div class="d-flex justify-content-between align-items-center">
								<small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?php echo date('d/m/Y', strtotime($tl['ngay_tao'] ?? 'now')); ?></small>
								<a class="btn btn-sm btn-primary" href="<?php echo BASE_URL; ?>/tailieu/download/<?php echo $tl['ma_tai_lieu']; ?>">
									<i class="bi bi-download me-1"></i>Tải về
								</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
	function filterDocuments() {
		const mon = document.getElementById('filterMon').value.toLowerCase();
		const search = document.getElementById('searchDoc').value.toLowerCase();
		const items = document.querySelectorAll('.doc-item');

		items.forEach(item => {
			const itemMon = item.getAttribute('data-mon').toLowerCase();
			const itemName = item.getAttribute('data-name');
			const matchMon = !mon || itemMon === mon;
			const matchSearch = !search || itemName.includes(search);
			item.style.display = (matchMon && matchSearch) ? 'block' : 'none';
		});
	}
	</script>
</body>
</html>
