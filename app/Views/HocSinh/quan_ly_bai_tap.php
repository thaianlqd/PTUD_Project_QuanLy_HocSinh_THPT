<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Tập - Học Sinh | THPT Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .content-section { display: none; }
        .content-section.active { display: block; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-overlay { z-index: 1000; background-color: rgba(0, 0, 0, 0.75); }
        .header-bg { background-color: #4f46e5; color: white; }
        .tab-button.active { background-color: #4f46e5; color: white; font-weight: 600; }
        .tab-button { transition: all 0.2s ease; }
        input[type="radio"]:checked + span { font-weight: bold; color: #4f46e5; }
        label:hover { background-color: #f0f0ff; }
        .spinner-border { width: 1.5rem; height: 1.5rem; border-width: .2em; }
        .submission-content pre {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 1rem;
            border-radius: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: inherit;
            font-size: 0.9rem;
        }
        .mcq-answer-review {
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 0.5rem;
        }
        /* Style cho đồng hồ đếm ngược */
        #timerBar {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 100;
        }
        #countdownTimer.warning {
            color: #dc2626; /* text-red-600 */
            background-color: #fee2e2; /* bg-red-100 */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-4 md:p-6">
        <header class="mb-8 p-4 rounded-lg shadow-lg header-bg">
            <h1 class="text-2xl md:text-3xl font-bold">Quản Lý Bài Tập</h1>
            <p class="text-indigo-200 text-sm md:text-base">Chào mừng, <?php echo htmlspecialchars($data['user_name'] ?? 'Học Sinh'); ?>! Xem và hoàn thành bài tập.</p>
             <a href="<?php echo BASE_URL ?? ''; ?>/dashboard" class="text-indigo-200 hover:text-white text-sm mt-2 inline-block">&larr; Quay lại Dashboard</a>
        </header>

        <!-- Section Danh sách -->
        <div id="listSection" class="content-section active bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="p-4 md:p-6">
                <h2 class="text-xl md:text-2xl font-semibold text-gray-800 mb-6">Danh Sách Bài Tập Của Bạn</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-indigo-50 text-indigo-800 uppercase text-xs md:text-sm leading-normal">
                                <th class="py-3 px-4 md:px-6 text-left">Mã</th>
                                <th class="py-3 px-4 md:px-6 text-left">Tên Bài Tập</th>
                                <th class="py-3 px-4 md:px-6 text-left hidden md:table-cell">Môn Học</th>
                                <th class="py-3 px-4 md:px-6 text-left">Loại</th>
                                <th class="py-3 px-4 md:px-6 text-left">Hạn Nộp</th>
                                <th class="py-3 px-4 md:px-6 text-left">Trạng Thái</th>
                                <th class="py-3 px-4 md:px-6 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="assignmentTableBody" class="text-gray-700 text-sm divide-y divide-gray-100">
                             <?php if (empty($data['assignments'])): ?>
                                 <tr><td colspan="7" class="text-center py-10 text-gray-500">Chưa có bài tập nào được giao.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Chi Tiết Bài Tập (Để bắt đầu làm) -->
        <div id="assignmentModal" class="modal-overlay fixed inset-0 hidden flex items-center justify-center p-4">
             <div class="bg-white rounded-xl p-6 md:p-8 w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Chi Tiết Bài Tập</h2>
                    <button class="text-gray-500 hover:text-gray-700 text-2xl" onclick="closeModal('assignmentModal')">&times;</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm text-gray-600 mb-6">
                    <p><strong>Mã:</strong> <span id="modalAssignmentId"></span></p>
                    <p><strong>Môn Học:</strong> <span id="modalSubject"></span></p>
                    <p class="md:col-span-2"><strong>Tên Bài Tập:</strong> <span id="modalAssignmentName" class="font-semibold text-gray-800"></span></p>
                    <p><strong>Loại Bài:</strong> <span id="modalAssignmentTypeDisplay" class="font-semibold text-indigo-600"></span></p>
                     <p><strong>Hạn Nộp:</strong> <span id="modalDueDate" class="text-red-500 font-semibold"></span></p>
                    <p><strong>Ngày Giao:</strong> <span id="modalAssignedDate"></span></p>
                    <p><strong>Trạng Thái:</strong> <span id="modalStatus" class="font-semibold"></span></p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-lg mb-4 max-h-48 overflow-y-auto border border-indigo-200">
                    <p class="font-bold text-indigo-700 mb-2 text-sm">Yêu Cầu Bài Tập:</p>
                    <pre id="modalContent" class="text-gray-700 whitespace-pre-wrap text-sm font-sans"></pre>
                </div>
                <p class="mb-6 text-sm">
                    <strong>Tài Liệu Đính Kèm:</strong>
                    <a id="modalAttachment" href="#" target="_blank" class="text-blue-500 hover:underline ml-2"></a>
                </p>
                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition duration-150" onclick="closeModal('assignmentModal')">Đóng</button>
                    <button id="startAssignmentBtn" class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 font-semibold hidden transition duration-150"></button>
                </div>
            </div>
        </div>
        
        <!-- MODAL: XEM LẠI BÀI ĐÃ NỘP (Giữ nguyên) -->
        <div id="submissionDetailModal" class="modal-overlay fixed inset-0 hidden flex items-center justify-center p-4">
             <div class="bg-white rounded-xl p-6 md:p-8 w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Chi Tiết Bài Đã Nộp</h2>
                    <button class="text-gray-500 hover:text-gray-700 text-2xl" onclick="closeModal('submissionDetailModal')">&times;</button>
                </div>
                
                <!-- Loading state -->
                 <div id="submissionModalLoading" class="text-center p-6 text-gray-600">
                    <div class="spinner-border text-primary inline-block mr-2" role="status"></div>
                    Đang tải chi tiết bài nộp...
                 </div>
                 
                 <!-- Content (hiện sau khi load) -->
                 <div id="submissionModalContent" style="display: none;">
                    <h3 id="sub_modal_title" class="text-lg font-semibold text-indigo-700 mb-4"></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm text-gray-600 mb-6">
                         <p><strong>Trạng Thái:</strong> <span id="sub_modal_status" class="font-semibold"></span></p>
                         <p><strong>Ngày Nộp:</strong> <span id="sub_modal_ngay_nop" class="font-semibold"></span></p>
                         <p><strong>Điểm Số:</strong> <span id="sub_modal_diem_so" class="font-bold text-lg text-green-600"></span></p>
                    </div>

                    <!-- Khu vực hiển thị nội dung bài nộp -->
                    <div id="sub_modal_content_wrapper" class="submission-content mb-6">
                        
                        <!-- Dùng cho Upload File -->
                        <div id="sub_modal_file_wrapper" style="display: none;">
                            <p class="font-bold text-gray-700 mb-2">File bạn đã nộp:</p>
                            <a id="sub_modal_file_link" href="#" target="_blank" class="inline-flex items-center text-blue-600 hover:underline bg-blue-50 p-3 rounded-lg border border-blue-200">
                                <i class="bi bi-file-earmark-arrow-down-fill mr-2"></i>
                                <span id="sub_modal_file_name"></span>
                            </a>
                        </div>
                        
                        <!-- Dùng cho Tự luận (Gõ) -->
                        <div id="sub_modal_essay_wrapper" style="display: none;">
                             <p class="font-bold text-gray-700 mb-2">Nội dung bạn đã gõ:</p>
                             <pre id="sub_modal_essay_text"></pre>
                        </div>
                        
                        <!-- Dùng cho Trắc nghiệm -->
                         <div id="sub_modal_mcq_wrapper" style="display: none;">
                              <p class="font-bold text-gray-700 mb-2">Đáp án bạn đã chọn:</p>
                              <div id="sub_modal_mcq_answers" class="max-h-48 overflow-y-auto bg-gray-50 p-3 rounded-lg border">
                                  <!-- JS sẽ điền đáp án vào đây -->
                              </div>
                         </div>
                    </div>
                     <!-- Thông báo lỗi cho Modal Hủy -->
                      <div id="deleteSubmissionNotification" class="mt-4 text-sm font-semibold"></div>
                 </div>
                 
                 <!-- Footer -->
                <div class="flex justify-between items-center space-x-3 border-t pt-4">
                     <button id="deleteSubmissionBtn" data-id="" class="bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 font-semibold transition duration-150" style="display: none;">
                         <i class="bi bi-trash mr-1"></i> Hủy Bài Nộp
                     </button>
                    <button class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition duration-150" onclick="closeModal('submissionDetailModal')">Đóng</button>
                </div>
            </div>
        </div>


         <!-- Section Làm Bài Trắc Nghiệm (ĐÃ NÂNG CẤP) -->
        <div id="multipleChoiceSection" class="content-section max-w-4xl mx-auto bg-white shadow-xl rounded-lg p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-lg md:text-2xl font-bold text-indigo-700">Làm Bài Trắc Nghiệm: <span id="mcqAssignmentName"></span></h2>
                <button class="text-xs md:text-sm bg-gray-300 text-gray-800 py-1 px-3 rounded-full hover:bg-gray-400 transition duration-150" onclick="switchSection('listSection')">
                    <span class="font-bold">&larr; Quay lại</span>
                </button>
            </div>
            
            <!-- === BẮT ĐẦU TIMER BAR MỚI === -->
            <div id="timerBar" class="p-4 mb-4 rounded-lg border shadow-sm" style="display: none;">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <span class="text-lg font-bold text-indigo-600 mb-2 sm:mb-0">Thời gian còn lại:</span>
                    <span id="countdownTimer" class="text-2xl font-bold text-gray-800 bg-gray-100 px-4 py-2 rounded-lg transition-colors duration-300">--:--</span>
                </div>
            </div>
            <!-- === KẾT THÚC TIMER BAR MỚI === -->

            <div id="mcqInstructions" class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg text-sm"></div>
            <form id="mcqForm">
                <div id="mcqLoading" class="text-center p-6 text-gray-600"><div class="spinner-border text-primary inline-block mr-2" role="status"></div>Đang tải câu hỏi...</div>
                <div id="questionsContainer" class="space-y-6" style="display: none;"></div>
                <div id="mcqNotification" class="mt-6 text-sm font-semibold"></div>
                <button type="submit" id="submitMultipleChoice" class="mt-8 bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 font-semibold w-full transition duration-150" disabled>Nộp Bài Trắc Nghiệm</button>
            </form>
        </div>

        <!-- Section Nộp Bài Tự Luận / Upload (Giữ nguyên) -->
        <div id="essaySection" class="content-section max-w-4xl mx-auto bg-white shadow-xl rounded-lg p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-lg md:text-2xl font-bold text-indigo-700">Nộp Bài <span id="essayAssignmentTypeDisplay"></span>: <span id="essayAssignmentName"></span></h2>
                 <button class="text-xs md:text-sm bg-gray-300 text-gray-800 py-1 px-3 rounded-full hover:bg-gray-400 transition duration-150" onclick="switchSection('listSection')">
                    <span class="font-bold">&larr; Quay lại</span>
                 </button>
            </div>
            <div id="essayInstructions" class="mb-6 p-4 bg-blue-100 text-blue-800 rounded-lg text-sm"></div>
            <div class="flex border-b border-gray-200 mb-6">
                <button id="tabTypingBtn" class="tab-button active py-2 px-4 rounded-t-lg text-sm bg-indigo-600 text-white"><i class="bi bi-keyboard mr-1"></i>Gõ Trực Tiếp</button>
                <button id="tabUploadBtn" class="tab-button py-2 px-4 rounded-t-lg text-sm text-gray-600 hover:bg-gray-100"><i class="bi bi-upload mr-1"></i>Upload File</button>
            </div>
            <div id="typingTabContent" class="tab-content">
                <form id="typingForm" class="space-y-4">
                    <label for="essayAnswerInput" class="text-lg font-semibold text-gray-700 block mb-2">Nội dung bài làm:</label>
                    <textarea id="essayAnswerInput" rows="10" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" placeholder="Nhập nội dung bài làm tại đây..."></textarea>
                    <div id="typingNotification" class="mt-4 text-sm font-semibold"></div>
                    <button type="submit" id="submitTyping" class="mt-4 bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 font-semibold w-full transition duration-150">Xác Nhận Nộp Bài (Gõ)</button>
                </form>
            </div>
            <div id="uploadTabContent" class="tab-content" style="display: none;">
                <form id="uploadForm" class="space-y-4">
                    <label for="uploadFile" class="font-semibold text-gray-700 mb-3 block">Tải lên file bài làm (PDF hoặc DOCX, tối đa 5MB):</label>
                    <input type="file" id="uploadFile" name="file_bai_lam" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer border border-gray-300 rounded-lg p-2 shadow-sm"/>
                    <div id="uploadNotification" class="mt-4 text-sm font-semibold"></div>
                    <button type="submit" id="submitUpload" class="mt-4 bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 font-semibold w-full transition duration-150">Xác Nhận Nộp Bài (Upload)</button>
                </form>
            </div>
        </div>

    </div>

<script>
    // --- Lấy dữ liệu bài tập từ PHP ---
    const assignmentsData = <?php echo json_encode($data['assignments'] ?? []); ?>;
    const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";

    // --- Biến Trạng Thái Chung ---
    let currentAssignment = null;
    let countdownInterval = null; // Biến cho đồng hồ đếm ngược

    // --- Helper Functions ---
    function switchSection(targetId) {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
            console.log("Timer stopped due to section switch.");
        }
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
            section.classList.remove('active');
        });
        const target = document.getElementById(targetId);
        if (target) {
            target.style.display = 'block';
            void target.offsetWidth; // Force reflow
            target.classList.add('active');
            window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
        }
    }
    function getAssignmentTypeDisplay(type) {
         switch(type) {
             case 'TracNghiem': return 'Trắc Nghiệm';
             case 'TuLuan': return 'Tự Luận (Gõ)';
             case 'UploadFile': return 'Upload File';
             default: return type;
         }
    }
    function getStatusBadge(status) {
        let colorClasses = 'bg-gray-100 text-gray-800'; // Default
        if (!status) return `<span class="${colorClasses} py-1 px-3 rounded-full text-xs font-semibold whitespace-nowrap">Lỗi</span>`;
        if (status.startsWith('Chưa Làm')) colorClasses = 'bg-blue-100 text-blue-800';
        else if (status.startsWith('Đã Nộp')) colorClasses = 'bg-green-100 text-green-800';
        else if (status.startsWith('Chờ Chấm')) colorClasses = 'bg-purple-100 text-purple-800';
        else if (status.startsWith('Hoàn Thành')) colorClasses = 'bg-teal-100 text-teal-800';
        else if (status.startsWith('Quá Hạn')) colorClasses = 'bg-orange-100 text-orange-800';
        if (status.endsWith('(Trễ)')) colorClasses = 'bg-red-100 text-red-800';
        return `<span class="${colorClasses} py-1 px-3 rounded-full text-xs font-semibold whitespace-nowrap">${status}</span>`;
    }
    
    function safeParseDate(dateString) {
         if (!dateString) return null;
         const safeDateStr = (dateString || '').replace(' ', 'T');
         const dateObj = new Date(safeDateStr);
         if (isNaN(dateObj.getTime())) return null;
         return dateObj;
    }

    function showNotification(message, type = 'success', containerId = 'typingNotification', duration = 5000) {
        const notification = document.getElementById(containerId);
        if (!notification) return;
        let bgColor = 'bg-green-100'; let textColor = 'text-green-800';
        if (type === 'error') { bgColor = 'bg-red-100'; textColor = 'text-red-800'; }
        else if (type === 'info') { bgColor = 'bg-blue-100'; textColor = 'text-blue-800'; }
        notification.innerHTML = `<div class="p-3 rounded-lg ${bgColor} ${textColor} text-sm font-medium shadow">${message}</div>`;
        if (duration > 0) {
             setTimeout(() => { if (notification) notification.innerHTML = ''; }, duration);
        }
    }
    
    function updateAssignmentStatus(id, newStatus) {
        const assignmentIndex = assignmentsData.findIndex(a => a.ma_bai_tap == id); 
        if (assignmentIndex > -1) {
            assignmentsData[assignmentIndex].trang_thai_final = newStatus; 
            renderAssignmentList(); // Cập nhật lại danh sách
        }
    }
    
    function setupEssayTabs() {
        const typingBtn = document.getElementById('tabTypingBtn');
        const uploadBtn = document.getElementById('tabUploadBtn');
        const typingContent = document.getElementById('typingTabContent');
        const uploadContent = document.getElementById('uploadTabContent');
         if (!typingBtn || !uploadBtn || !typingContent || !uploadContent) return;

         const assignmentType = currentAssignment?.loai_bai_tap;
         
         typingBtn.style.display = (assignmentType === 'TuLuan') ? 'inline-block' : 'none';
         uploadBtn.style.display = (assignmentType === 'UploadFile') ? 'inline-block' : 'none';
         
        const switchTab = (activeBtn, inactiveBtn, activeContent, inactiveContent) => {
             activeBtn.classList.add('active', 'bg-indigo-600', 'text-white');
             activeBtn.classList.remove('text-gray-600', 'hover:bg-gray-100');
             inactiveBtn.classList.remove('active', 'bg-indigo-600', 'text-white');
             inactiveBtn.classList.add('text-gray-600', 'hover:bg-gray-100');
             activeContent.style.display = 'block';
             inactiveContent.style.display = 'none';
        };
         typingBtn.addEventListener('click', (e) => { e.preventDefault(); switchTab(typingBtn, uploadBtn, typingContent, uploadContent); });
         uploadBtn.addEventListener('click', (e) => { e.preventDefault(); switchTab(uploadBtn, typingBtn, uploadContent, typingContent); });

          if (assignmentType === 'UploadFile') { uploadBtn.click(); }
          else { typingBtn.click(); }
    }
    
    function escapeHtml(unsafe) {
         if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }


    // --- Core Functions ---

    // 1. Render Danh Sách Bài Tập
    function renderAssignmentList() {
        const tableBody = document.getElementById('assignmentTableBody');
        if (!tableBody) return;
        tableBody.innerHTML = ''; // Clear

        if (!Array.isArray(assignmentsData) || assignmentsData.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-gray-500">Chưa có bài tập nào được giao.</td></tr>';
            return;
        }

        assignmentsData.forEach(assignment => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-indigo-50 transition duration-150 ease-in-out';
            
            const dueDateObj = safeParseDate(assignment.han_nop);
            const isValidDate = dueDateObj !== null;
            const isOverdue = isValidDate && (new Date() > dueDateObj);
            const status = String(assignment.trang_thai_final); 

            const formatDate = (dateObj) => {
                if (!isValidDate) return 'N/A';
                return dateObj.toLocaleString('vi-VN', { 
                    hour: '2-digit', minute: '2-digit', 
                    day: '2-digit', month: '2-digit', year: 'numeric' 
                });
            };
            const dueDateDisplay = formatDate(dueDateObj);
            
            let actionButtonHtml = '';
            if (status.startsWith('Hoàn Thành') || status.startsWith('Đã Nộp') || status.startsWith('Chờ Chấm')) {
                actionButtonHtml = `<button class="bg-blue-500 text-white py-1 px-3 rounded-lg hover:bg-blue-600 text-xs action-button" data-id="${assignment.ma_bai_tap}" data-action="xem-lai">Xem Lại</button>`;
            } else if (status.startsWith('Quá Hạn') || isOverdue) {
                actionButtonHtml = `<button class="bg-gray-400 text-white py-1 px-3 rounded-lg text-xs" disabled>Đã quá hạn</button>`;
            } else if (status.startsWith('Chưa Làm')) {
                actionButtonHtml = `<button class="bg-indigo-500 text-white py-1 px-3 rounded-lg hover:bg-indigo-600 text-xs action-button" data-id="${assignment.ma_bai_tap}" data-action="lam-bai">Làm Bài</button>`;
            } else {
                 actionButtonHtml = `<button class="bg-gray-400 text-white py-1 px-3 rounded-lg text-xs" disabled>Trạng thái lỗi</button>`;
            }
            
            row.innerHTML = `
                <td class="py-3 px-4 md:px-6">${assignment.ma_bai_tap}</td>
                <td class="py-3 px-4 md:px-6 font-medium text-gray-800">${escapeHtml(assignment.ten_bai_tap)}</td>
                <td class="py-3 px-4 md:px-6 text-gray-600 hidden md:table-cell">${escapeHtml(assignment.ten_mon_hoc || 'N/A')}</td>
                <td class="py-3 px-4 md:px-6 font-medium text-indigo-700">${getAssignmentTypeDisplay(assignment.loai_bai_tap)}</td>
                <td class="py-3 px-4 md:px-6 ${isOverdue && !status.startsWith('Đã') && !status.startsWith('Hoàn') ? 'text-red-600 font-bold' : ''}">${dueDateDisplay}</td>
                <td class="py-3 px-4 md:px-6">${getStatusBadge(status)}</td>
                <td class="py-3 px-4 md:px-6 text-center">${actionButtonHtml}</td>
            `;
            tableBody.appendChild(row);
        });

        // Gắn listener vào .action-button
        document.querySelectorAll('.action-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.currentTarget.dataset.id;
                const action = e.currentTarget.dataset.action;
                
                const assignment = assignmentsData.find(a => a.ma_bai_tap == id);
                
                if (assignment) { 
                    if (action === 'lam-bai') {
                        openModal(assignment);
                    } else if (action === 'xem-lai') {
                        openSubmissionDetailModal(assignment);
                    }
                }
                else { console.error("Assignment not found:", id); alert("Lỗi: Không tìm thấy bài tập!");}
            });
        });
    }

    // 2. Mở Modal Chi Tiết (SỬA: Xử lý onclick cho TracNghiem với batDauLamBai)
    function openModal(assignment) {
        currentAssignment = assignment;
        document.getElementById('modalAssignmentId').textContent = assignment.ma_bai_tap;
        document.getElementById('modalAssignmentName').textContent = escapeHtml(assignment.ten_bai_tap);
        document.getElementById('modalSubject').textContent = escapeHtml(assignment.ten_mon_hoc || 'N/A');
        document.getElementById('modalAssignmentTypeDisplay').textContent = getAssignmentTypeDisplay(assignment.loai_bai_tap);
        
        const assignedDateObj = safeParseDate(assignment.ngay_giao);
        document.getElementById('modalAssignedDate').textContent = assignedDateObj ? assignedDateObj.toLocaleDateString('vi-VN') : 'N/A';
        
        const dueDateObj = safeParseDate(assignment.han_nop);
        const dueDateDisplay = dueDateObj ? dueDateObj.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) : 'N/A';
        document.getElementById('modalDueDate').textContent = dueDateDisplay;
        
        document.getElementById('modalStatus').innerHTML = getStatusBadge(assignment.trang_thai_final);
        document.getElementById('modalContent').innerHTML = assignment.mo_ta ? escapeHtml(assignment.mo_ta).replace(/\n/g, '<br>') : '<i class="text-gray-500">Không có yêu cầu chi tiết.</i>';

        const attachmentLink = document.getElementById('modalAttachment');
        if (assignment.file_dinh_kem) {
            attachmentLink.href = `${BASE_URL}/public/${assignment.file_dinh_kem}`;
            attachmentLink.textContent = assignment.file_dinh_kem.split('/').pop() || 'Tải xuống';
            attachmentLink.style.display = 'inline';
            attachmentLink.target = '_blank';
        } else {
            attachmentLink.textContent = 'Không có';
            attachmentLink.style.display = 'inline';
            attachmentLink.href = 'javascript:void(0);';
            attachmentLink.target = '';
        }

        const startBtn = document.getElementById('startAssignmentBtn');
        const isOverdue = dueDateObj && (new Date() > dueDateObj);
        const status = String(assignment.trang_thai_final);
        const canStart = status.startsWith('Chưa Làm');

        startBtn.classList.add('hidden');
        startBtn.onclick = null;
        startBtn.disabled = false;
        startBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'bg-indigo-600', 'hover:bg-indigo-700');

        if (isOverdue && (status.startsWith('Chưa Làm') || status.startsWith('Quá Hạn'))) {
            startBtn.textContent = 'Đã quá hạn';
            startBtn.classList.remove('hidden');
            startBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            startBtn.disabled = true;
        } else if (canStart) {
            startBtn.textContent = 'Bắt Đầu Làm Bài';
            startBtn.classList.remove('hidden');
            startBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            startBtn.onclick = async () => {
                closeModal('assignmentModal'); 
                if (assignment.loai_bai_tap === 'TracNghiem') {
                    // SỬA: Gọi API batDauLamBai trước
                    try {
                        const startResponse = await fetch(`${BASE_URL}/baitap/batDauLamBai/${assignment.ma_bai_tap}`);
                        const startResult = await startResponse.json();
                        if (!startResult.success) {
                            throw new Error(startResult.message);
                        }
                        // Reload assignment với gio_bat_dau mới
                        const detailResponse = await fetch(`${BASE_URL}/baitap/getChiTiet/${assignment.ma_bai_tap}`);
                        const detailResult = await detailResponse.json();
                        if (detailResult.success) {
                            currentAssignment = detailResult.assignment;
                        }
                        loadMultipleChoiceSection(currentAssignment);
                    } catch (error) {
                        alert(`Lỗi khởi động: ${error.message}`);
                        return;
                    }
                } else if (assignment.loai_bai_tap === 'TuLuan' || assignment.loai_bai_tap === 'UploadFile') {
                    loadEssaySection(assignment);
                }
            };
        }
        
        document.getElementById('assignmentModal').classList.remove('hidden');
        document.getElementById('assignmentModal').classList.add('flex');
    }

    // Đóng Modal
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
            console.log("Timer stopped.");
        }
    }

    // MỞ MODAL XEM LẠI BÀI NỘP (SỬA: Check type trước parse JSON)
    async function openSubmissionDetailModal(assignment) {
        currentAssignment = assignment; 
        const modal = document.getElementById('submissionDetailModal');
        const loadingDiv = document.getElementById('submissionModalLoading');
        const contentDiv = document.getElementById('submissionModalContent');
        const deleteBtn = document.getElementById('deleteSubmissionBtn');

        // Reset modal
        loadingDiv.style.display = 'block';
        loadingDiv.innerHTML = '<div class="spinner-border text-primary inline-block mr-2" role="status"></div> Đang tải chi tiết bài nộp...';
        contentDiv.style.display = 'none';
        deleteBtn.style.display = 'none';
        deleteBtn.dataset.id = '';
        document.getElementById('deleteSubmissionNotification').innerHTML = '';
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = '<i class="bi bi-trash mr-1"></i> Hủy Bài Nộp';

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        try {
            const response = await fetch(`${BASE_URL}/baitap/getBaiNopChiTiet/${assignment.ma_bai_tap}`, {
                method: 'GET', // (Nên ghi rõ, dù GET là mặc định)
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
        });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Lỗi mạng (${response.status}): ${errorText.substring(0, 100)}`);
            }
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Không thể tải chi tiết bài nộp.');
            }

            const submission = result.submission; 
            const assignmentDetails = result.assignment || currentAssignment; 
            currentAssignment = assignmentDetails; 

            if (!submission) {
                throw new Error('Không tìm thấy dữ liệu bài nộp.');
            }

            // Điền thông tin vào modal
            document.getElementById('sub_modal_title').textContent = escapeHtml(assignmentDetails.ten_bai_tap || 'Bài tập không tên');
            document.getElementById('sub_modal_status').innerHTML = getStatusBadge(assignmentDetails.trang_thai_final || submission.trang_thai);

            let ngayNopDisplay = 'N/A';
            if (submission.ngay_nop) {
                 try {
                     const ngayNopDate = safeParseDate(submission.ngay_nop);
                     if (ngayNopDate) {
                         ngayNopDisplay = ngayNopDate.toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' });
                     } else { ngayNopDisplay = submission.ngay_nop; }
                 } catch(e) { ngayNopDisplay = submission.ngay_nop; }
            }
            document.getElementById('sub_modal_ngay_nop').textContent = ngayNopDisplay;
            document.getElementById('sub_modal_diem_so').textContent = submission.diem_so !== null ? submission.diem_so : 'Chưa chấm';

            document.getElementById('sub_modal_file_wrapper').style.display = 'none';
            document.getElementById('sub_modal_essay_wrapper').style.display = 'none';
            document.getElementById('sub_modal_mcq_wrapper').style.display = 'none';

            const assignmentType = assignmentDetails.loai_bai_tap;

            if (assignmentType === 'UploadFile') {
                if (submission.file_dinh_kem) {
                    const fileLink = document.getElementById('sub_modal_file_link');
                    fileLink.href = `${BASE_URL}/public/${submission.file_dinh_kem}`;
                    document.getElementById('sub_modal_file_name').textContent = submission.file_dinh_kem.split('/').pop() || 'Tải file đã nộp';
                    document.getElementById('sub_modal_file_wrapper').style.display = 'block';
                } else {
                     document.getElementById('sub_modal_file_wrapper').innerHTML = '<p class="text-gray-500 italic">Không có file được nộp.</p>';
                     document.getElementById('sub_modal_file_wrapper').style.display = 'block';
                }
            } else if (assignmentType === 'TuLuan') {
                document.getElementById('sub_modal_essay_text').textContent = submission.noi_dung_tra_loi || '(Không có nội dung)';
                document.getElementById('sub_modal_essay_wrapper').style.display = 'block';
            } else if (assignmentType === 'TracNghiem') {
                const mcqContainer = document.getElementById('sub_modal_mcq_answers');
                mcqContainer.innerHTML = ''; 
                try {
                    const userAnswers = JSON.parse(submission.noi_dung_tra_loi || '{}');
                    // SỬA: Check type trước parse
                    let questions = [];
                    if (assignmentType === 'TracNghiem' && assignmentDetails.content && typeof assignmentDetails.content === 'string') {
                        try {
                            const parsedContent = JSON.parse(assignmentDetails.content);
                            questions = parsedContent.questions || [];
                        } catch (parseErr) {
                            console.error("Lỗi parse content JSON:", parseErr);
                            questions = [];
                        }
                    }

                    if(questions.length === 0) {
                         mcqContainer.innerHTML = '<p class="text-gray-500">Không tìm thấy dữ liệu câu hỏi gốc.</p>';
                    } else {
                         renderMCQReview(questions, userAnswers);
                    }
                } catch (e) {
                     console.error("Lỗi parse JSON:", e, "Data:", submission.noi_dung_tra_loi, "Questions:", assignmentDetails.content);
                     mcqContainer.innerHTML = '<p class="text-red-500">Lỗi đọc đáp án đã nộp.</p>';
                }
                document.getElementById('sub_modal_mcq_wrapper').style.display = 'block';
            }

            // Xử lý nút Hủy Nộp
            const canDelete = !String(assignmentDetails.trang_thai_final).startsWith('Hoàn Thành');
            if (canDelete) {
                deleteBtn.style.display = 'block';
                deleteBtn.dataset.id = assignment.ma_bai_tap;
            }

            loadingDiv.style.display = 'none';
            contentDiv.style.display = 'block';

        } catch (error) {
            console.error("Lỗi openSubmissionDetailModal:", error);
            loadingDiv.innerHTML = `<p class="text-red-500 font-semibold p-4">${error.message}</p>`;
            deleteBtn.style.display = 'none';
            loadingDiv.style.display = 'block';
            contentDiv.style.display = 'none';
        }
    }
    
    // Helper renderMCQReview
    function renderMCQReview(questions, userAnswers) {
         const mcqContainer = document.getElementById('sub_modal_mcq_answers');
         mcqContainer.innerHTML = '';
         if(!questions || questions.length === 0) {
             mcqContainer.innerHTML = '<p class="text-gray-500">Không tìm thấy dữ liệu câu hỏi gốc.</p>';
             return;
         }
         questions.forEach((q, index) => {
             const userAnswerKey = `q${q.id}`;
             const userAnswerValue = userAnswers[userAnswerKey] || 'N/A';
             let userAnswerText = `Bạn chọn: ${userAnswerValue}`;
             
             if (Array.isArray(q.options) && userAnswerValue !== 'N/A') {
                  const matchingOption = q.options.find(opt => String(opt).trim().startsWith(userAnswerValue + '.'));
                  if(matchingOption) userAnswerText = escapeHtml(matchingOption);
             }

             mcqContainer.innerHTML += `
                 <div class="mcq-answer-review text-sm">
                     <p class="font-semibold text-gray-800">Câu ${index + 1}: ${escapeHtml(q.text)}</p>
                     <p class="text-blue-700 ml-4"><strong>${userAnswerText}</strong></p>
                 </div>
             `;
         });
    }
    

    // 3. Load Trang Trắc Nghiệm (SỬA: Sử dụng gio_bat_dau từ currentAssignment; Thêm alert <1 phút)
    async function loadMultipleChoiceSection(assignment) {
        switchSection('multipleChoiceSection');
        document.getElementById('mcqAssignmentName').textContent = escapeHtml(assignment.ten_bai_tap);
        const instructionsDiv = document.getElementById('mcqInstructions');
        const timerBar = document.getElementById('timerBar');
        const timerDisplay = document.getElementById('countdownTimer');
        
        instructionsDiv.innerHTML = '';
        timerBar.style.display = 'none';
        timerDisplay.textContent = '--:--';
        timerDisplay.classList.remove('warning');
        document.getElementById('mcqNotification').innerHTML = '';
        document.getElementById('questionsContainer').innerHTML = '';
        document.getElementById('mcqLoading').style.display = 'block';
        document.getElementById('questionsContainer').style.display = 'none';
        document.getElementById('submitMultipleChoice').disabled = true;
        
        if (countdownInterval) clearInterval(countdownInterval);

        try {
            const response = await fetch(`${BASE_URL}/baitap/getChiTiet/${assignment.ma_bai_tap}`);
            if (!response.ok) throw new Error(`Lỗi mạng (${response.status})`);
            const result = await response.json();
            if (!result.success || !result.assignment) throw new Error(result.message || 'Không thể tải chi tiết.');

            currentAssignment = result.assignment; // Update with full data
            let jsonString = currentAssignment.content; 
            let questions = [];

            if (jsonString && typeof jsonString === 'string') {
                 try {
                     const decodedData = JSON.parse(jsonString);
                     if (decodedData && Array.isArray(decodedData.questions)) {
                         questions = decodedData.questions;
                     } else {
                          throw new Error("Cấu trúc JSON không hợp lệ (thiếu 'questions').");
                     }
                 } catch (e) {
                     throw new Error(`Lỗi định dạng câu hỏi. Vui lòng báo GV. (Lỗi: ${e.message})`);
                 }
            } else if (Array.isArray(currentAssignment.questions)) {
                 questions = currentAssignment.questions;
            }
            
            currentAssignment.questions = questions; // Cập nhật mảng questions

            // --- LOGIC TIMER (Sử dụng gio_bat_dau từ API) ---
            const thoiGianLamBaiPhut = parseInt(currentAssignment.thoi_gian_lam_bai);
            const gioBatDauLamBai = currentAssignment.gio_bat_dau_lam_bai;

            if (!thoiGianLamBaiPhut || !gioBatDauLamBai) {
                throw new Error("Thông tin bài làm (thời gian hoặc giờ bắt đầu) bị thiếu. Vui lòng báo GV.");
            }

            const startTime = safeParseDate(gioBatDauLamBai);
            if (!startTime) {
                throw new Error("Lỗi đọc giờ bắt đầu làm bài.");
            }
            
            const endTime = new Date(startTime.getTime() + thoiGianLamBaiPhut * 60000);
            timerBar.style.display = 'block';

            countdownInterval = setInterval(() => {
                const now = new Date();
                const remainingMs = endTime.getTime() - now.getTime();

                if (remainingMs <= 0) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                    timerDisplay.textContent = "00:00";
                    timerDisplay.classList.add('warning');
                    console.log("Hết giờ! Tự động nộp bài.");
                    submitMcqForm(true); // Tự động nộp
                } else {
                    const minutes = Math.floor(remainingMs / 60000);
                    const seconds = Math.floor((remainingMs % 60000) / 1000);
                    timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    if (minutes < 5) {
                        timerDisplay.classList.add('warning');
                    } else {
                        timerDisplay.classList.remove('warning');
                    }
                    // SỬA: Alert khi <1 phút
                    if (minutes <= 1 && !timerDisplay.dataset.alerted) {
                        alert('Còn dưới 1 phút! Hãy hoàn thành và nộp bài ngay.');
                        timerDisplay.dataset.alerted = 'true';
                    }
                }
            }, 1000);
            // --- HẾT LOGIC TIMER ---

            // Render câu hỏi
            const questionsContainer = document.getElementById('questionsContainer');
            questionsContainer.innerHTML = '';
            if (questions.length === 0) {
                questionsContainer.innerHTML = '<p class="text-center text-red-500 font-semibold">Không có câu hỏi nào.</p>';
            } else {
                questions.forEach((q, index) => {
                    const questionDiv = document.createElement('div');
                    questionDiv.className = 'border p-4 rounded-lg bg-gray-50 shadow-sm';
                    const optionsHtml = Array.isArray(q.options) ? q.options.map(opt => `
                        <label class="flex items-center text-gray-700 hover:bg-indigo-50 p-2 rounded cursor-pointer transition duration-150">
                            <input type="radio" required name="q${q.id}" value="${String(opt).split('.')[0]}" class="mr-3 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                            <span>${escapeHtml(opt)}</span>
                        </label>`).join('') : '<p class="text-red-500">Lỗi: Thiếu phương án.</p>';
                    questionDiv.innerHTML = `<p class="font-semibold text-gray-900 mb-3">Câu ${index + 1}: ${escapeHtml(q.text || 'N/A')}</p><div class="space-y-2">${optionsHtml}</div>`;
                    questionsContainer.appendChild(questionDiv);
                });
            }
            
            const dueDateObj = safeParseDate(assignment.han_nop);
            instructionsDiv.innerHTML = `<p><strong>Chú ý:</strong> Bạn có <span class="font-bold text-red-600">${thoiGianLamBaiPhut} phút</span> để hoàn thành bài. Đồng hồ sẽ tự nộp bài khi hết giờ.</p> <p class="mt-2">Hạn nộp cuối: <span class="font-bold">${dueDateObj ? dueDateObj.toLocaleString('vi-VN') : 'N/A'}</span></p>`;
            
            document.getElementById('mcqLoading').style.display = 'none';
            document.getElementById('questionsContainer').style.display = 'block';
            document.getElementById('submitMultipleChoice').disabled = false;
            
        } catch (error) {
            console.error("Lỗi load MCQ:", error);
            document.getElementById('mcqLoading').innerHTML = `<p class="text-red-500 font-semibold p-4 border border-red-300 bg-red-50 rounded">${error.message}</p>`;
            document.getElementById('submitMultipleChoice').disabled = true;
            if(countdownInterval) clearInterval(countdownInterval); 
            timerBar.style.display = 'none';
        }
    }

    // 4. Load Trang Tự Luận/Upload
    function loadEssaySection(assignment) {
        currentAssignment = assignment; 
        switchSection('essaySection');
        
        const dueDateObj = safeParseDate(assignment.han_nop);
        const isOverdue = dueDateObj && (new Date() > dueDateObj);
        
        document.getElementById('essayAssignmentName').textContent = escapeHtml(assignment.ten_bai_tap);
        document.getElementById('essayInstructions').innerHTML = `<p class="font-semibold">Yêu Cầu Bài Tập:</p><pre class="mt-2 text-gray-700 max-h-24 overflow-y-auto whitespace-pre-wrap font-sans">${escapeHtml(assignment.mo_ta || 'N/A')}</pre><p class="mt-4">Hạn nộp: <span class="font-bold ${isOverdue ? 'text-red-600' : ''}">${dueDateObj ? dueDateObj.toLocaleString('vi-VN') : 'N/A'}</span></p>`;
        document.getElementById('essayAssignmentTypeDisplay').textContent = getAssignmentTypeDisplay(assignment.loai_bai_tap);
        document.getElementById('essayAnswerInput').value = ''; 
        document.getElementById('uploadFile').value = '';
        document.getElementById('typingNotification').innerHTML = ''; 
        document.getElementById('uploadNotification').innerHTML = '';

        const submitTypingBtn = document.getElementById('submitTyping');
        const submitUploadBtn = document.getElementById('submitUpload');
        
        if (isOverdue) {
            showNotification('Đã quá hạn, không thể nộp bài.', 'error', 'typingNotification', 0);
            showNotification('Đã quá hạn, không thể nộp bài.', 'error', 'uploadNotification', 0);
            submitTypingBtn.disabled = true;
            submitUploadBtn.disabled = true;
        } else {
            submitTypingBtn.disabled = false;
            submitUploadBtn.disabled = false;
        }

        setupEssayTabs(); 
    }

    // --- Submission Handlers ---

    // HÀM MỚI: Dùng chung cho nộp Trắc nghiệm (SỬA: Xử lý diem_so từ response)
    async function submitMcqForm(isAutoSubmit = false) {
        const assignment = currentAssignment;
        if (!assignment || !Array.isArray(assignment.questions)) return;

        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        
        const formEl = document.getElementById('mcqForm');
        const formData = new FormData(formEl);
        const answers = {};
        let answeredCount = 0;
        assignment.questions.forEach(q => { const answer = formData.get(`q${q.id}`); answers[`q${q.id}`] = answer; if (answer !== null) answeredCount++; });

        if (!isAutoSubmit) {
            if (answeredCount < assignment.questions.length && !confirm(`Bạn mới trả lời ${answeredCount}/${assignment.questions.length} câu. Vẫn nộp?`)) return;
            if (answeredCount == assignment.questions.length && !confirm('Bạn chắc chắn muốn nộp bài?')) return;
        }
        
        const submitButton = document.getElementById('submitMultipleChoice'); 
        submitButton.disabled = true; 
        submitButton.textContent = 'Đang nộp...';
        showNotification(isAutoSubmit ? 'Hết giờ! Đang tự động nộp bài...' : 'Đang xử lý...', 'info', 'mcqNotification', 0);

        try {
            const response = await fetch(`${BASE_URL}/baitap/nopBaiTracNghiem`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ma_bai_tap: assignment.ma_bai_tap, answers: answers })
            });
            
            if (!response.ok) {
                const errText = await response.text();
                 try { const errJson = JSON.parse(errText); throw new Error(errJson.message || 'Lỗi server.'); }
                 catch(e) { throw new Error(errText || 'Lỗi server.'); }
            }
            const result = await response.json();
            if (!result.success) throw new Error(result.message || 'Lỗi server.');

            showNotification(result.message || 'Nộp thành công!', 'success', 'mcqNotification');
            if (result.diem_so !== undefined) {
                console.log(`Điểm: ${result.diem_so}`);
            }
            updateAssignmentStatus(assignment.ma_bai_tap, result.newStatus || 'Hoàn Thành');
            setTimeout(() => switchSection('listSection'), 2000);
        } catch (error) {
            console.error("Lỗi nộp MCQ:", error); 
            showNotification(`Lỗi: ${error.message}.`, 'error', 'mcqNotification', 0);
            submitButton.disabled = false; 
            submitButton.textContent = 'Nộp Bài Trắc Nghiệm';
        }
    }
    
    // Listener cho nút nộp Trắc nghiệm
    document.getElementById('mcqForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        await submitMcqForm(false); 
    });

    // Nộp Tự Luận (Gõ)
    document.getElementById('typingForm').addEventListener('submit', async (e) => {
         e.preventDefault(); 
         const assignment = currentAssignment; 
         const answer = document.getElementById('essayAnswerInput').value.trim();
         if (!assignment) return; 

         const dueDateObj = safeParseDate(assignment.han_nop);
         const isOverdue = dueDateObj && (new Date() > dueDateObj);
         if (isOverdue) {
             showNotification('Đã quá hạn nộp bài. Không thể nộp.', 'error', 'typingNotification', 0);
             return;
         }
         
         if (answer.length < 20) { showNotification('Nội dung quá ngắn (yêu cầu ít nhất 20 ký tự).', 'error', 'typingNotification', 0); return; }
         if (!confirm('Chắc chắn nộp bài?')) return;
         
         const submitButton = document.getElementById('submitTyping'); 
         submitButton.disabled = true; 
         submitButton.textContent = 'Đang nộp...';
         showNotification('Đang xử lý...', 'info', 'typingNotification', 0);

         try {
             const response = await fetch(`${BASE_URL}/baitap/nopBaiGoTrucTiep`, {
                 method: 'POST', headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ ma_bai_tap: assignment.ma_bai_tap, noi_dung: answer })
             });
             if (!response.ok) {
                 const errText = await response.text();
                 try { const errJson = JSON.parse(errText); throw new Error(errJson.message || 'Lỗi server.'); }
                 catch(e) { throw new Error(errText || 'Lỗi server.'); }
             }
             const result = await response.json();
             if (!result.success) throw new Error(result.message || 'Lỗi server.');

             showNotification(result.message || 'Nộp thành công!', 'success', 'typingNotification');
             updateAssignmentStatus(assignment.ma_bai_tap, result.newStatus || 'Đã Nộp');
             setTimeout(() => switchSection('listSection'), 1500);
         } catch (error) {
             console.error("Lỗi nộp bài gõ:", error); 
             showNotification(`Lỗi: ${error.message}.`, 'error', 'typingNotification', 0);
             submitButton.disabled = false; 
             submitButton.textContent = 'Xác Nhận Nộp Bài (Gõ)';
         }
    });

    // Nộp Tự Luận (Upload)
    document.getElementById('uploadForm').addEventListener('submit', async (e) => {
         e.preventDefault(); 
         const assignment = currentAssignment; 
         const fileInput = document.getElementById('uploadFile'); 
         const file = fileInput.files[0];
         if (!assignment || !fileInput) return; 
         
         const dueDateObj = safeParseDate(assignment.han_nop);
         const isOverdue = dueDateObj && (new Date() > dueDateObj);
         if (isOverdue) {
             showNotification('Đã quá hạn nộp bài. Không thể nộp.', 'error', 'uploadNotification', 0);
             return;
         }
         
         if (!file) { showNotification('Vui lòng chọn file.', 'error', 'uploadNotification', 0); return; }
         const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']; const maxSize = 5 * 1024 * 1024;
         if (!allowedTypes.includes(file.type)) { showNotification('File phải là PDF/DOCX.', 'error', 'uploadNotification', 0); return; }
         if (file.size > maxSize) { showNotification('File quá lớn (> 5MB).', 'error', 'uploadNotification', 0); return; }
         if (!confirm(`Chắc chắn nộp file "${file.name}"?`)) return;
         
         const submitButton = document.getElementById('submitUpload'); 
         submitButton.disabled = true; 
         submitButton.textContent = 'Đang upload...';
         showNotification('Đang xử lý file...', 'info', 'uploadNotification', 0);
         const formData = new FormData(); 
         formData.append('ma_bai_tap', assignment.ma_bai_tap); 
         formData.append('file_bai_lam', file);

         try {
             const response = await fetch(`${BASE_URL}/baitap/nopBaiUpload`, { method: 'POST', body: formData });
             if (!response.ok) {
                 const errText = await response.text();
                 try { const errJson = JSON.parse(errText); throw new Error(errJson.message || 'Lỗi server.'); }
                 catch(e) { throw new Error(errText || 'Lỗi server.'); }
             }
             const result = await response.json();
             if (!result.success) throw new Error(result.message || 'Lỗi server.');

             showNotification(result.message || 'Upload thành công!', 'success', 'uploadNotification');
             updateAssignmentStatus(assignment.ma_bai_tap, result.newStatus || 'Đã Nộp');
             setTimeout(() => switchSection('listSection'), 1500);
         } catch (error) {
             console.error("Lỗi upload:", error); 
             showNotification(`Lỗi: ${error.message}.`, 'error', 'uploadNotification', 0);
              submitButton.disabled = false; 
              submitButton.textContent = 'Xác Nhận Nộp Bài (Upload)';
         } finally {
              fileInput.value = ''; 
         }
    });
    
    // HÀM XỬ LÝ HỦY BÀI NỘP
    document.getElementById('deleteSubmissionBtn').addEventListener('click', async (e) => {
         const button = e.currentTarget;
         const assignmentId = button.dataset.id;
         if (!assignmentId) return;
         
         if (!confirm('Bạn có chắc chắn muốn hủy bài nộp này? Bạn sẽ phải làm lại từ đầu.')) {
             return;
         }
         
         button.disabled = true;
         button.textContent = 'Đang hủy...';
         showNotification('Đang xử lý...', 'info', 'deleteSubmissionNotification', 0);
         
         try {
             const response = await fetch(`${BASE_URL}/baitap/huyBaiNop`, {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ ma_bai_tap: assignmentId })
             });
             
             if (!response.ok) {
                 const errText = await response.text();
                 try { const errJson = JSON.parse(errText); throw new Error(errJson.message || 'Lỗi server.'); }
                 catch(e) { throw new Error(errText || 'Lỗi server.'); }
             }
             const result = await response.json();
             if (!result.success) throw new Error(result.message || 'Lỗi server.');
             
             // Thành công
             showNotification(result.message, 'success', 'deleteSubmissionNotification', 2000);
             updateAssignmentStatus(assignmentId, result.newStatus || 'Chưa Làm');
             setTimeout(() => {
                 closeModal('submissionDetailModal');
             }, 1500);
             
         } catch (error) {
             console.error("Lỗi hủy bài nộp:", error);
             showNotification(`Lỗi: ${error.message}.`, 'error', 'deleteSubmissionNotification', 0);
             button.disabled = false;
             button.textContent = 'Hủy Bài Nộp';
         }
    });


    // --- Khởi Tạo ---
    document.addEventListener('DOMContentLoaded', () => { renderAssignmentList(); });
</script>
</body>
</html>