<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Thông Báo Thanh Toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        @media print { 
            body { font-size: 12pt; background-color: white; } 
            .no-print { display: none; } 
            .phieu { box-shadow: none; border: 1px solid #000; }
        }
        .phieu { 
            border: 2px solid #000; 
            padding: 20px; 
            max-width: 800px; 
            margin: 50px auto; 
            background-color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header { text-align: center; margin-bottom: 30px; }
        .info-section { margin-bottom: 20px; }
        .footer { margin-top: 30px; text-align: center; font-size: 0.9em; color: #666; }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="phieu">
            <div class="header">
                <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-receipt me-2"></i>PHIẾU THÔNG BÁO THANH TOÁN HỌC PHÍ</h3>
                <p class="mb-0"><strong>Trường THPT [Tên Trường]</strong></p>
            </div>
            
            <div class="info-section">
                <p class="mb-2"><strong>Số thứ tự phiếu:</strong> <?php echo htmlspecialchars($data['stt_phieu']); ?></p>
                <p class="mb-2"><strong>Ngày tạo phiếu:</strong> <?php echo htmlspecialchars($data['ngay_tao']); ?></p>
            </div>
            
            <hr style="border-top: 2px solid #000; margin: 20px 0;">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-section">
                        <p class="mb-1"><strong>Phụ huynh:</strong> <?php echo htmlspecialchars($data['ten_phu_huynh']); ?></p>
                        <p class="mb-0"><strong>Học sinh:</strong> <?php echo htmlspecialchars($data['ten_con']); ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-section">
                        <p class="mb-1"><strong>Mã hóa đơn:</strong> #<?php echo htmlspecialchars($data['ma_hoa_don']); ?></p>
                        <p class="mb-0"><strong>Nội dung thanh toán:</strong> <?php echo htmlspecialchars($data['noi_dung']); ?></p>
                    </div>
                </div>
            </div>
            
            <hr style="border-top: 2px solid #000; margin: 20px 0;">
            
            <div class="info-section">
                <p class="mb-2"><strong>Số tiền phải đóng:</strong> <span class="fw-bold text-danger fs-5"><?php echo htmlspecialchars($data['so_tien']); ?> VNĐ</span></p>
                <p class="mb-2"><strong>Thời hạn đóng:</strong> <span class="fw-bold"><?php echo htmlspecialchars($data['thoi_han']); ?></span></p>
                <p class="mb-4"><strong>Địa điểm đóng tiền:</strong> <?php echo htmlspecialchars($data['dia_diem']); ?></p>
            </div>
            
            <hr style="border-top: 2px solid #000; margin: 20px 0;">
            
            <div class="footer">
                <p class="mb-0">Mang phiếu này đến địa điểm trên để đóng tiền. Nhân viên tài vụ sẽ kiểm tra, xác nhận và cập nhật hệ thống sau khi nhận tiền.</p>
                <p class="mt-2 small">Lưu ý: Nếu quá thời hạn, vui lòng liên hệ Ban Giám hiệu để được hỗ trợ.</p>
                <p class="mt-3 small text-muted">Hệ thống THPT Manager - In tự động</p>
            </div>
            
            <div class="text-center no-print mt-4">
                <button class="btn btn-secondary me-2" onclick="window.print()"><i class="bi bi-printer me-1"></i>In Phiếu</button>
                <a href="<?php echo BASE_URL; ?>/thanhtoan/index" class="btn btn-primary"><i class="bi bi-arrow-left me-1"></i>Quay Lại Trang Thanh Toán</a>
            </div>
        </div>
    </div>
</body>
</html>