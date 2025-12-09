<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Phiếu / Biên Lai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Times New Roman', serif; }
        .phieu { 
            border: 2px solid #333; 
            padding: 40px; 
            max-width: 800px; 
            margin: 30px auto; 
            background-color: white;
            position: relative;
        }
        .header { text-align: center; margin-bottom: 30px; text-transform: uppercase; }
        .footer { margin-top: 40px; font-size: 0.9em; color: #666; }
        
        /* Con dấu ĐÃ THANH TOÁN */
        .stamp-paid {
            position: absolute;
            top: 150px;
            right: 50px;
            border: 5px solid #dc3545;
            color: #dc3545;
            font-size: 3rem;
            font-weight: bold;
            padding: 10px 20px;
            text-transform: uppercase;
            transform: rotate(-15deg);
            opacity: 0.8;
            mask-image: url('https://s3.amazonaws.com/spoonflower/public/design_thumbnails/0296/6334/rrgrunge_texture_shop_preview.png');
            -webkit-mask-image: url('https://s3.amazonaws.com/spoonflower/public/design_thumbnails/0296/6334/rrgrunge_texture_shop_preview.png');
            pointer-events: none;
        }

        @media print { 
            body { font-size: 12pt; background-color: white; -webkit-print-color-adjust: exact; } 
            .no-print { display: none !important; } 
            .phieu { border: 1px solid #000; margin: 0; box-shadow: none; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body onload="window.print()">

    <?php
    // --- LOGIC TỰ ĐỘNG PHÂN LOẠI ---
    // Dựa vào địa điểm để biết là Online hay Tiền mặt
    // (Vì ở Controller ta set: Online => 'Hệ thống thanh toán trực tuyến', Tiền mặt => 'Phòng Tài vụ...')
    $is_online = (strpos($data['dia_diem'], 'trực tuyến') !== false);

    if ($is_online) {
        $tieu_de = "BIÊN LAI THU TIỀN HỌC PHÍ ĐIỆN TỬ";
        $label_tien = "Số tiền đã thanh toán";
        $loi_dan = "Giao dịch đã hoàn tất trực tuyến. Phụ huynh vui lòng lưu phiếu này để đối chiếu khi cần thiết.";
        $mau_sac = "success"; // Xanh lá
    } else {
        $tieu_de = "PHIẾU THÔNG BÁO ĐÓNG HỌC PHÍ";
        $label_tien = "Số tiền phải đóng";
        $loi_dan = "Phụ huynh vui lòng mang phiếu này đến <b>" . $data['dia_diem'] . "</b> để hoàn tất đóng tiền.";
        $mau_sac = "primary"; // Xanh dương
    }
    ?>

    <div class="container">
        <div class="phieu shadow">
            
            <?php if($is_online): ?>
                <div class="stamp-paid">ĐÃ THANH TOÁN</div>
            <?php endif; ?>

            <div class="row">
                <div class="col-4 text-center">
                    <p class="mb-0 fw-bold">SỞ GIÁO DỤC & ĐÀO TẠO</p>
                    <p class="mb-0 fw-bold">TRƯỜNG THPT [TÊN TRƯỜNG]</p>
                    <hr class="w-50 mx-auto my-1">
                </div>
                <div class="col-8 text-end small">
                    <p class="mb-0">Mã phiếu: <strong><?php echo htmlspecialchars($data['stt_phieu']); ?></strong></p>
                    <p class="mb-0">Ngày in: <?php echo htmlspecialchars($data['ngay_tao']); ?></p>
                </div>
            </div>

            <div class="header mt-4">
                <h2 class="fw-bold text-<?php echo $mau_sac; ?>"><?php echo $tieu_de; ?></h2>
                <p class="fst-italic text-muted">Học kỳ 1 - Năm học <?php echo date('Y'); ?>-<?php echo date('Y')+1; ?></p>
            </div>
            
            <div class="card mb-4 border-0 bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">Họ tên phụ huynh: <strong class="fs-5"><?php echo htmlspecialchars($data['ten_phu_huynh']); ?></strong></p>
                            <p class="mb-0">Họ tên học sinh: <strong><?php echo htmlspecialchars($data['ten_con']); ?></strong></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-2">Mã hóa đơn gốc: <strong>#<?php echo htmlspecialchars($data['ma_hoa_don']); ?></strong></p>
                            <?php if ($is_online): ?>
                                <p class="mb-0 text-success"><i class="bi bi-check-circle-fill"></i> Xác nhận thanh toán qua Cổng Online</p>
                            <?php else: ?>
                                <p class="mb-0 text-danger"><i class="bi bi-hourglass-split"></i> Đang chờ nộp tiền mặt</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <table class="table table-bordered border-dark mt-3">
                <thead class="table-light border-dark">
                    <tr>
                        <th scope="col" class="text-center" width="50">STT</th>
                        <th scope="col">Nội dung khoản thu</th>
                        <th scope="col" class="text-end" width="200">Thành tiền (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td><?php echo htmlspecialchars($data['noi_dung']); ?></td>
                        <td class="text-end fw-bold"><?php echo htmlspecialchars($data['so_tien']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end fw-bold text-uppercase"><?php echo $label_tien; ?>:</td>
                        <td class="text-end fw-bold fs-5 text-danger"><?php echo htmlspecialchars($data['so_tien']); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mt-4">
                <p><strong>Thời hạn:</strong> <?php echo htmlspecialchars($data['thoi_han']); ?></p>
                <p><strong>Địa điểm/Kênh thanh toán:</strong> <?php echo htmlspecialchars($data['dia_diem']); ?></p>
            </div>
            
            <hr class="my-4">
            
            <div class="text-center">
                <p class="fst-italic"><?php echo $loi_dan; ?></p>
                <div class="row mt-5">
                    <div class="col-6">
                        <p class="fw-bold">Người lập phiếu</p>
                        <br><br><br>
                        <p class="small text-muted">(Ký, ghi rõ họ tên)</p>
                    </div>
                    <div class="col-6">
                        <p class="fw-bold">Người nộp tiền</p>
                        <br><br><br>
                        <p class="fw-bold"><?php echo htmlspecialchars($data['ten_phu_huynh']); ?></p>
                    </div>
                </div>
                <p class="footer mt-5">Hệ thống quản lý THPT Manager - In tự động lúc <?php echo date('H:i d/m/Y'); ?></p>
            </div>

            <div class="text-center no-print mt-5 pb-3">
                <button class="btn btn-primary btn-lg me-2 shadow" onclick="window.print()">
                    <i class="bi bi-printer-fill me-2"></i> IN PHIẾU / LƯU PDF
                </button>
                <button class="btn btn-outline-secondary btn-lg" onclick="window.close()">
                    Đóng cửa sổ
                </button>
            </div>
        </div>
    </div>
</body>
</html>