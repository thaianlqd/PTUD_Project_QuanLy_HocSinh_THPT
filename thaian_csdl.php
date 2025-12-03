-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 03, 2025 at 03:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thpt_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `bai_nop`
--

CREATE TABLE `bai_nop` (
  `ma_bai_nop` int(11) NOT NULL,
  `ma_bai_tap` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_nop` date DEFAULT curdate(),
  `trang_thai` enum('DaNop','ChamDiem','HoanThanh') DEFAULT 'DaNop',
  `diem_so` decimal(4,2) DEFAULT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `noi_dung_tra_loi` text DEFAULT NULL,
  `lan_nop` int(11) DEFAULT 1,
  `gio_bat_dau_lam_bai` datetime DEFAULT NULL COMMENT 'Lưu giờ HS bắt đầu làm bài trắc nghiệm'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bai_nop`
--

INSERT INTO `bai_nop` (`ma_bai_nop`, `ma_bai_tap`, `ma_nguoi_dung`, `ngay_nop`, `trang_thai`, `diem_so`, `file_dinh_kem`, `noi_dung_tra_loi`, `lan_nop`, `gio_bat_dau_lam_bai`) VALUES
(17, 3, 2, '2025-10-30', 'DaNop', NULL, 'uploads/bailam/bailam_3_hs_2_1761803852_01_22656871_NguyenThaiAn_Lab_Data_Preprocessing_BTChuong1_KTDLVUD__1_.docx', NULL, 1, NULL),
(18, 2, 2, '2025-10-30', 'DaNop', NULL, NULL, 'ask/.dmnaskdaal/sd,nas', 1, NULL),
(19, 1, 2, '2025-10-30', 'DaNop', NULL, NULL, '{\"q1\":\"A\",\"q2\":\"A\",\"q3\":\"A\",\"q4\":\"B\",\"q5\":\"B\",\"q6\":\"A\",\"q7\":\"A\",\"q8\":\"A\",\"q9\":\"B\",\"q10\":\"C\",\"q11\":\"B\",\"q12\":\"B\",\"q13\":\"C\",\"q14\":\"D\",\"q15\":\"D\",\"q16\":\"D\",\"q17\":\"C\",\"q18\":\"D\",\"q19\":\"D\",\"q20\":\"A\"}', 1, NULL),
(20, 4, 2, '2025-11-12', 'DaNop', NULL, NULL, 'dsadsaddddddddddddddd', 1, NULL),
(21, 9, 2, '2025-11-12', 'DaNop', NULL, NULL, '{\"q1\":\"A\",\"q2\":\"A\",\"q3\":\"A\"}', 1, NULL),
(22, 10, 2, '2025-11-14', 'DaNop', NULL, 'uploads/bailam/bailam_10_hs_2_1763091552_hihi.pdf', NULL, 1, NULL),
(23, 11, 2, '2025-11-14', 'DaNop', NULL, NULL, 'dassssssssssssssssssss', 1, NULL),
(24, 12, 2, '2025-11-14', 'DaNop', NULL, NULL, '{\"q1\":\"A\",\"q2\":\"A\",\"q3\":\"A\"}', 1, NULL),
(25, 13, 2, '2025-11-14', 'DaNop', NULL, 'uploads/bailam/bailam_13_hs_2_1763092498_hihi.pdf', NULL, 1, NULL),
(26, 7, 2, '2025-11-14', 'DaNop', NULL, NULL, 'dsadddddddddddddsadasdasd', 1, NULL),
(27, 18, 2, '2025-11-14', 'DaNop', NULL, NULL, NULL, 1, '2025-11-14 16:20:07'),
(28, 8, 2, '2025-11-14', 'DaNop', NULL, 'uploads/bailam/bailam_8_hs_2_1763112151_hihi.pdf', NULL, 1, NULL),
(29, 19, 2, '2025-11-14', 'DaNop', NULL, NULL, 'hihhhhhhhhhhhhhhhhhhhhhhhhhhh', 1, NULL),
(30, 17, 2, '2025-11-14', 'DaNop', NULL, NULL, 'dsaaaaaaaaaaaaaaaaaa', 1, NULL),
(31, 20, 2, '2025-11-14', 'DaNop', NULL, NULL, 'đâsdddddddddddddddddddddsda', 1, NULL),
(32, 21, 2, '2025-11-14', 'HoanThanh', 10.00, NULL, '{\"q1\":\"C\",\"q2\":\"B\",\"q3\":\"D\"}', 1, '2025-11-14 17:00:00'),
(33, 14, 2, '2025-11-14', 'DaNop', NULL, 'uploads/bailam/bailam_14_hs_2_1763114448_hihi.pdf', NULL, 1, NULL),
(36, 25, 2, '2025-11-14', 'HoanThanh', 6.67, NULL, '{\"q1\":\"C\",\"q2\":\"B\",\"q3\":\"A\"}', 1, '2025-11-14 23:00:55'),
(37, 26, 2, '2025-11-15', 'DaNop', NULL, NULL, 'hhhhhhhhhhhhhhhhhhhhhhhhhhhhh', 1, NULL),
(38, 27, 2, '2025-11-15', 'DaNop', NULL, 'uploads/bailam/bailam_27_hs_2_1763171013_hihi.pdf', NULL, 1, NULL),
(39, 28, 2, '2025-11-15', 'HoanThanh', 10.00, NULL, '{\"q1\":\"C\",\"q2\":\"B\",\"q3\":\"D\"}', 1, '2025-11-15 08:43:44'),
(40, 30, 2, '2025-11-15', 'DaNop', NULL, NULL, NULL, 1, '2025-11-15 08:46:40');

-- --------------------------------------------------------

--
-- Table structure for table `bai_tap`
--

CREATE TABLE `bai_tap` (
  `ma_bai_tap` int(11) NOT NULL,
  `ten_bai_tap` varchar(100) NOT NULL,
  `mo_ta` varchar(255) DEFAULT NULL,
  `ngay_giao` date DEFAULT curdate(),
  `han_nop` datetime NOT NULL,
  `loai_bai_tap` enum('TuLuan','TracNghiem','UploadFile') NOT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `ma_lop` int(11) DEFAULT NULL,
  `ma_giao_vien` int(11) DEFAULT NULL,
  `ma_mon_hoc` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bai_tap`
--

INSERT INTO `bai_tap` (`ma_bai_tap`, `ten_bai_tap`, `mo_ta`, `ngay_giao`, `han_nop`, `loai_bai_tap`, `file_dinh_kem`, `ma_lop`, `ma_giao_vien`, `ma_mon_hoc`) VALUES
(1, 'Kiểm tra Trắc nghiệm Toán Chương 1', 'Ôn tập kiến thức về Mệnh đề và Tập hợp. Chọn đáp án đúng nhất.', '2025-10-28', '2025-11-04 00:00:00', 'TracNghiem', NULL, 1, NULL, NULL),
(2, 'Phân tích truyện ngắn Lão Hạc', 'Nêu cảm nhận của em về nhân vật Lão Hạc và giá trị nhân đạo của tác phẩm.', '2025-10-29', '2025-11-05 00:00:00', 'TuLuan', NULL, 1, NULL, NULL),
(3, 'Báo cáo tìm hiểu về Lịch sử Internet', 'Nộp file báo cáo (PDF hoặc DOCX) trình bày quá trình hình thành và phát triển của Internet.', '2025-10-30', '2025-11-07 00:00:00', 'UploadFile', NULL, 1, NULL, NULL),
(4, 'tự luận môn toán', 'nộp bài đúng hạn', '2025-11-12', '2025-11-12 00:00:00', 'TuLuan', 'uploads/debai/debai_1_1762915524_filecuabo.docx', 1, 3, 1),
(5, 'tự luận môn toán', 'dsdsdsds', '2025-11-12', '2025-11-13 00:00:00', 'TuLuan', NULL, 1, 3, 1),
(6, 'nộp file môn toán', 'dsdsdasd', '2025-11-12', '2025-11-13 00:00:00', 'UploadFile', NULL, 1, 3, 1),
(7, 'tự luận môn toán', 'dsadasd', '2025-11-12', '2025-12-13 00:00:00', 'TuLuan', NULL, 1, 3, 1),
(8, 'nộp file môn toán 2', 'fdfsdfs', '2025-11-12', '2025-12-13 00:00:00', 'UploadFile', NULL, 1, 3, 1),
(9, 'trắc nghiệm môn toán', 'gege', '2025-11-12', '2025-02-05 00:00:00', 'TracNghiem', NULL, 1, 3, 1),
(10, 'nộp file môn toán hhiihih', 'nhé', '2025-11-14', '2025-11-15 00:00:00', 'UploadFile', NULL, 1, 3, 1),
(11, 'tự luận toán hihihih', 'dsdsadasd', '2025-11-14', '2025-11-15 00:00:00', 'TuLuan', 'uploads/debai/debai_1_1763091742_hihi.pdf', 1, 3, 1),
(12, 'trắc nghiệm môn toán hihih', 'dsadasdas', '2025-11-14', '2025-11-15 00:00:00', 'TracNghiem', NULL, 1, 3, 1),
(13, 'nộp file môn toán - thái an', 'dssdsdsds', '2025-11-14', '2025-11-15 00:00:00', 'UploadFile', NULL, 1, 3, 1),
(14, 'nộp file môn toán - thái an 22222222222', 'sdasdsad', '2025-11-14', '2025-11-15 00:00:00', 'UploadFile', 'uploads/debai/debai_1_1763092820_hihi.pdf', 1, 3, 1),
(15, 'nộp file môn toán - thái an 3333333333', 'sadsaddas', '2025-11-14', '2025-11-15 00:00:00', 'UploadFile', NULL, 1, 3, 1),
(16, 'nộp file môn toán - thái an 4444444', 'hehe', '2025-11-14', '2025-11-15 00:00:00', 'UploadFile', NULL, 1, 3, 1),
(17, 'tự luận môn toán - thái an 44444444', 'hehehe', '2025-11-14', '2025-11-15 00:00:00', 'TuLuan', NULL, 1, 3, 1),
(18, 'trắc nghiệm môn toántrắc - thái an 444444', 'ehhee', '2025-11-14', '2025-11-15 00:00:00', 'TracNghiem', NULL, 1, 3, 1),
(19, 'thái an tự luận', 'hehe', '2025-11-14', '2025-11-15 19:30:00', 'TuLuan', NULL, 1, 3, 1),
(20, 'thái annnn tự luậnnnnnnnnnnnnnnnnnnn', 'hehe', '2025-11-14', '2025-11-15 19:30:00', 'TuLuan', NULL, 1, 3, 1),
(21, 'trắc nghiệmmmmmmmmmmmmmm', 'đâsdasd', '2025-11-14', '2025-11-15 19:30:00', 'TracNghiem', NULL, 1, 3, 1),
(22, 'nộp fileeeeeeeeeeeeeee', 'dsadasdasd', '2025-11-14', '2025-11-15 19:30:00', 'UploadFile', NULL, 1, 3, 1),
(23, 'abcd', 'aaaaa', '2025-11-14', '2025-11-15 19:30:00', 'TuLuan', NULL, 1, 3, 1),
(24, 'abcd - nộp file', 'aaaa', '2025-11-14', '2025-11-15 19:30:00', 'UploadFile', NULL, 1, 3, 1),
(25, 'abcd - trắc nghiệm', 'aaaa', '2025-11-14', '2025-11-15 19:30:00', 'TracNghiem', NULL, 1, 3, 1),
(26, 'upload nèeeeeeeee', 'dsadsadasdd', '2025-11-15', '2025-11-15 19:30:00', 'TuLuan', NULL, 1, 3, 1),
(27, 'upl', 'dsdasadasd', '2025-11-15', '2025-11-15 19:30:00', 'UploadFile', NULL, 1, 3, 1),
(28, 'upload nèeeeeeeee', 'aaaaaaaaa', '2025-11-15', '2025-11-15 19:30:00', 'TracNghiem', NULL, 1, 3, 1),
(29, 'hihiihihh', 'aaaaa', '2025-11-15', '2025-11-15 19:30:00', 'TracNghiem', NULL, 1, 3, 1),
(30, 'hehehhee', 'aaaa', '2025-11-15', '2025-11-15 19:30:00', 'TracNghiem', NULL, 1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bai_tap_trac_nghiem`
--

CREATE TABLE `bai_tap_trac_nghiem` (
  `ma_bai_tap` int(11) NOT NULL,
  `danh_sach_cau_hoi` text DEFAULT NULL,
  `thoi_gian_lam_bai` int(11) DEFAULT NULL,
  `so_lan_lam` int(11) DEFAULT 1,
  `cach_tinh_diem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bai_tap_trac_nghiem`
--

INSERT INTO `bai_tap_trac_nghiem` (`ma_bai_tap`, `danh_sach_cau_hoi`, `thoi_gian_lam_bai`, `so_lan_lam`, `cach_tinh_diem`) VALUES
(1, '{\n  \"questions\": [\n    {\"id\": 1, \"text\": \"Mệnh đề \\\"∀x ∈ ℝ, x² ≥ 0\\\" đúng hay sai?\", \"options\": [\"A. Đúng\", \"B. Sai\"], \"correct\": \"A\"},\n    {\"id\": 2, \"text\": \"Phủ định của mệnh đề \\\"∃x ∈ ℚ, x² = 2\\\" là gì?\", \"options\": [\"A. ∀x ∈ ℚ, x² ≠ 2\", \"B. ∀x ∈ ℚ, x² = 2\", \"C. ∃x ∈ ℚ, x² ≠ 2\", \"D. ∀x ∉ ℚ, x² = 2\"], \"correct\": \"A\"},\n    {\"id\": 3, \"text\": \"Tập hợp A = {x ∈ ℕ | x < 5} gồm các phần tử nào?\", \"options\": [\"A. {1, 2, 3, 4}\", \"B. {0, 1, 2, 3, 4}\", \"C. {1, 2, 3, 4, 5}\", \"D. {0, 1, 2, 3}\"], \"correct\": \"B\"},\n    {\"id\": 4, \"text\": \"Cho A = {1, 2}, B = {2, 3}. A ∪ B bằng gì?\", \"options\": [\"A. {2}\", \"B. {1, 3}\", \"C. {1, 2, 3}\", \"D. {1, 2, 2, 3}\"], \"correct\": \"C\"},\n    {\"id\": 5, \"text\": \"Cho A = {1, 2}, B = {2, 3}. A ∩ B bằng gì?\", \"options\": [\"A. {2}\", \"B. {1, 3}\", \"C. {1, 2, 3}\", \"D. {}\"], \"correct\": \"A\"},\n    {\"id\": 6, \"text\": \"Cho A = {1, 2}. Tập con của A có 1 phần tử là?\", \"options\": [\"A. {1}, {2}\", \"B. {}, {1}, {2}\", \"C. {1, 2}\", \"D. {}\"], \"correct\": \"A\"},\n    {\"id\": 7, \"text\": \"Số phần tử của tập rỗng là bao nhiêu?\", \"options\": [\"A. 0\", \"B. 1\", \"C. Vô số\", \"D. Không xác định\"], \"correct\": \"A\"},\n    {\"id\": 8, \"text\": \"Ký hiệu nào biểu thị \\\"là tập con của\\\"?\", \"options\": [\"A. ∈\", \"B. ⊂\", \"C. ∪\", \"D. ∩\"], \"correct\": \"B\"},\n    {\"id\": 9, \"text\": \"Mệnh đề P ⇒ Q sai khi nào?\", \"options\": [\"A. P đúng, Q sai\", \"B. P sai, Q đúng\", \"C. P sai, Q sai\", \"D. P đúng, Q đúng\"], \"correct\": \"A\"},\n    {\"id\": 10, \"text\": \"Tập (-∞; 3] ∩ [0; 5) là tập nào?\", \"options\": [\"A. [0; 3]\", \"B. (0; 3]\", \"C. [0; 5)\", \"D. (-∞; 5)\"], \"correct\": \"A\"},\n    {\"id\": 11, \"text\": \"Câu nào sau đây là mệnh đề?\", \"options\": [\"A. Bạn học giỏi quá!\", \"B. Số 5 là số nguyên tố.\", \"C. Hôm nay trời đẹp không?\", \"D. Hãy làm bài tập!\"], \"correct\": \"B\"},\n    {\"id\": 12, \"text\": \"Tập hợp các số tự nhiên chẵn nhỏ hơn 6?\", \"options\": [\"A. {2, 4}\", \"B. {0, 2, 4}\", \"C. {2, 4, 6}\", \"D. {0, 2, 4, 6}\"], \"correct\": \"B\"},\n    {\"id\": 13, \"text\": \"Cho A = {a, b}, B = {a, b, c}. Khẳng định nào đúng?\", \"options\": [\"A. A = B\", \"B. A ⊂ B\", \"C. B ⊂ A\", \"D. A ∈ B\"], \"correct\": \"B\"},\n    {\"id\": 14, \"text\": \"Phần bù của tập A trong tập E là gì?\", \"options\": [\"A. E \\\\ A\", \"B. A \\\\ E\", \"C. A ∪ E\", \"D. A ∩ E\"], \"correct\": \"A\"},\n    {\"id\": 15, \"text\": \"Mệnh đề đảo của \\\"Nếu tam giác ABC vuông thì AB² + AC² = BC²\\\" là gì?\", \"options\": [\"A. Nếu AB² + AC² ≠ BC² thì tam giác ABC không vuông\", \"B. Nếu AB² + AC² = BC² thì tam giác ABC vuông\", \"C. Nếu tam giác ABC không vuông thì AB² + AC² ≠ BC²\", \"D. Tam giác ABC vuông khi và chỉ khi AB² + AC² = BC²\"], \"correct\": \"B\"},\n    {\"id\": 16, \"text\": \"Tập [-2; 4) \\\\ (0; 5] bằng tập nào?\", \"options\": [\"A. [-2; 0]\", \"B. [-2; 0)\", \"C. (4; 5]\", \"D. [-2; 5]\"], \"correct\": \"A\"},\n    {\"id\": 17, \"text\": \"Ký hiệu ℝ là tập hợp nào?\", \"options\": [\"A. Số tự nhiên\", \"B. Số nguyên\", \"C. Số hữu tỉ\", \"D. Số thực\"], \"correct\": \"D\"},\n    {\"id\": 18, \"text\": \"Phần tử nào thuộc tập A = {x ∈ ℤ | -1 < x ≤ 2}?\", \"options\": [\"A. -1\", \"B. 0\", \"C. 3\", \"D. 2.5\"], \"correct\": \"B\"},\n    {\"id\": 19, \"text\": \"Cho A = (1; 5], B = [3; 7). A ∩ B là?\", \"options\": [\"A. [3; 5]\", \"B. (1; 7)\", \"C. (3; 5]\", \"D. [3; 5)\"], \"correct\": \"A\"},\n    {\"id\": 20, \"text\": \"Mệnh đề nào sau đây SAI?\", \"options\": [\"A. π là số vô tỉ\", \"B. √4 là số hữu tỉ\", \"C. 0 là số tự nhiên\", \"D. -3 là số nguyên dương\"], \"correct\": \"D\"}\n  ]\n}\n', 45, 1, NULL),
(9, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 45, 1, NULL),
(12, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 45, 1, NULL),
(18, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 45, 1, NULL),
(21, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 45, 1, NULL),
(25, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 45, 1, NULL),
(28, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 45, 1, NULL),
(29, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 45, 1, NULL),
(30, '{\"questions\":[{\"id\":1,\"text\":\"Thủ đô của Việt Nam là gì?\",\"options\":[\"A. TP. Hồ Chí Minh\",\"B. Đà Nẵng\",\"C. Hà Nội\",\"D. Hải Phòng\"],\"correct\":\"C\"},{\"id\":2,\"text\":\"1 + 1 bằng mấy?\",\"options\":[\"A. 1\",\"B. 2\",\"C. 3\",\"D. 4\"],\"correct\":\"B\"},{\"id\":3,\"text\":\"Con vật nào sau đây đẻ trứng?\",\"options\":[\"A. Con Chó\",\"B. Con Mèo\",\"C. Con Lợn\",\"D. Con Gà\"],\"correct\":\"D\"}]}', 10, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bai_tap_tu_luan`
--

CREATE TABLE `bai_tap_tu_luan` (
  `ma_bai_tap` int(11) NOT NULL,
  `de_bai_chi_tiet` text DEFAULT NULL,
  `yeu_cau_dung_luong` int(11) DEFAULT NULL,
  `tieu_chi_cham_diem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bai_tap_tu_luan`
--

INSERT INTO `bai_tap_tu_luan` (`ma_bai_tap`, `de_bai_chi_tiet`, `yeu_cau_dung_luong`, `tieu_chi_cham_diem`) VALUES
(2, 'Dựa vào văn bản truyện ngắn Lão Hạc của Nam Cao, hãy viết bài văn phân tích:\n- Hình ảnh và số phận của người nông dân Việt Nam trước Cách mạng tháng Tám qua nhân vật Lão Hạc.\n- Tình cảm sâu sắc của Lão Hạc dành cho con trai và con chó Vàng.\n- Giá trị nhân đạo và hiện thực của tác phẩm.', NULL, '- Mở bài giới thiệu tác giả, tác phẩm, vấn đề nghị luận.\n- Thân bài triển khai đủ các ý, có dẫn chứng, lập luận chặt chẽ.\n- Kết bài nêu cảm nghĩ, đánh giá chung.\n- Diễn đạt trôi chảy, đúng chính tả, ngữ pháp.'),
(4, '', NULL, NULL),
(5, 'đasadasdasd', NULL, NULL),
(7, 'ds', NULL, NULL),
(11, 'dsdasdsad', NULL, NULL),
(17, 'dấdasd', NULL, NULL),
(19, 'thái an nè hihi', NULL, NULL),
(20, 'dsdadasdsadd', NULL, NULL),
(23, 'aaaaaaaaaaaaaaa', NULL, NULL),
(26, 'đề bài ở đây', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bai_tap_upload_file`
--

CREATE TABLE `bai_tap_upload_file` (
  `ma_bai_tap` int(11) NOT NULL,
  `loai_file_cho_phep` varchar(100) DEFAULT NULL,
  `dung_luong_toi_da` int(11) DEFAULT NULL,
  `so_luong_file_toi_da` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bai_tap_upload_file`
--

INSERT INTO `bai_tap_upload_file` (`ma_bai_tap`, `loai_file_cho_phep`, `dung_luong_toi_da`, `so_luong_file_toi_da`) VALUES
(3, '.pdf,.docx', 5, 1),
(6, '.pdf,.docx,.zip', 5, 1),
(8, '.pdf,.docx,.zip', 5, 1),
(10, '.pdf,.docx,.zip', 5, 1),
(13, '.pdf,.docx,.zip', 5, 1),
(14, '.pdf,.docx,.zip', 5, 1),
(15, '.pdf,.docx,.zip', 5, 1),
(16, '.pdf,.docx,.zip', 5, 1),
(22, '.pdf,.docx,.zip', 5, 1),
(24, '.pdf,.docx,.zip', 5, 1),
(27, '.pdf,.docx,.zip', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bai_viet`
--

CREATE TABLE `bai_viet` (
  `ma_bai_viet` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `tieu_de` varchar(100) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `ngay_dang` date DEFAULT curdate(),
  `tac_gia` varchar(100) NOT NULL,
  `loai_bai_viet` varchar(50) DEFAULT NULL,
  `trang_thai` enum('DaDang','Nhap','DaXoa') DEFAULT 'Nhap'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bang_phan_cong`
--

CREATE TABLE `bang_phan_cong` (
  `ma_phan_cong` int(11) NOT NULL,
  `ten_bang_phan_cong` varchar(100) NOT NULL,
  `trang_thai` enum('HoatDong','DaXoa') DEFAULT 'HoatDong',
  `ma_mon_hoc` int(11) NOT NULL,
  `ma_giao_vien` int(11) NOT NULL,
  `ma_lop` int(11) NOT NULL,
  `so_tiet_tuan` int(11) DEFAULT NULL COMMENT 'Số tiết/tuần thực tế phân công cho GV này, lớp này (quan trọng cho ràng buộc)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bang_phan_cong`
--

INSERT INTO `bang_phan_cong` (`ma_phan_cong`, `ten_bang_phan_cong`, `trang_thai`, `ma_mon_hoc`, `ma_giao_vien`, `ma_lop`, `so_tiet_tuan`) VALUES
(1, 'PC_10A1_Toan', 'HoatDong', 1, 3, 1, 5),
(2, 'PC_10A1_Van', 'HoatDong', 2, 4, 1, 5),
(3, 'PC_10A1_Anh', 'HoatDong', 3, 5, 1, 4),
(4, 'PC_10A1_Su', 'HoatDong', 4, 6, 1, 2),
(5, 'PC_10A1_GDTC', 'HoatDong', 5, 7, 1, 2),
(6, 'PC_10A1_GDQP', 'HoatDong', 6, 8, 1, 2),
(7, 'PC_10A1_HDTN', 'HoatDong', 7, 9, 1, 4),
(8, 'PC_10A1_GDDP', 'HoatDong', 8, 9, 1, 1),
(9, 'PC_10A1_Ly', 'HoatDong', 11, 10, 1, 5),
(10, 'PC_10A1_Hoa', 'HoatDong', 12, 11, 1, 5),
(11, 'PC_10A1_Sinh', 'HoatDong', 13, 9, 1, 5),
(12, 'PC_10A1_Tin', 'HoatDong', 15, 13, 1, 4),
(13, 'PC_10A1_ChaoCo', 'HoatDong', 18, 9, 1, 1),
(14, 'PC_10A2_Toan', 'HoatDong', 1, 3, 2, 5),
(15, 'PC_10A2_Van', 'HoatDong', 2, 4, 2, 5),
(16, 'PC_10A2_Anh', 'HoatDong', 3, 5, 2, 4),
(17, 'PC_10A2_Su', 'HoatDong', 4, 6, 2, 2),
(18, 'PC_10A2_GDTC', 'HoatDong', 5, 7, 2, 2),
(19, 'PC_10A2_GDQP', 'HoatDong', 6, 8, 2, 2),
(20, 'PC_10A2_HDTN', 'HoatDong', 7, 12, 2, 4),
(21, 'PC_10A2_GDDP', 'HoatDong', 8, 12, 2, 1),
(22, 'PC_10A2_Dia', 'HoatDong', 9, 15, 2, 5),
(23, 'PC_10A2_Ly', 'HoatDong', 11, 10, 2, 5),
(24, 'PC_10A2_Hoa', 'HoatDong', 12, 11, 2, 5),
(25, 'PC_10A2_Tin', 'HoatDong', 15, 13, 2, 4),
(26, 'PC_10A2_ChaoCo', 'HoatDong', 18, 12, 2, 1),
(27, 'PC_10D1_Toan', 'HoatDong', 1, 3, 3, 5),
(28, 'PC_10D1_Van', 'HoatDong', 2, 4, 3, 5),
(29, 'PC_10D1_Anh', 'HoatDong', 3, 5, 3, 4),
(30, 'PC_10D1_Su', 'HoatDong', 4, 6, 3, 2),
(31, 'PC_10D1_GDTC', 'HoatDong', 5, 7, 3, 2),
(32, 'PC_10D1_GDQP', 'HoatDong', 6, 8, 3, 2),
(33, 'PC_10D1_HDTN', 'HoatDong', 7, 14, 3, 4),
(34, 'PC_10D1_GDDP', 'HoatDong', 8, 14, 3, 1),
(35, 'PC_10D1_Dia', 'HoatDong', 9, 15, 3, 5),
(36, 'PC_10D1_KTPL', 'HoatDong', 10, 16, 3, 5),
(37, 'PC_10D1_Sinh', 'HoatDong', 13, 9, 3, 5),
(38, 'PC_10D1_Tin', 'HoatDong', 15, 13, 3, 4),
(39, 'PC_10D1_ChaoCo', 'HoatDong', 18, 14, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ban_giam_hieu`
--

CREATE TABLE `ban_giam_hieu` (
  `ma_bgh` int(11) NOT NULL,
  `chuc_vu` varchar(100) DEFAULT 'Ban Giám Hiệu',
  `quyen_han` varchar(255) DEFAULT NULL COMMENT 'Quyền hạn cụ thể (nếu cần)',
  `ngay_bo_nhiem` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ban_giam_hieu`
--

INSERT INTO `ban_giam_hieu` (`ma_bgh`, `chuc_vu`, `quyen_han`, `ngay_bo_nhiem`) VALUES
(54, 'Hiệu Trưởng', NULL, '2025-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `bien_nhan_thanh_toan`
--

CREATE TABLE `bien_nhan_thanh_toan` (
  `ma_bien_nhan` int(11) NOT NULL,
  `ma_hoa_don` int(11) NOT NULL,
  `ngay_lap_bien_nhan` date DEFAULT curdate(),
  `noi_dung_thanh_toan` varchar(255) DEFAULT NULL,
  `trang_thai` enum('DaThanhToan','ChuaThanhToan') DEFAULT 'ChuaThanhToan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bien_nhan_thanh_toan`
--

INSERT INTO `bien_nhan_thanh_toan` (`ma_bien_nhan`, `ma_hoa_don`, `ngay_lap_bien_nhan`, `noi_dung_thanh_toan`, `trang_thai`) VALUES
(1, 1, '2025-11-13', 'Thanh toán học phí qua VNPAY. Mã GD: VNP_1763050335', 'DaThanhToan'),
(2, 4, '2025-11-16', 'Thanh toán học phí qua VNPAY. Mã GD: 15264005', 'DaThanhToan'),
(3, 3, '2025-11-16', 'Thanh toán học phí qua VNPAY. Mã GD: 15264007', 'DaThanhToan'),
(4, 8, '2025-11-17', 'Thanh toán học phí qua VNPAY. Mã GD: 15265914', 'DaThanhToan'),
(5, 12, '2025-11-17', 'Thanh toán học phí qua Sepay QR. Mã GD: FT25321903440800', 'DaThanhToan'),
(6, 19, '2025-12-02', 'Thanh toán học phí qua VNPAY. Mã GD: 15314501', 'DaThanhToan'),
(7, 15, '2025-12-02', 'Thanh toán học phí qua Sepay QR. Mã GD: FT25336103026453', 'DaThanhToan');

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_diem_danh`
--

CREATE TABLE `chi_tiet_diem_danh` (
  `ma_ctdd` int(11) NOT NULL,
  `ma_phien` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `trang_thai_diem_danh` enum('CoMat','VangCoPhep','VangKhongPhep','DiTre') DEFAULT NULL,
  `thoi_gian_nop` datetime DEFAULT NULL COMMENT 'Thời gian HS tự điểm danh',
  `ly_do` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chi_tiet_diem_danh`
--

INSERT INTO `chi_tiet_diem_danh` (`ma_ctdd`, `ma_phien`, `ma_nguoi_dung`, `trang_thai_diem_danh`, `thoi_gian_nop`, `ly_do`) VALUES
(1, 2, 17, 'VangKhongPhep', NULL, NULL),
(2, 2, 2, 'CoMat', NULL, NULL),
(3, 2, 46, 'VangKhongPhep', NULL, NULL),
(4, 2, 47, 'CoMat', NULL, NULL),
(5, 3, 17, 'VangKhongPhep', NULL, NULL),
(6, 3, 2, 'VangKhongPhep', NULL, NULL),
(7, 3, 46, 'VangKhongPhep', NULL, NULL),
(8, 3, 47, 'VangKhongPhep', NULL, NULL),
(9, 8, 2, 'CoMat', '2025-11-15 09:46:49', NULL),
(10, 9, 2, 'CoMat', '2025-11-15 09:49:26', NULL),
(11, 6, 17, 'CoMat', NULL, NULL),
(12, 6, 2, 'CoMat', NULL, NULL),
(13, 6, 46, 'CoMat', NULL, NULL),
(14, 6, 47, 'CoMat', NULL, NULL),
(15, 6, 17, 'CoMat', NULL, NULL),
(16, 6, 2, 'CoMat', NULL, NULL),
(17, 6, 46, 'CoMat', NULL, NULL),
(18, 6, 47, 'CoMat', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `diem_so`
--

CREATE TABLE `diem_so` (
  `ma_diem` int(11) NOT NULL,
  `ma_ket_qua_hoc_tap` int(11) NOT NULL,
  `ma_mon_hoc` int(11) NOT NULL,
  `diem_so` decimal(4,2) NOT NULL,
  `loai_diem` enum('DiemMieng','Diem15Phut','Diem1Tiet','DiemHocKy') NOT NULL,
  `ngay_kiem_tra` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diem_so`
--

INSERT INTO `diem_so` (`ma_diem`, `ma_ket_qua_hoc_tap`, `ma_mon_hoc`, `diem_so`, `loai_diem`, `ngay_kiem_tra`) VALUES
(1, 1, 1, 9.00, 'DiemHocKy', '2025-10-20');

-- --------------------------------------------------------

--
-- Table structure for table `diem_thi_tuyen_sinh`
--

CREATE TABLE `diem_thi_tuyen_sinh` (
  `ma_diem_thi` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `diem_toan` decimal(4,2) DEFAULT NULL,
  `diem_anh` decimal(4,2) DEFAULT NULL,
  `diem_van` decimal(4,2) DEFAULT NULL,
  `nam_tuyen_sinh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diem_thi_tuyen_sinh`
--

INSERT INTO `diem_thi_tuyen_sinh` (`ma_diem_thi`, `ma_nguoi_dung`, `diem_toan`, `diem_anh`, `diem_van`, `nam_tuyen_sinh`) VALUES
(10, 46, 10.00, 8.00, 8.00, 2025),
(12, 47, 8.00, 8.00, 8.00, 2025),
(14, 48, 8.00, 8.00, 8.00, 2025),
(16, 49, 8.00, 8.00, 8.00, 2025);

-- --------------------------------------------------------

--
-- Table structure for table `giao_vien`
--

CREATE TABLE `giao_vien` (
  `ma_giao_vien` int(11) NOT NULL COMMENT 'Kế thừa từ nguoi_dung.ma_nguoi_dung',
  `chuc_vu` varchar(100) DEFAULT 'Giáo viên bộ môn',
  `ngay_vao_truong` date DEFAULT NULL,
  `trinh_do_chuyen_mon` varchar(100) DEFAULT NULL,
  `kinh_nghiem` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `giao_vien`
--

INSERT INTO `giao_vien` (`ma_giao_vien`, `chuc_vu`, `ngay_vao_truong`, `trinh_do_chuyen_mon`, `kinh_nghiem`) VALUES
(3, 'Giáo viên bộ môn', '2020-09-01', 'Thạc sĩ Toán', 0),
(4, 'Giáo viên bộ môn', '2019-08-15', 'Cử nhân Sư phạm Ngữ Văn', 0),
(5, 'Giáo viên bộ môn', '2021-09-01', 'Cử nhân Sư phạm Tiếng Anh', 0),
(6, 'Giáo viên bộ môn', '2018-07-20', 'Cử nhân Sư phạm Lịch Sử', 0),
(7, 'Giáo viên bộ môn', '2022-08-01', 'Cử nhân Giáo dục thể chất', 0),
(8, 'Giáo viên bộ môn', '2017-09-05', 'Cử nhân Giáo dục QP-AN', 0),
(9, 'Giáo viên chủ nhiệm 10A1', '2015-08-20', 'Thạc sĩ Sinh học', 0),
(10, 'Giáo viên bộ môn', '2016-09-01', 'Thạc sĩ Vật Lý', 0),
(11, 'Giáo viên bộ môn', '2019-09-01', 'Cử nhân Sư phạm Hóa học', 0),
(12, 'Giáo viên chủ nhiệm 10A2', '2020-08-10', 'Cử nhân Giáo dục Tiểu học (kiêm nhiệm)', 0),
(13, 'Giáo viên bộ môn', '2021-08-15', 'Cử nhân Công nghệ thông tin', 0),
(14, 'Giáo viên chủ nhiệm 10D1', '2018-08-25', 'Cử nhân Sư phạm (kiêm nhiệm)', 0),
(15, 'Giáo viên bộ môn', '2017-08-30', 'Thạc sĩ Địa lý', 0),
(16, 'Giáo viên bộ môn', '2022-09-01', 'Cử nhân Luật & Kinh tế', 0),
(52, 'Giáo viên bộ môn', '2025-02-05', 'cử nhân', 0),
(54, 'BanGiamHieu', NULL, 'Tiến sĩ Quản lý Giáo dục', 0);

-- --------------------------------------------------------

--
-- Table structure for table `hoa_don`
--

CREATE TABLE `hoa_don` (
  `ma_hoa_don` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_lap_hoa_don` date DEFAULT curdate(),
  `so_luong` int(11) DEFAULT NULL,
  `don_gia` decimal(18,2) DEFAULT NULL,
  `thanh_tien` decimal(18,2) GENERATED ALWAYS AS (`so_luong` * `don_gia`) STORED,
  `hinh_thuc_thanh_toan` enum('TienMat','ChuyenKhoan','TheNganHang','SepayQR') DEFAULT NULL,
  `trang_thai_hoa_don` enum('DaThanhToan','ChuaThanhToan','Huy') DEFAULT 'ChuaThanhToan',
  `ma_giao_dich_ben_thu_3` varchar(100) DEFAULT NULL,
  `ngay_thanh_toan` datetime DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `ngay_het_han` date NOT NULL DEFAULT (current_timestamp() + interval 30 day) COMMENT 'Thời hạn đóng hóa đơn',
  `trang_thai_tam` enum('ChuaThanhToan','ChoThanhToanTaiTruong','DaThanhToan') DEFAULT 'ChuaThanhToan' COMMENT 'Trạng thái tạm cho tiền mặt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoa_don`
--

INSERT INTO `hoa_don` (`ma_hoa_don`, `ma_nguoi_dung`, `ngay_lap_hoa_don`, `so_luong`, `don_gia`, `hinh_thuc_thanh_toan`, `trang_thai_hoa_don`, `ma_giao_dich_ben_thu_3`, `ngay_thanh_toan`, `ghi_chu`, `ngay_het_han`, `trang_thai_tam`) VALUES
(1, 20, '2025-11-13', 1, 5000000.00, '', 'DaThanhToan', 'VNP_1763050335', '2025-11-13 23:12:15', 'Học phí Học kỳ 1 (2025-2026)', '2025-12-13', 'ChuaThanhToan'),
(2, 20, '2025-11-13', 1, 850000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Thu BHYT và các khoản quỹ đầu năm', '2025-12-13', 'ChoThanhToanTaiTruong'),
(3, 20, '2025-11-13', 1, 2500000.00, '', 'DaThanhToan', '15264007', '2025-11-16 09:39:18', 'Học phí Kỳ 1 (2025-2026)', '2025-12-13', NULL),
(4, 20, '2025-11-13', 1, 560000.00, '', 'DaThanhToan', '15264005', '2025-11-16 09:35:24', 'TH BHYT cá nhân quý 4 năm', '2025-12-17', NULL),
(5, 21, '2025-11-16', 1, 2500000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Học phí Học kỳ 1 (2025-2026)', '2025-12-16', 'ChuaThanhToan'),
(6, 21, '2025-11-16', 1, 300000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Tiền quỹ lớp và đồng phục', '2025-12-16', 'ChuaThanhToan'),
(7, 22, '2025-11-16', 1, 2500000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Học phí Học kỳ 1 (2025-2026)', '2025-12-16', 'ChuaThanhToan'),
(8, 20, '2025-11-16', 1, 150000.00, '', 'DaThanhToan', '15265914', '2025-11-17 12:23:57', 'Thu tiền học ngoại khóa tháng 11', '2025-11-30', NULL),
(9, 20, '2025-10-15', 1, 50000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Thu tiền sổ liên lạc điện tử (Quá hạn)', '2025-11-15', 'ChuaThanhToan'),
(10, 20, '2025-11-17', 1, 5000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Phí Test QR - Hoá đơn 1', '2025-11-24', ''),
(11, 20, '2025-11-17', 1, 5000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Phí Test QR - Hoá đơn 2', '2025-11-24', ''),
(12, 20, '2025-11-17', 1, 5000.00, 'SepayQR', 'DaThanhToan', 'FT25321903440800', '2025-11-17 16:50:36', 'Phí Test QR - Hoá đơn 3', '2025-11-24', NULL),
(13, 20, '2025-11-17', 1, 5000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Phí Test QR - Hoá đơn 4', '2025-11-24', 'ChuaThanhToan'),
(14, 20, '2025-11-17', 1, 5000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Phí Test QR - Hoá đơn 5', '2025-11-24', 'ChuaThanhToan'),
(15, 20, '2025-12-02', 1, 5000.00, 'SepayQR', 'DaThanhToan', 'FT25336103026453', '2025-12-02 12:13:33', 'Phí Test QR - Hóa đơn 1 (Mới)', '2026-01-01', NULL),
(16, 20, '2025-12-02', 1, 5000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Phí Test QR - Hóa đơn 2 (Mới)', '2026-01-01', 'ChuaThanhToan'),
(17, 20, '2025-12-02', 1, 5000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Phí Test QR - Hóa đơn 3 (Mới)', '2026-01-01', 'ChuaThanhToan'),
(18, 20, '2025-12-02', 1, 5000.00, NULL, 'ChuaThanhToan', NULL, NULL, 'Phí Test QR - Hóa đơn 4 (Mới)', '2026-01-01', 'ChuaThanhToan'),
(19, 20, '2025-12-02', 1, 200000.00, '', 'DaThanhToan', '15314501', '2025-12-02 12:11:30', 'Học phí bổ sung (Test 200k)', '2026-01-01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hoc_ky`
--

CREATE TABLE `hoc_ky` (
  `ma_hoc_ky` int(11) NOT NULL,
  `ma_nam_hoc` int(11) NOT NULL,
  `ten_hoc_ky` varchar(50) NOT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `trang_thai` enum('DangHoatDong','DaKetThuc') DEFAULT 'DangHoatDong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoc_ky`
--

INSERT INTO `hoc_ky` (`ma_hoc_ky`, `ma_nam_hoc`, `ten_hoc_ky`, `ngay_bat_dau`, `ngay_ket_thuc`, `trang_thai`) VALUES
(1, 1, 'Học Kỳ 1 (2025-2026)', '2025-08-01', '2025-12-31', 'DangHoatDong'),
(2, 1, 'Học Kỳ 2 (2026)', '2026-01-01', '2026-05-31', 'DangHoatDong');

-- --------------------------------------------------------

--
-- Table structure for table `hoc_sinh`
--

CREATE TABLE `hoc_sinh` (
  `ma_hoc_sinh` int(11) NOT NULL COMMENT 'Kế thừa từ nguoi_dung.ma_nguoi_dung',
  `ngay_nhap_hoc` date DEFAULT NULL,
  `trang_thai` enum('DangHoc','NghiHoc','ChuyenTruong') DEFAULT 'DangHoc',
  `ma_lop` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoc_sinh`
--

INSERT INTO `hoc_sinh` (`ma_hoc_sinh`, `ngay_nhap_hoc`, `trang_thai`, `ma_lop`) VALUES
(2, '2025-09-01', 'DangHoc', 1),
(17, '2025-09-01', 'DangHoc', 1),
(19, '2025-09-01', 'DangHoc', 3),
(46, '2025-10-30', 'DangHoc', 1),
(47, '2025-10-30', 'DangHoc', 1),
(53, NULL, 'DangHoc', NULL);

--
-- Triggers `hoc_sinh`
--
DELIMITER $$
CREATE TRIGGER `trg_after_delete_hoc_sinh` AFTER DELETE ON `hoc_sinh` FOR EACH ROW BEGIN
    IF OLD.ma_lop IS NOT NULL THEN
        UPDATE lop_hoc SET si_so = GREATEST(0, si_so - 1) WHERE ma_lop = OLD.ma_lop;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_insert_hoc_sinh` AFTER INSERT ON `hoc_sinh` FOR EACH ROW BEGIN
    IF NEW.ma_lop IS NOT NULL THEN
        UPDATE lop_hoc SET si_so = si_so + 1 WHERE ma_lop = NEW.ma_lop;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_hoc_sinh` AFTER UPDATE ON `hoc_sinh` FOR EACH ROW BEGIN
    -- Chỉ chạy khi mã lớp thay đổi
    IF IFNULL(OLD.ma_lop, 0) <> IFNULL(NEW.ma_lop, 0) THEN
        -- Giảm sĩ số lớp cũ (nếu có)
        IF OLD.ma_lop IS NOT NULL THEN
            UPDATE lop_hoc SET si_so = GREATEST(0, si_so - 1) WHERE ma_lop = OLD.ma_lop;
        END IF;
        -- Tăng sĩ số lớp mới (nếu có)
        IF NEW.ma_lop IS NOT NULL THEN
            UPDATE lop_hoc SET si_so = si_so + 1 WHERE ma_lop = NEW.ma_lop;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ket_qua_hoc_tap`
--

CREATE TABLE `ket_qua_hoc_tap` (
  `ma_ket_qua_hoc_tap` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `diem_tb_mon` decimal(4,2) DEFAULT NULL,
  `diem_tb_hoc_ky` decimal(4,2) DEFAULT NULL,
  `diem_tb_ca_nam` decimal(4,2) DEFAULT NULL,
  `xep_loai_mon_hoc` varchar(50) DEFAULT NULL,
  `xep_loai_hoc_luc` varchar(50) DEFAULT NULL,
  `xep_loai_hanh_kiem` varchar(50) DEFAULT NULL,
  `so_lan_nghi_khong_phep` int(11) DEFAULT 0,
  `so_lan_nghi_co_phep` int(11) DEFAULT 0,
  `vi_pham_noi_quy` varchar(255) DEFAULT NULL,
  `nhan_xet` varchar(255) DEFAULT NULL,
  `trang_thai` enum('HoanThanh','ChuaHoanThanh') DEFAULT 'ChuaHoanThanh'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ket_qua_hoc_tap`
--

INSERT INTO `ket_qua_hoc_tap` (`ma_ket_qua_hoc_tap`, `ma_nguoi_dung`, `diem_tb_mon`, `diem_tb_hoc_ky`, `diem_tb_ca_nam`, `xep_loai_mon_hoc`, `xep_loai_hoc_luc`, `xep_loai_hanh_kiem`, `so_lan_nghi_khong_phep`, `so_lan_nghi_co_phep`, `vi_pham_noi_quy`, `nhan_xet`, `trang_thai`) VALUES
(1, 2, NULL, NULL, NULL, NULL, 'Khá', 'Tốt', 0, 0, NULL, NULL, 'ChuaHoanThanh');

-- --------------------------------------------------------

--
-- Table structure for table `ket_qua_thi_tuyen_sinh`
--

CREATE TABLE `ket_qua_thi_tuyen_sinh` (
  `ma_ket_qua_tuyen_sinh` int(11) NOT NULL,
  `ma_diem_thi` int(11) NOT NULL,
  `tong_diem` decimal(6,2) DEFAULT NULL,
  `trang_thai` enum('Dau','Truot') NOT NULL,
  `ma_nguyen_vong_trung_tuyen` int(11) DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `trang_thai_xac_nhan` enum('Cho_xac_nhan','Xac_nhan_nhap_hoc','Tu_choi_nhap_hoc') NOT NULL DEFAULT 'Cho_xac_nhan',
  `ngay_xac_nhan` timestamp NULL DEFAULT NULL,
  `ma_truong_trung_tuyen` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ket_qua_thi_tuyen_sinh`
--

INSERT INTO `ket_qua_thi_tuyen_sinh` (`ma_ket_qua_tuyen_sinh`, `ma_diem_thi`, `tong_diem`, `trang_thai`, `ma_nguyen_vong_trung_tuyen`, `ghi_chu`, `trang_thai_xac_nhan`, `ngay_xac_nhan`, `ma_truong_trung_tuyen`) VALUES
(17, 10, 26.00, 'Dau', 7, NULL, 'Xac_nhan_nhap_hoc', '2025-10-30 06:00:23', 1),
(18, 12, 24.00, 'Dau', 9, NULL, 'Tu_choi_nhap_hoc', '2025-10-30 06:00:23', 1),
(19, 14, 24.00, 'Dau', 10, NULL, 'Cho_xac_nhan', NULL, 2),
(20, 16, 24.00, 'Dau', 12, NULL, 'Xac_nhan_nhap_hoc', '2025-10-30 06:00:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lop_hoc`
--

CREATE TABLE `lop_hoc` (
  `ma_lop` int(11) NOT NULL,
  `ten_lop` varchar(50) NOT NULL,
  `khoi` int(11) DEFAULT NULL,
  `si_so` int(11) DEFAULT 0,
  `trang_thai_lop` enum('HoatDong','DaXoa') DEFAULT 'HoatDong',
  `ma_truong` int(11) DEFAULT NULL,
  `ma_nam_hoc` int(11) DEFAULT NULL,
  `ma_phong_hoc_chinh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lop_hoc`
--

INSERT INTO `lop_hoc` (`ma_lop`, `ten_lop`, `khoi`, `si_so`, `trang_thai_lop`, `ma_truong`, `ma_nam_hoc`, `ma_phong_hoc_chinh`) VALUES
(1, '10A1', 10, 4, 'HoatDong', 1, 1, 1),
(2, '10A2', 10, 1, 'HoatDong', 1, 1, 2),
(3, '10D1', 10, 1, 'HoatDong', 1, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `mon_hoc`
--

CREATE TABLE `mon_hoc` (
  `ma_mon_hoc` int(11) NOT NULL,
  `ten_mon_hoc` varchar(100) NOT NULL,
  `loai_mon` enum('Bắt buộc','Tự chọn KHTN','Tự chọn KHXH','Tự chọn CN-NT','Hoạt động') DEFAULT NULL,
  `mo_ta_mon_hoc` varchar(255) DEFAULT NULL,
  `trang_thai_mon_hoc` enum('HoatDong','DaXoa') DEFAULT 'HoatDong',
  `yeu_cau_phong_dac_biet` enum('None','LabTin','LabLyHoaSinh','NhaTheChat') DEFAULT 'None' COMMENT 'Loại phòng đặc biệt môn học yêu cầu (nếu có)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mon_hoc`
--

INSERT INTO `mon_hoc` (`ma_mon_hoc`, `ten_mon_hoc`, `loai_mon`, `mo_ta_mon_hoc`, `trang_thai_mon_hoc`, `yeu_cau_phong_dac_biet`) VALUES
(1, 'Toán', 'Bắt buộc', NULL, 'HoatDong', 'None'),
(2, 'Ngữ văn', 'Bắt buộc', NULL, 'HoatDong', 'None'),
(3, 'Ngoại ngữ 1 (Tiếng Anh)', 'Bắt buộc', NULL, 'HoatDong', 'None'),
(4, 'Lịch sử', 'Bắt buộc', NULL, 'HoatDong', 'None'),
(5, 'Giáo dục thể chất', 'Bắt buộc', NULL, 'HoatDong', 'NhaTheChat'),
(6, 'Giáo dục quốc phòng và an ninh', 'Bắt buộc', NULL, 'HoatDong', 'None'),
(7, 'Hoạt động trải nghiệm, hướng nghiệp', 'Bắt buộc', NULL, 'HoatDong', 'None'),
(8, 'Nội dung giáo dục địa phương', 'Bắt buộc', NULL, 'HoatDong', 'None'),
(9, 'Địa lí', 'Tự chọn KHXH', NULL, 'HoatDong', 'None'),
(10, 'Giáo dục kinh tế và pháp luật', 'Tự chọn KHXH', NULL, 'HoatDong', 'None'),
(11, 'Vật lí', 'Tự chọn KHTN', NULL, 'HoatDong', 'LabLyHoaSinh'),
(12, 'Hóa học', 'Tự chọn KHTN', NULL, 'HoatDong', 'LabLyHoaSinh'),
(13, 'Sinh học', 'Tự chọn KHTN', NULL, 'HoatDong', 'LabLyHoaSinh'),
(14, 'Công nghệ (Công nghiệp/Nông nghiệp)', 'Tự chọn CN-NT', NULL, 'HoatDong', 'None'),
(15, 'Tin học', 'Tự chọn CN-NT', NULL, 'HoatDong', 'LabTin'),
(16, 'Âm nhạc', 'Tự chọn CN-NT', NULL, 'HoatDong', 'None'),
(17, 'Mỹ thuật', 'Tự chọn CN-NT', NULL, 'HoatDong', 'None'),
(18, 'Chào cờ', 'Hoạt động', 'Tiết sinh hoạt đầu tuần', 'HoatDong', 'None'),
(19, 'Sinh hoạt lớp', 'Hoạt động', 'Tiết sinh hoạt cuối tuần', 'HoatDong', 'None');

-- --------------------------------------------------------

--
-- Table structure for table `nam_hoc`
--

CREATE TABLE `nam_hoc` (
  `ma_nam_hoc` int(11) NOT NULL,
  `ten_nam_hoc` varchar(50) NOT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `trang_thai` enum('DangHoatDong','DaKetThuc') DEFAULT 'DangHoatDong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nam_hoc`
--

INSERT INTO `nam_hoc` (`ma_nam_hoc`, `ten_nam_hoc`, `ngay_bat_dau`, `ngay_ket_thuc`, `trang_thai`) VALUES
(1, '2025-2026', '2025-09-01', '2026-06-30', 'DangHoatDong');

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_tai_khoan` int(11) DEFAULT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('Nam','Nu','Khac') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`ma_nguoi_dung`, `ma_tai_khoan`, `ho_ten`, `email`, `so_dien_thoai`, `dia_chi`, `ngay_sinh`, `gioi_tinh`) VALUES
(1, 1, 'Trần Thị B (Admin)', 'admin@thpt.edu.vn', '0123456789', '123 Admin St, TP.HCM', '1990-05-15', 'Nu'),
(2, 2, 'Lê Văn A (HS 10A1)', 'hsa@thpt.edu.vn', '0987654321', '456 Student Ave, TP.HCM', '2010-01-20', 'Nam'),
(3, 3, 'Nguyễn Văn C (GV Toán)', 'gvtoan.c@thpt.edu.vn', '0112233445', '789 Teacher Rd, TP.HCM', '1985-11-10', 'Nam'),
(4, 4, 'Trần Thị D (GV Văn)', 'gvvan.d@thpt.edu.vn', '0223344556', NULL, NULL, 'Nu'),
(5, 5, 'Lê Minh E (GV Anh)', 'gvanh.e@thpt.edu.vn', '0334455667', NULL, NULL, 'Nam'),
(6, 6, 'Phạm Thị F (GV Sử)', 'gvsu.f@thpt.edu.vn', '0445566778', NULL, NULL, 'Nu'),
(7, 7, 'Hoàng Văn G (GV TD)', 'gvtd.g@thpt.edu.vn', '0556677889', NULL, NULL, 'Nam'),
(8, 8, 'Vũ Thị H (GV QP)', 'gvqp.h@thpt.edu.vn', '0667788990', NULL, NULL, 'Nu'),
(9, 9, 'Đỗ Thị Nhiệm (CN 10A1)', 'gvcn.a1@thpt.edu.vn', '0778899001', NULL, NULL, 'Nu'),
(10, 10, 'Ngô Văn K (GV Lý)', 'gvly.k@thpt.edu.vn', '0889900112', NULL, NULL, 'Nam'),
(11, 11, 'Bùi Thị L (GV Hóa)', 'gvhoa.l@thpt.edu.vn', '0990011223', NULL, NULL, 'Nu'),
(12, 12, 'Đặng Văn M (CN 10A2)', 'gvcn.a2@thpt.edu.vn', '0121234567', NULL, NULL, 'Nam'),
(13, 13, 'Châu Thị N (GV Tin)', 'gvtin.n@thpt.edu.vn', '0232345678', NULL, NULL, 'Nu'),
(14, 14, 'Giang Văn P (CN 10D1)', 'gvcn.d1@thpt.edu.vn', '0343456789', NULL, NULL, 'Nam'),
(15, 15, 'Huỳnh Thị Q (GV Địa)', 'gvdia.q@thpt.edu.vn', '0454567890', NULL, NULL, 'Nu'),
(16, 16, 'Kim Văn R (GV KTPL)', 'gvktpl.r@thpt.edu.vn', '0565678901', NULL, NULL, 'Nam'),
(17, 17, 'Học Sinh 2 (HS 10A1)', 'hs2@thpt.edu.vn', '0987654322', 'điện ', NULL, 'Nu'),
(19, 19, 'Học Sinh 4 (HS 10D1)', 'hs4@thpt.edu.vn', '0987654324', 'thái an', NULL, 'Nu'),
(20, 20, 'Trần Văn An', 'phuhuynh1@email.com', '0911111111', NULL, NULL, 'Nam'),
(21, 21, 'Lê Thị Bình', 'phuhuynh2@email.com', '0922222222', NULL, NULL, 'Nu'),
(22, 22, 'Phạm Văn Hùng', 'phuhuynh3@email.com', '0933333333', NULL, NULL, 'Nam'),
(46, 46, 'Nguyễn Văn A (TS)', 'nguyenvana@thi.ts', '0811111111', NULL, '2010-05-15', 'Nam'),
(47, 47, 'Trần Văn B (TS)', 'tranvanb@thi.ts', '0822222222', NULL, '2010-03-10', 'Nam'),
(48, 48, 'Lê Thị C (TS)', 'lethic@thi.ts', '0833333333', NULL, '2010-11-02', 'Nu'),
(49, 49, 'Phạm Văn D (TS)', 'phamvand@thi.ts', '0844444444', NULL, '2010-07-20', 'Nam'),
(52, 52, 'thái an', 'ankudo1234@gmail.com', '0914835112', 'điện biên phủ', '2004-02-05', 'Nam'),
(53, 53, 'nguyễn thái an', 'ankudo1234444@gmail.com', '0877132205', 'điện biên', '2004-02-05', 'Nam'),
(54, 54, 'Nguyễn Văn Hiệu Trưởng', 'hieutruong@thpt.edu.vn', '0123456000', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nguyen_vong`
--

CREATE TABLE `nguyen_vong` (
  `ma_nguyen_vong` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_truong` int(11) DEFAULT NULL,
  `thu_tu_nguyen_vong` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguyen_vong`
--

INSERT INTO `nguyen_vong` (`ma_nguyen_vong`, `ma_nguoi_dung`, `ma_truong`, `thu_tu_nguyen_vong`) VALUES
(7, 46, 1, 1),
(8, 46, 2, 2),
(9, 47, 1, 1),
(10, 48, 2, 1),
(11, 48, 1, 2),
(12, 49, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nhan_vien_so_gd`
--

CREATE TABLE `nhan_vien_so_gd` (
  `ma_nv_so` int(11) NOT NULL COMMENT 'Kế thừa từ nguoi_dung.ma_nguoi_dung',
  `phong_ban` varchar(100) DEFAULT NULL,
  `chuc_vu` varchar(100) DEFAULT NULL,
  `quyen_han` varchar(255) DEFAULT NULL,
  `ngay_bat_dau_cong_tac` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phien_diem_danh`
--

CREATE TABLE `phien_diem_danh` (
  `ma_phien` int(11) NOT NULL,
  `tieu_de` varchar(255) DEFAULT NULL,
  `ngay_diem_danh` date DEFAULT curdate(),
  `thoi_gian` time DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `trang_thai_phien` enum('ChuaMo','DangDiemDanh','HetThoiGian') DEFAULT 'ChuaMo',
  `ma_lop_hoc` int(11) DEFAULT NULL,
  `ma_giao_vien` int(11) DEFAULT NULL,
  `loai_phien` enum('GiaoVien','HocSinh') NOT NULL DEFAULT 'GiaoVien' COMMENT 'GiaoVien = GV tự điểm danh; HocSinh = HS tự điểm danh',
  `thoi_gian_mo` datetime DEFAULT NULL COMMENT 'Thời gian bắt đầu cho HS tự điểm danh',
  `thoi_gian_dong` datetime DEFAULT NULL COMMENT 'Thời gian kết thúc cho HS tự điểm danh'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phien_diem_danh`
--

INSERT INTO `phien_diem_danh` (`ma_phien`, `tieu_de`, `ngay_diem_danh`, `thoi_gian`, `ghi_chu`, `trang_thai_phien`, `ma_lop_hoc`, `ma_giao_vien`, `loai_phien`, `thoi_gian_mo`, `thoi_gian_dong`) VALUES
(1, 'Điểm danh Không rõ - 12/11/2025', '2025-11-12', '10:52:18', '', 'HetThoiGian', 1, 3, 'GiaoVien', NULL, NULL),
(2, 'Điểm danh Toán - 10A1 - 13/11/2025', '2025-11-13', '16:04:31', '', 'HetThoiGian', 1, 3, 'GiaoVien', NULL, NULL),
(3, 'Điểm danh Toán - 10A1 - 13/11/2025', '2025-11-13', '16:05:28', '', 'HetThoiGian', 1, 3, 'GiaoVien', NULL, NULL),
(4, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '09:09:58', 'điểm danh nhé mn ơi', 'HetThoiGian', 1, 3, 'GiaoVien', NULL, '2025-11-15 10:05:46'),
(5, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '09:10:22', '', 'HetThoiGian', 1, 3, 'GiaoVien', NULL, '2025-11-15 10:05:42'),
(6, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '09:30:25', '', 'HetThoiGian', 1, 3, 'GiaoVien', NULL, '2025-11-15 10:05:38'),
(7, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '09:30:54', '', 'HetThoiGian', 1, 3, 'HocSinh', '2025-11-15 09:35:00', '2025-11-15 10:05:21'),
(8, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '09:46:27', '', 'HetThoiGian', 1, 3, 'HocSinh', '2025-11-15 09:46:00', '2025-11-15 10:00:00'),
(9, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '09:48:50', '', 'HetThoiGian', 1, 3, 'HocSinh', '2025-11-15 09:48:00', '2025-11-15 09:57:47'),
(10, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '09:58:22', '', 'HetThoiGian', 1, 3, 'HocSinh', '2025-11-15 09:58:00', '2025-11-15 10:00:48'),
(11, 'Điểm danh Toán - 10A1 - 15/11/2025', '2025-11-15', '10:12:43', '', 'HetThoiGian', 1, 3, 'HocSinh', '2025-11-15 10:00:00', '2025-11-15 10:13:25');

-- --------------------------------------------------------

--
-- Table structure for table `phieu_dang_ky_nhap_hoc`
--

CREATE TABLE `phieu_dang_ky_nhap_hoc` (
  `ma_nhap_hoc` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_truong` int(11) NOT NULL,
  `ngay_nhap_hoc` date DEFAULT NULL,
  `bien_lai_so` varchar(20) DEFAULT NULL,
  `bien_lai_ngay` date DEFAULT NULL,
  `bien_lai_so_tien` decimal(18,2) DEFAULT 0.00,
  `tinh_trang_nhap_hoc` enum('DaNhapHoc','ChoXacNhan','Huy') DEFAULT 'ChoXacNhan',
  `ma_lop` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phieu_xin_nghi_hoc`
--

CREATE TABLE `phieu_xin_nghi_hoc` (
  `ma_phieu` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_lam_don` date DEFAULT curdate(),
  `ngay_bat_dau_nghi` date NOT NULL,
  `ngay_ket_thuc_nghi` date NOT NULL,
  `so_ngay_nghi` int(11) GENERATED ALWAYS AS (to_days(`ngay_ket_thuc_nghi`) - to_days(`ngay_bat_dau_nghi`) + 1) STORED,
  `ly_do_nghi` varchar(255) DEFAULT NULL,
  `trang_thai_don` enum('ChoDuyet','DaDuyet','TuChoi') DEFAULT 'ChoDuyet'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phieu_yeu_cau_chinh_sua_diem`
--

CREATE TABLE `phieu_yeu_cau_chinh_sua_diem` (
  `ma_phieu` int(11) NOT NULL,
  `ma_giao_vien` int(11) NOT NULL,
  `ma_diem` int(11) DEFAULT NULL,
  `tieu_de` varchar(100) NOT NULL,
  `ngay_lap_phieu` date DEFAULT curdate(),
  `diem_cu` decimal(4,2) NOT NULL,
  `diem_de_nghi` decimal(4,2) NOT NULL,
  `ly_do_chinh_sua` varchar(255) DEFAULT NULL,
  `trang_thai_phieu` enum('ChoDuyet','DaDuyet','TuChoi') DEFAULT 'ChoDuyet',
  `nguoi_duyet` int(11) DEFAULT NULL,
  `ngay_duyet` date DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieu_yeu_cau_chinh_sua_diem`
--

INSERT INTO `phieu_yeu_cau_chinh_sua_diem` (`ma_phieu`, `ma_giao_vien`, `ma_diem`, `tieu_de`, `ngay_lap_phieu`, `diem_cu`, `diem_de_nghi`, `ly_do_chinh_sua`, `trang_thai_phieu`, `nguoi_duyet`, `ngay_duyet`, `ghi_chu`) VALUES
(10, 3, 1, 'Xin sửa điểm Học kỳ môn Toán - HS Lê Văn A', '2025-11-12', 8.10, 9.00, 'Em nhập nhầm điểm, điểm đúng là 9.0. Xin BGH xem xét.', 'DaDuyet', 54, '2025-11-12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `phong_hoc`
--

CREATE TABLE `phong_hoc` (
  `ma_phong` int(11) NOT NULL,
  `ten_phong` varchar(50) NOT NULL,
  `loai_phong` varchar(50) DEFAULT NULL,
  `suc_chua` int(11) DEFAULT NULL,
  `vi_tri` varchar(100) DEFAULT NULL,
  `thiet_bi` varchar(255) DEFAULT NULL,
  `trang_thai_phong` enum('HoatDong','DangSuaChua','DaXoa') DEFAULT 'HoatDong',
  `ghi_chu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phong_hoc`
--

INSERT INTO `phong_hoc` (`ma_phong`, `ten_phong`, `loai_phong`, `suc_chua`, `vi_tri`, `thiet_bi`, `trang_thai_phong`, `ghi_chu`) VALUES
(1, 'A.101', 'Phòng học thường', 45, 'Dãy A, Tầng 1', 'Bảng, máy chiếu', 'HoatDong', NULL),
(2, 'A.102', 'Phòng học thường', 45, 'Dãy A, Tầng 1', 'Bảng, máy chiếu', 'HoatDong', NULL),
(3, 'A.103', 'Phòng học thường', 45, 'Dãy A, Tầng 1', 'Bảng, máy chiếu', 'HoatDong', NULL),
(4, 'LabTin01', 'Phòng thực hành Tin', 40, 'Dãy B, Tầng 2', 'Máy tính, máy chiếu', 'HoatDong', NULL),
(5, 'LabLyHoaSinh01', 'Phòng thực hành Lý-Hóa-Sinh', 40, 'Dãy B, Tầng 3', 'Dụng cụ thí nghiệm, máy chiếu', 'HoatDong', NULL),
(6, 'NhaTheChat', 'Nhà thi đấu', 100, 'Khu thể chất', 'Dụng cụ thể thao', 'HoatDong', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `phu_huynh`
--

CREATE TABLE `phu_huynh` (
  `ma_phu_huynh` int(11) NOT NULL COMMENT 'Kế thừa từ nguoi_dung.ma_nguoi_dung',
  `nghe_nghiep` varchar(100) DEFAULT NULL,
  `noi_cong_tac` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phu_huynh`
--

INSERT INTO `phu_huynh` (`ma_phu_huynh`, `nghe_nghiep`, `noi_cong_tac`) VALUES
(20, 'Kỹ sư', NULL),
(21, 'Bác sĩ', NULL),
(22, 'Kinh doanh', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quan_tri_vien`
--

CREATE TABLE `quan_tri_vien` (
  `ma_qtv` int(11) NOT NULL COMMENT 'Kế thừa từ nguoi_dung.ma_nguoi_dung',
  `chuc_vu` varchar(100) DEFAULT NULL,
  `trang_thai` enum('HoatDong','TamNgung','DaXoa') DEFAULT 'HoatDong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quan_tri_vien`
--

INSERT INTO `quan_tri_vien` (`ma_qtv`, `chuc_vu`, `trang_thai`) VALUES
(1, 'Admin Hệ Thống', 'HoatDong');

-- --------------------------------------------------------

--
-- Table structure for table `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `ma_tai_khoan` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `vai_tro` enum('GiaoVien','NhanVienSoGD','ThiSinh','QuanTriVien','HocSinh','PhuHuynh','BanGiamHieu') NOT NULL,
  `trang_thai` enum('HoatDong','TamNgung','DaXoa') DEFAULT 'HoatDong',
  `ngay_tao_tai_khoan` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tai_khoan`
--

INSERT INTO `tai_khoan` (`ma_tai_khoan`, `username`, `password`, `vai_tro`, `trang_thai`, `ngay_tao_tai_khoan`) VALUES
(1, 'quantri', '202cb962ac59075b964b07152d234b70', 'QuanTriVien', 'HoatDong', '2025-10-28'),
(2, 'hsa', '202cb962ac59075b964b07152d234b70', 'HocSinh', 'HoatDong', '2025-10-28'),
(3, 'gvtoan_c', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(4, 'gvvan_d', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(5, 'gvanh_e', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(6, 'gvsu_f', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(7, 'gvtd_g', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(8, 'gvqp_h', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(9, 'gvcn_a1', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(10, 'gvly_k', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(11, 'gvhoa_l', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(12, 'gvcn_a2', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(13, 'gvtin_n', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(14, 'gvcn_d1', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(15, 'gvdia_q', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(16, 'gvktpl_r', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-28'),
(17, 'hs2@thpt.edu.vn', '25f9e794323b453885f5181f1b624d0b', 'HocSinh', 'HoatDong', '2025-10-28'),
(19, 'hs4@thpt.edu.vn', '25f9e794323b453885f5181f1b624d0b', 'HocSinh', 'HoatDong', '2025-10-28'),
(20, 'phuhuynh1', '202cb962ac59075b964b07152d234b70', 'PhuHuynh', 'HoatDong', '2025-10-28'),
(21, 'phuhuynh2', '202cb962ac59075b964b07152d234b70', 'PhuHuynh', 'HoatDong', '2025-10-28'),
(22, 'phuhuynh3', '202cb962ac59075b964b07152d234b70', 'PhuHuynh', 'HoatDong', '2025-10-28'),
(46, 'ts_nguyenvana', '202cb962ac59075b964b07152d234b70', 'ThiSinh', 'HoatDong', '2025-10-28'),
(47, 'ts_tranvanb', '202cb962ac59075b964b07152d234b70', 'ThiSinh', 'HoatDong', '2025-10-28'),
(48, 'ts_lethic', '202cb962ac59075b964b07152d234b70', 'ThiSinh', 'HoatDong', '2025-10-28'),
(49, 'ts_phamvand', '202cb962ac59075b964b07152d234b70', 'ThiSinh', 'HoatDong', '2025-10-28'),
(52, 'ankudo1234@gmail.com', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-10-30'),
(53, 'ankudo1234444@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'HocSinh', 'HoatDong', '2025-11-09'),
(54, 'bgh_hieutruong', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-11-09'),
(55, 'gv_toan', '202cb962ac59075b964b07152d234b70', 'GiaoVien', 'HoatDong', '2025-11-09'),
(59, 'hs_10a1_1', '202cb962ac59075b964b07152d234b70', 'HocSinh', 'HoatDong', '2025-11-09');

-- --------------------------------------------------------

--
-- Table structure for table `tai_lieu`
--

CREATE TABLE `tai_lieu` (
  `ma_tai_lieu` int(11) NOT NULL,
  `ten_tai_lieu` varchar(100) NOT NULL,
  `mo_ta` varchar(255) DEFAULT NULL,
  `loai_tai_lieu` enum('GiaoAn','BaiGiang','DeThi','TaiLieuThamKhao','HuongDan') DEFAULT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `ma_mon_hoc` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thi_sinh`
--

CREATE TABLE `thi_sinh` (
  `ma_nguoi_dung` int(11) NOT NULL COMMENT 'Kế thừa từ NGUOI_DUNG',
  `so_bao_danh` varchar(20) DEFAULT NULL,
  `truong_thcs` varchar(100) DEFAULT NULL,
  `lop_hoc` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thi_sinh`
--

INSERT INTO `thi_sinh` (`ma_nguoi_dung`, `so_bao_danh`, `truong_thcs`, `lop_hoc`) VALUES
(46, 'SBD001', 'THCS Lê Lợi', '9A1'),
(47, 'SBD002', 'THCS Quang Trung', '9A2'),
(48, 'SBD003', 'THCS Trần Hưng Đạo', '9B1'),
(49, 'SBD004', 'THCS Lê Lợi', '9A1');

-- --------------------------------------------------------

--
-- Table structure for table `thoi_khoa_bieu`
--

CREATE TABLE `thoi_khoa_bieu` (
  `ma_tkb` int(11) NOT NULL,
  `thu_trong_tuan` enum('Thu2','Thu3','Thu4','Thu5','Thu6','Thu7','ChuNhat') NOT NULL,
  `tuan_ap_dung` int(11) DEFAULT NULL,
  `so_tiet_trong_ngay` int(11) DEFAULT NULL,
  `so_tiet_trong_tuan` int(11) DEFAULT NULL,
  `ngay` date DEFAULT NULL,
  `thang` int(11) DEFAULT NULL,
  `nam` int(11) DEFAULT NULL,
  `ma_lop` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiet_hoc`
--

CREATE TABLE `tiet_hoc` (
  `ma_tiet_hoc` int(11) NOT NULL,
  `ten_tiet_hoc` varchar(50) NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `ma_tkb` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tkb_chi_tiet`
--

CREATE TABLE `tkb_chi_tiet` (
  `ma_tkb_chi_tiet` int(11) NOT NULL,
  `ma_lop` int(11) NOT NULL,
  `ma_hoc_ky` int(11) NOT NULL,
  `ma_phan_cong` int(11) NOT NULL COMMENT 'Liên kết đến bảng phân công (chứa GV, Môn)',
  `thu` tinyint(4) NOT NULL COMMENT '2 = Thứ 2, 7 = Thứ 7',
  `tiet` tinyint(4) NOT NULL COMMENT 'Tiết học trong ngày (1 -> 7 hoặc hơn)',
  `ma_phong_hoc` int(11) DEFAULT NULL COMMENT 'Phòng học thực tế (nếu khác phòng mặc định)',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tkb_chi_tiet`
--

INSERT INTO `tkb_chi_tiet` (`ma_tkb_chi_tiet`, `ma_lop`, `ma_hoc_ky`, `ma_phan_cong`, `thu`, `tiet`, `ma_phong_hoc`, `ngay_tao`) VALUES
(40, 1, 1, 6, 2, 3, 1, '2025-11-17 05:25:51'),
(41, 1, 1, 6, 2, 4, 1, '2025-11-17 05:25:56'),
(42, 1, 1, 5, 3, 1, 1, '2025-11-17 05:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `to_hop_mon`
--

CREATE TABLE `to_hop_mon` (
  `ma_to_hop_mon` int(11) NOT NULL,
  `ten_to_hop` varchar(50) NOT NULL,
  `mo_ta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `to_hop_mon_mon_hoc`
--

CREATE TABLE `to_hop_mon_mon_hoc` (
  `ma_to_hop_mon` int(11) NOT NULL,
  `ma_mon_hoc` int(11) NOT NULL,
  `trang_thai` enum('HoatDong','DaXoa') DEFAULT 'HoatDong',
  `ghi_chu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `truong_thpt`
--

CREATE TABLE `truong_thpt` (
  `ma_truong` int(11) NOT NULL,
  `ten_truong` varchar(100) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `loai_truong` varchar(50) DEFAULT NULL,
  `chi_tieu_hoc_sinh` int(11) DEFAULT NULL,
  `so_luong_hoc_sinh` int(11) DEFAULT 0,
  `ghi_chu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `truong_thpt`
--

INSERT INTO `truong_thpt` (`ma_truong`, `ten_truong`, `dia_chi`, `so_dien_thoai`, `email`, `loai_truong`, `chi_tieu_hoc_sinh`, `so_luong_hoc_sinh`, `ghi_chu`) VALUES
(1, 'THPT A', '123 Đường A, Quận 1, TP.HCM', '0281111222', 'info@thpta.edu.vn', 'Công lập', 100, 3, NULL),
(2, 'THPT B', NULL, NULL, NULL, NULL, 100, 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bai_nop`
--
ALTER TABLE `bai_nop`
  ADD PRIMARY KEY (`ma_bai_nop`),
  ADD UNIQUE KEY `uk_bai_tap_nguoi_dung` (`ma_bai_tap`,`ma_nguoi_dung`),
  ADD KEY `ma_bai_tap` (`ma_bai_tap`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD PRIMARY KEY (`ma_bai_tap`),
  ADD KEY `ma_lop` (`ma_lop`),
  ADD KEY `fk_baitap_giaovien` (`ma_giao_vien`),
  ADD KEY `fk_baitap_monhoc` (`ma_mon_hoc`);

--
-- Indexes for table `bai_tap_trac_nghiem`
--
ALTER TABLE `bai_tap_trac_nghiem`
  ADD PRIMARY KEY (`ma_bai_tap`);

--
-- Indexes for table `bai_tap_tu_luan`
--
ALTER TABLE `bai_tap_tu_luan`
  ADD PRIMARY KEY (`ma_bai_tap`);

--
-- Indexes for table `bai_tap_upload_file`
--
ALTER TABLE `bai_tap_upload_file`
  ADD PRIMARY KEY (`ma_bai_tap`);

--
-- Indexes for table `bai_viet`
--
ALTER TABLE `bai_viet`
  ADD PRIMARY KEY (`ma_bai_viet`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `bang_phan_cong`
--
ALTER TABLE `bang_phan_cong`
  ADD PRIMARY KEY (`ma_phan_cong`),
  ADD UNIQUE KEY `uk_phan_cong_gv_mon_lop` (`ma_giao_vien`,`ma_mon_hoc`,`ma_lop`),
  ADD KEY `ma_mon_hoc` (`ma_mon_hoc`),
  ADD KEY `ma_lop` (`ma_lop`);

--
-- Indexes for table `ban_giam_hieu`
--
ALTER TABLE `ban_giam_hieu`
  ADD PRIMARY KEY (`ma_bgh`);

--
-- Indexes for table `bien_nhan_thanh_toan`
--
ALTER TABLE `bien_nhan_thanh_toan`
  ADD PRIMARY KEY (`ma_bien_nhan`),
  ADD KEY `ma_hoa_don` (`ma_hoa_don`);

--
-- Indexes for table `chi_tiet_diem_danh`
--
ALTER TABLE `chi_tiet_diem_danh`
  ADD PRIMARY KEY (`ma_ctdd`),
  ADD KEY `ma_phien` (`ma_phien`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `ly_do` (`ly_do`);

--
-- Indexes for table `diem_so`
--
ALTER TABLE `diem_so`
  ADD PRIMARY KEY (`ma_diem`),
  ADD KEY `ma_ket_qua_hoc_tap` (`ma_ket_qua_hoc_tap`),
  ADD KEY `ma_mon_hoc` (`ma_mon_hoc`);

--
-- Indexes for table `diem_thi_tuyen_sinh`
--
ALTER TABLE `diem_thi_tuyen_sinh`
  ADD PRIMARY KEY (`ma_diem_thi`),
  ADD UNIQUE KEY `unique_thi_sinh_nam` (`ma_nguoi_dung`,`nam_tuyen_sinh`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `giao_vien`
--
ALTER TABLE `giao_vien`
  ADD PRIMARY KEY (`ma_giao_vien`);

--
-- Indexes for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`ma_hoa_don`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `hoc_ky`
--
ALTER TABLE `hoc_ky`
  ADD PRIMARY KEY (`ma_hoc_ky`),
  ADD KEY `ma_nam_hoc` (`ma_nam_hoc`);

--
-- Indexes for table `hoc_sinh`
--
ALTER TABLE `hoc_sinh`
  ADD PRIMARY KEY (`ma_hoc_sinh`),
  ADD KEY `ma_lop` (`ma_lop`);

--
-- Indexes for table `ket_qua_hoc_tap`
--
ALTER TABLE `ket_qua_hoc_tap`
  ADD PRIMARY KEY (`ma_ket_qua_hoc_tap`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `ket_qua_thi_tuyen_sinh`
--
ALTER TABLE `ket_qua_thi_tuyen_sinh`
  ADD PRIMARY KEY (`ma_ket_qua_tuyen_sinh`),
  ADD KEY `ma_diem_thi` (`ma_diem_thi`),
  ADD KEY `ma_nguyen_vong_trung_tuyen` (`ma_nguyen_vong_trung_tuyen`),
  ADD KEY `idx_truong_xac_nhan` (`ma_truong_trung_tuyen`,`trang_thai_xac_nhan`);

--
-- Indexes for table `lop_hoc`
--
ALTER TABLE `lop_hoc`
  ADD PRIMARY KEY (`ma_lop`),
  ADD KEY `ma_truong` (`ma_truong`),
  ADD KEY `ma_nam_hoc` (`ma_nam_hoc`),
  ADD KEY `fk_lop_phong_chinh` (`ma_phong_hoc_chinh`);

--
-- Indexes for table `mon_hoc`
--
ALTER TABLE `mon_hoc`
  ADD PRIMARY KEY (`ma_mon_hoc`);

--
-- Indexes for table `nam_hoc`
--
ALTER TABLE `nam_hoc`
  ADD PRIMARY KEY (`ma_nam_hoc`);

--
-- Indexes for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`ma_nguoi_dung`),
  ADD UNIQUE KEY `ma_tai_khoan` (`ma_tai_khoan`),
  ADD UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`);

--
-- Indexes for table `nguyen_vong`
--
ALTER TABLE `nguyen_vong`
  ADD PRIMARY KEY (`ma_nguyen_vong`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `ma_truong` (`ma_truong`);

--
-- Indexes for table `nhan_vien_so_gd`
--
ALTER TABLE `nhan_vien_so_gd`
  ADD PRIMARY KEY (`ma_nv_so`);

--
-- Indexes for table `phien_diem_danh`
--
ALTER TABLE `phien_diem_danh`
  ADD PRIMARY KEY (`ma_phien`),
  ADD KEY `ma_lop_hoc` (`ma_lop_hoc`),
  ADD KEY `ma_giao_vien` (`ma_giao_vien`);

--
-- Indexes for table `phieu_dang_ky_nhap_hoc`
--
ALTER TABLE `phieu_dang_ky_nhap_hoc`
  ADD PRIMARY KEY (`ma_nhap_hoc`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `ma_lop` (`ma_lop`),
  ADD KEY `ma_truong` (`ma_truong`);

--
-- Indexes for table `phieu_xin_nghi_hoc`
--
ALTER TABLE `phieu_xin_nghi_hoc`
  ADD PRIMARY KEY (`ma_phieu`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `phieu_yeu_cau_chinh_sua_diem`
--
ALTER TABLE `phieu_yeu_cau_chinh_sua_diem`
  ADD PRIMARY KEY (`ma_phieu`),
  ADD KEY `ma_giao_vien` (`ma_giao_vien`),
  ADD KEY `nguoi_duyet` (`nguoi_duyet`),
  ADD KEY `fk_phieu_ma_diem` (`ma_diem`);

--
-- Indexes for table `phong_hoc`
--
ALTER TABLE `phong_hoc`
  ADD PRIMARY KEY (`ma_phong`);

--
-- Indexes for table `phu_huynh`
--
ALTER TABLE `phu_huynh`
  ADD PRIMARY KEY (`ma_phu_huynh`);

--
-- Indexes for table `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`ma_qtv`);

--
-- Indexes for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`ma_tai_khoan`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tai_lieu`
--
ALTER TABLE `tai_lieu`
  ADD PRIMARY KEY (`ma_tai_lieu`),
  ADD KEY `ma_mon_hoc` (`ma_mon_hoc`);

--
-- Indexes for table `thi_sinh`
--
ALTER TABLE `thi_sinh`
  ADD PRIMARY KEY (`ma_nguoi_dung`),
  ADD UNIQUE KEY `so_bao_danh` (`so_bao_danh`);

--
-- Indexes for table `thoi_khoa_bieu`
--
ALTER TABLE `thoi_khoa_bieu`
  ADD PRIMARY KEY (`ma_tkb`),
  ADD KEY `ma_lop` (`ma_lop`);

--
-- Indexes for table `tiet_hoc`
--
ALTER TABLE `tiet_hoc`
  ADD PRIMARY KEY (`ma_tiet_hoc`),
  ADD KEY `ma_tkb` (`ma_tkb`);

--
-- Indexes for table `tkb_chi_tiet`
--
ALTER TABLE `tkb_chi_tiet`
  ADD PRIMARY KEY (`ma_tkb_chi_tiet`),
  ADD UNIQUE KEY `uk_lop_hoc_ky_thu_tiet` (`ma_lop`,`ma_hoc_ky`,`thu`,`tiet`),
  ADD KEY `ma_phan_cong` (`ma_phan_cong`),
  ADD KEY `ma_phong_hoc` (`ma_phong_hoc`);

--
-- Indexes for table `to_hop_mon`
--
ALTER TABLE `to_hop_mon`
  ADD PRIMARY KEY (`ma_to_hop_mon`);

--
-- Indexes for table `to_hop_mon_mon_hoc`
--
ALTER TABLE `to_hop_mon_mon_hoc`
  ADD PRIMARY KEY (`ma_to_hop_mon`,`ma_mon_hoc`),
  ADD KEY `ma_mon_hoc` (`ma_mon_hoc`);

--
-- Indexes for table `truong_thpt`
--
ALTER TABLE `truong_thpt`
  ADD PRIMARY KEY (`ma_truong`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bai_nop`
--
ALTER TABLE `bai_nop`
  MODIFY `ma_bai_nop` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `bai_tap`
--
ALTER TABLE `bai_tap`
  MODIFY `ma_bai_tap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `bai_viet`
--
ALTER TABLE `bai_viet`
  MODIFY `ma_bai_viet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bang_phan_cong`
--
ALTER TABLE `bang_phan_cong`
  MODIFY `ma_phan_cong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `bien_nhan_thanh_toan`
--
ALTER TABLE `bien_nhan_thanh_toan`
  MODIFY `ma_bien_nhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chi_tiet_diem_danh`
--
ALTER TABLE `chi_tiet_diem_danh`
  MODIFY `ma_ctdd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `diem_so`
--
ALTER TABLE `diem_so`
  MODIFY `ma_diem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `diem_thi_tuyen_sinh`
--
ALTER TABLE `diem_thi_tuyen_sinh`
  MODIFY `ma_diem_thi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `hoa_don`
--
ALTER TABLE `hoa_don`
  MODIFY `ma_hoa_don` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `hoc_ky`
--
ALTER TABLE `hoc_ky`
  MODIFY `ma_hoc_ky` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ket_qua_hoc_tap`
--
ALTER TABLE `ket_qua_hoc_tap`
  MODIFY `ma_ket_qua_hoc_tap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ket_qua_thi_tuyen_sinh`
--
ALTER TABLE `ket_qua_thi_tuyen_sinh`
  MODIFY `ma_ket_qua_tuyen_sinh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `lop_hoc`
--
ALTER TABLE `lop_hoc`
  MODIFY `ma_lop` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mon_hoc`
--
ALTER TABLE `mon_hoc`
  MODIFY `ma_mon_hoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `nam_hoc`
--
ALTER TABLE `nam_hoc`
  MODIFY `ma_nam_hoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `ma_nguoi_dung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `nguyen_vong`
--
ALTER TABLE `nguyen_vong`
  MODIFY `ma_nguyen_vong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `phien_diem_danh`
--
ALTER TABLE `phien_diem_danh`
  MODIFY `ma_phien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `phieu_dang_ky_nhap_hoc`
--
ALTER TABLE `phieu_dang_ky_nhap_hoc`
  MODIFY `ma_nhap_hoc` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phieu_xin_nghi_hoc`
--
ALTER TABLE `phieu_xin_nghi_hoc`
  MODIFY `ma_phieu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phieu_yeu_cau_chinh_sua_diem`
--
ALTER TABLE `phieu_yeu_cau_chinh_sua_diem`
  MODIFY `ma_phieu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `phong_hoc`
--
ALTER TABLE `phong_hoc`
  MODIFY `ma_phong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `ma_tai_khoan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `tai_lieu`
--
ALTER TABLE `tai_lieu`
  MODIFY `ma_tai_lieu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `thoi_khoa_bieu`
--
ALTER TABLE `thoi_khoa_bieu`
  MODIFY `ma_tkb` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiet_hoc`
--
ALTER TABLE `tiet_hoc`
  MODIFY `ma_tiet_hoc` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tkb_chi_tiet`
--
ALTER TABLE `tkb_chi_tiet`
  MODIFY `ma_tkb_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `to_hop_mon`
--
ALTER TABLE `to_hop_mon`
  MODIFY `ma_to_hop_mon` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `truong_thpt`
--
ALTER TABLE `truong_thpt`
  MODIFY `ma_truong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bai_nop`
--
ALTER TABLE `bai_nop`
  ADD CONSTRAINT `bai_nop_ibfk_1` FOREIGN KEY (`ma_bai_tap`) REFERENCES `bai_tap` (`ma_bai_tap`) ON DELETE CASCADE,
  ADD CONSTRAINT `bai_nop_ibfk_2` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `hoc_sinh` (`ma_hoc_sinh`) ON DELETE CASCADE;

--
-- Constraints for table `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD CONSTRAINT `bai_tap_ibfk_1` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc` (`ma_lop`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_baitap_giaovien` FOREIGN KEY (`ma_giao_vien`) REFERENCES `giao_vien` (`ma_giao_vien`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_baitap_monhoc` FOREIGN KEY (`ma_mon_hoc`) REFERENCES `mon_hoc` (`ma_mon_hoc`) ON DELETE SET NULL;

--
-- Constraints for table `bai_tap_trac_nghiem`
--
ALTER TABLE `bai_tap_trac_nghiem`
  ADD CONSTRAINT `bai_tap_trac_nghiem_ibfk_1` FOREIGN KEY (`ma_bai_tap`) REFERENCES `bai_tap` (`ma_bai_tap`) ON DELETE CASCADE;

--
-- Constraints for table `bai_tap_tu_luan`
--
ALTER TABLE `bai_tap_tu_luan`
  ADD CONSTRAINT `bai_tap_tu_luan_ibfk_1` FOREIGN KEY (`ma_bai_tap`) REFERENCES `bai_tap` (`ma_bai_tap`) ON DELETE CASCADE;

--
-- Constraints for table `bai_tap_upload_file`
--
ALTER TABLE `bai_tap_upload_file`
  ADD CONSTRAINT `bai_tap_upload_file_ibfk_1` FOREIGN KEY (`ma_bai_tap`) REFERENCES `bai_tap` (`ma_bai_tap`) ON DELETE CASCADE;

--
-- Constraints for table `bai_viet`
--
ALTER TABLE `bai_viet`
  ADD CONSTRAINT `bai_viet_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nhan_vien_so_gd` (`ma_nv_so`) ON DELETE CASCADE;

--
-- Constraints for table `bang_phan_cong`
--
ALTER TABLE `bang_phan_cong`
  ADD CONSTRAINT `bang_phan_cong_ibfk_2` FOREIGN KEY (`ma_mon_hoc`) REFERENCES `mon_hoc` (`ma_mon_hoc`) ON DELETE CASCADE,
  ADD CONSTRAINT `bang_phan_cong_ibfk_4` FOREIGN KEY (`ma_giao_vien`) REFERENCES `giao_vien` (`ma_giao_vien`) ON DELETE CASCADE,
  ADD CONSTRAINT `bang_phan_cong_ibfk_5` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc` (`ma_lop`) ON DELETE CASCADE;

--
-- Constraints for table `ban_giam_hieu`
--
ALTER TABLE `ban_giam_hieu`
  ADD CONSTRAINT `ban_giam_hieu_ibfk_1` FOREIGN KEY (`ma_bgh`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Constraints for table `bien_nhan_thanh_toan`
--
ALTER TABLE `bien_nhan_thanh_toan`
  ADD CONSTRAINT `bien_nhan_thanh_toan_ibfk_1` FOREIGN KEY (`ma_hoa_don`) REFERENCES `hoa_don` (`ma_hoa_don`) ON DELETE CASCADE;

--
-- Constraints for table `chi_tiet_diem_danh`
--
ALTER TABLE `chi_tiet_diem_danh`
  ADD CONSTRAINT `chi_tiet_diem_danh_ibfk_1` FOREIGN KEY (`ma_phien`) REFERENCES `phien_diem_danh` (`ma_phien`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_diem_danh_ibfk_2` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `hoc_sinh` (`ma_hoc_sinh`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_diem_danh_ibfk_3` FOREIGN KEY (`ly_do`) REFERENCES `phieu_xin_nghi_hoc` (`ma_phieu`) ON DELETE SET NULL;

--
-- Constraints for table `diem_so`
--
ALTER TABLE `diem_so`
  ADD CONSTRAINT `diem_so_ibfk_1` FOREIGN KEY (`ma_ket_qua_hoc_tap`) REFERENCES `ket_qua_hoc_tap` (`ma_ket_qua_hoc_tap`) ON DELETE CASCADE,
  ADD CONSTRAINT `diem_so_ibfk_2` FOREIGN KEY (`ma_mon_hoc`) REFERENCES `mon_hoc` (`ma_mon_hoc`) ON DELETE CASCADE;

--
-- Constraints for table `diem_thi_tuyen_sinh`
--
ALTER TABLE `diem_thi_tuyen_sinh`
  ADD CONSTRAINT `diem_thi_tuyen_sinh_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `thi_sinh` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Constraints for table `giao_vien`
--
ALTER TABLE `giao_vien`
  ADD CONSTRAINT `giao_vien_ibfk_1` FOREIGN KEY (`ma_giao_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Constraints for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD CONSTRAINT `hoa_don_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `phu_huynh` (`ma_phu_huynh`) ON DELETE CASCADE;

--
-- Constraints for table `hoc_ky`
--
ALTER TABLE `hoc_ky`
  ADD CONSTRAINT `hoc_ky_ibfk_1` FOREIGN KEY (`ma_nam_hoc`) REFERENCES `nam_hoc` (`ma_nam_hoc`) ON DELETE CASCADE;

--
-- Constraints for table `hoc_sinh`
--
ALTER TABLE `hoc_sinh`
  ADD CONSTRAINT `hoc_sinh_ibfk_1` FOREIGN KEY (`ma_hoc_sinh`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `hoc_sinh_ibfk_2` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc` (`ma_lop`) ON DELETE SET NULL;

--
-- Constraints for table `ket_qua_hoc_tap`
--
ALTER TABLE `ket_qua_hoc_tap`
  ADD CONSTRAINT `ket_qua_hoc_tap_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `hoc_sinh` (`ma_hoc_sinh`) ON DELETE CASCADE;

--
-- Constraints for table `ket_qua_thi_tuyen_sinh`
--
ALTER TABLE `ket_qua_thi_tuyen_sinh`
  ADD CONSTRAINT `ket_qua_thi_tuyen_sinh_ibfk_1` FOREIGN KEY (`ma_diem_thi`) REFERENCES `diem_thi_tuyen_sinh` (`ma_diem_thi`) ON DELETE CASCADE,
  ADD CONSTRAINT `ket_qua_thi_tuyen_sinh_ibfk_2` FOREIGN KEY (`ma_nguyen_vong_trung_tuyen`) REFERENCES `nguyen_vong` (`ma_nguyen_vong`) ON DELETE SET NULL;

--
-- Constraints for table `lop_hoc`
--
ALTER TABLE `lop_hoc`
  ADD CONSTRAINT `fk_lop_phong_chinh` FOREIGN KEY (`ma_phong_hoc_chinh`) REFERENCES `phong_hoc` (`ma_phong`) ON DELETE SET NULL,
  ADD CONSTRAINT `lop_hoc_ibfk_1` FOREIGN KEY (`ma_truong`) REFERENCES `truong_thpt` (`ma_truong`) ON DELETE CASCADE,
  ADD CONSTRAINT `lop_hoc_ibfk_2` FOREIGN KEY (`ma_nam_hoc`) REFERENCES `nam_hoc` (`ma_nam_hoc`) ON DELETE CASCADE;

--
-- Constraints for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD CONSTRAINT `nguoi_dung_ibfk_1` FOREIGN KEY (`ma_tai_khoan`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE CASCADE;

--
-- Constraints for table `nguyen_vong`
--
ALTER TABLE `nguyen_vong`
  ADD CONSTRAINT `nguyen_vong_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `thi_sinh` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `nguyen_vong_ibfk_2` FOREIGN KEY (`ma_truong`) REFERENCES `truong_thpt` (`ma_truong`) ON DELETE CASCADE;

--
-- Constraints for table `nhan_vien_so_gd`
--
ALTER TABLE `nhan_vien_so_gd`
  ADD CONSTRAINT `nhan_vien_so_gd_ibfk_1` FOREIGN KEY (`ma_nv_so`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Constraints for table `phien_diem_danh`
--
ALTER TABLE `phien_diem_danh`
  ADD CONSTRAINT `phien_diem_danh_ibfk_1` FOREIGN KEY (`ma_lop_hoc`) REFERENCES `lop_hoc` (`ma_lop`) ON DELETE SET NULL,
  ADD CONSTRAINT `phien_diem_danh_ibfk_2` FOREIGN KEY (`ma_giao_vien`) REFERENCES `giao_vien` (`ma_giao_vien`) ON DELETE SET NULL;

--
-- Constraints for table `phieu_dang_ky_nhap_hoc`
--
ALTER TABLE `phieu_dang_ky_nhap_hoc`
  ADD CONSTRAINT `phieu_dang_ky_nhap_hoc_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `thi_sinh` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `phieu_dang_ky_nhap_hoc_ibfk_2` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc` (`ma_lop`) ON DELETE SET NULL,
  ADD CONSTRAINT `phieu_dang_ky_nhap_hoc_ibfk_3` FOREIGN KEY (`ma_truong`) REFERENCES `truong_thpt` (`ma_truong`) ON DELETE CASCADE;

--
-- Constraints for table `phieu_xin_nghi_hoc`
--
ALTER TABLE `phieu_xin_nghi_hoc`
  ADD CONSTRAINT `phieu_xin_nghi_hoc_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `hoc_sinh` (`ma_hoc_sinh`) ON DELETE CASCADE;

--
-- Constraints for table `phieu_yeu_cau_chinh_sua_diem`
--
ALTER TABLE `phieu_yeu_cau_chinh_sua_diem`
  ADD CONSTRAINT `fk_phieu_ma_diem` FOREIGN KEY (`ma_diem`) REFERENCES `diem_so` (`ma_diem`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_phieu_nguoi_duyet_bgh` FOREIGN KEY (`nguoi_duyet`) REFERENCES `ban_giam_hieu` (`ma_bgh`) ON DELETE SET NULL,
  ADD CONSTRAINT `phieu_yeu_cau_chinh_sua_diem_ibfk_1` FOREIGN KEY (`ma_giao_vien`) REFERENCES `giao_vien` (`ma_giao_vien`) ON DELETE CASCADE;

--
-- Constraints for table `phu_huynh`
--
ALTER TABLE `phu_huynh`
  ADD CONSTRAINT `phu_huynh_ibfk_1` FOREIGN KEY (`ma_phu_huynh`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Constraints for table `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD CONSTRAINT `quan_tri_vien_ibfk_1` FOREIGN KEY (`ma_qtv`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Constraints for table `tai_lieu`
--
ALTER TABLE `tai_lieu`
  ADD CONSTRAINT `tai_lieu_ibfk_1` FOREIGN KEY (`ma_mon_hoc`) REFERENCES `mon_hoc` (`ma_mon_hoc`) ON DELETE SET NULL;

--
-- Constraints for table `thi_sinh`
--
ALTER TABLE `thi_sinh`
  ADD CONSTRAINT `thi_sinh_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Constraints for table `thoi_khoa_bieu`
--
ALTER TABLE `thoi_khoa_bieu`
  ADD CONSTRAINT `thoi_khoa_bieu_ibfk_1` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc` (`ma_lop`) ON DELETE CASCADE;

--
-- Constraints for table `tiet_hoc`
--
ALTER TABLE `tiet_hoc`
  ADD CONSTRAINT `tiet_hoc_ibfk_1` FOREIGN KEY (`ma_tkb`) REFERENCES `thoi_khoa_bieu` (`ma_tkb`) ON DELETE CASCADE;

--
-- Constraints for table `tkb_chi_tiet`
--
ALTER TABLE `tkb_chi_tiet`
  ADD CONSTRAINT `tkb_chi_tiet_ibfk_1` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc` (`ma_lop`) ON DELETE CASCADE,
  ADD CONSTRAINT `tkb_chi_tiet_ibfk_2` FOREIGN KEY (`ma_phan_cong`) REFERENCES `bang_phan_cong` (`ma_phan_cong`) ON DELETE CASCADE,
  ADD CONSTRAINT `tkb_chi_tiet_ibfk_3` FOREIGN KEY (`ma_phong_hoc`) REFERENCES `phong_hoc` (`ma_phong`) ON DELETE SET NULL;

--
-- Constraints for table `to_hop_mon_mon_hoc`
--
ALTER TABLE `to_hop_mon_mon_hoc`
  ADD CONSTRAINT `to_hop_mon_mon_hoc_ibfk_1` FOREIGN KEY (`ma_to_hop_mon`) REFERENCES `to_hop_mon` (`ma_to_hop_mon`) ON DELETE CASCADE,
  ADD CONSTRAINT `to_hop_mon_mon_hoc_ibfk_2` FOREIGN KEY (`ma_mon_hoc`) REFERENCES `mon_hoc` (`ma_mon_hoc`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
