<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Tập - Học Sinh | THPT Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
        .spinner-border { width: 1.5rem; height: 1.5rem; border-width: .2em; border-style: solid; border-color: currentColor transparent currentColor transparent; border-radius: 50%; animation: spin 0.75s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .submission-content pre {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 1rem;
            border-radius: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: inherit;
            font-size: 0.95rem;
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
            border-bottom: 2px solid #e5e7eb;
        }
        #countdownTimer.warning {
            color: #dc2626; 
            background-color: #fee2e2; 
            animation: pulse 1s infinite;
        }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-4 md:p-6">
        <header class="mb-8 p-6 rounded-lg shadow-lg header-bg flex justify-between items-center">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold">Quản Lý Bài Tập</h1>
                <p class="text-indigo-200 text-sm md:text-base mt-1">Xin chào, <?php echo htmlspecialchars($data['user_name'] ?? 'Học Sinh'); ?>!</p>
            </div>
            <a href="<?php echo BASE_URL ?? ''; ?>/dashboard" class="bg-white/20 hover:bg-white/30 text-white py-2 px-4 rounded-lg text-sm font-semibold transition">
                &larr; Dashboard
            </a>
        </header>

        <div id="listSection" class="content-section active bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="p-4 md:p-6">
                <h2 class="text-xl md:text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="bi bi-list-task mr-2 text-indigo-600"></i> Danh Sách Bài Tập
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-indigo-50 text-indigo-800 uppercase text-xs md:text-sm leading-normal">
                                <th class="py-3 px-4 text-center w-16">#</th>
                                <th class="py-3 px-4 text-left">Tên Bài Tập</th>
                                <th class="py-3 px-4 text-left hidden md:table-cell">Môn Học</th>
                                <th class="py-3 px-4 text-left w-32">Loại</th>
                                <th class="py-3 px-4 text-left w-40">Hạn Nộp</th>
                                <th class="py-3 px-4 text-left w-32">Trạng Thái</th>
                                <th class="py-3 px-4 text-center w-32">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="assignmentTableBody" class="text-gray-700 text-sm divide-y divide-gray-100">
                             <?php if (empty($data['assignments'])): ?>
                                 <tr><td colspan="7" class="text-center py-10 text-gray-500">Tuyệt vời! Hiện tại chưa có bài tập nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="assignmentModal" class="modal-overlay fixed inset-0 hidden flex items-center justify-center p-4">
             <div class="bg-white rounded-xl p-6 md:p-8 w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto transform transition-all scale-100">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
                        <i class="bi bi-info-circle-fill text-indigo-600 mr-2"></i> Chi Tiết Bài Tập
                    </h2>
                    <button class="text-gray-400 hover:text-gray-700 text-3xl leading-none" onclick="closeModal('assignmentModal')">&times;</button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-600 mb-6 bg-gray-50 p-4 rounded-lg">
                    <p><strong>Môn Học:</strong> <span id="modalSubject" class="text-gray-900"></span></p>
                    <p><strong>Loại Bài:</strong> <span id="modalAssignmentTypeDisplay" class="font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-200"></span></p>
                    <p><strong>Ngày Giao:</strong> <span id="modalAssignedDate"></span></p>
                    <p><strong>Hạn Nộp:</strong> <span id="modalDueDate" class="text-red-600 font-bold"></span></p>
                    <p class="md:col-span-2 text-lg"><strong>Tên Bài Tập:</strong> <span id="modalAssignmentName" class="font-bold text-gray-900"></span></p>
                </div>

                <div class="mb-6">
                    <p class="font-bold text-gray-800 mb-2 border-b pb-1">Yêu Cầu / Đề Bài:</p>
                    <div id="modalContent" class="text-gray-700 whitespace-pre-wrap text-sm font-sans bg-white border p-4 rounded-lg shadow-inner min-h-[100px]"></div>
                </div>

                <div class="mb-6 flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <i class="bi bi-paperclip text-blue-500 text-xl mr-3"></i>
                    <div>
                        <strong class="text-gray-700 block text-xs uppercase tracking-wide">Tài Liệu Đính Kèm</strong>
                        <a id="modalAttachment" href="#" target="_blank" class="text-blue-600 hover:underline font-medium break-all"></a>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button class="bg-gray-200 text-gray-700 py-2 px-5 rounded-lg hover:bg-gray-300 font-medium transition" onclick="closeModal('assignmentModal')">Đóng</button>
                    <button id="startAssignmentBtn" class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 font-bold shadow-lg transform hover:-translate-y-0.5 transition hidden">
                        Bắt Đầu Làm Bài
                    </button>
                </div>
            </div>
        </div>
        
        <div id="submissionDetailModal" class="modal-overlay fixed inset-0 hidden flex items-center justify-center p-4">
             <div class="bg-white rounded-xl p-0 w-full max-w-4xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="bg-indigo-600 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold"><i class="bi bi-eye-fill mr-2"></i> Chi Tiết Bài Đã Nộp</h2>
                    <button class="text-indigo-200 hover:text-white text-3xl leading-none" onclick="closeModal('submissionDetailModal')">&times;</button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6 md:p-8">
                    <div id="submissionModalLoading" class="text-center p-10 text-gray-600">
                        <div class="spinner-border text-indigo-600 inline-block mr-2" role="status"></div>
                        <span class="text-lg">Đang tải dữ liệu...</span>
                     </div>
                     
                     <div id="submissionModalContent" style="display: none;">
                        
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div>
                                <h3 id="sub_modal_title" class="text-lg font-bold text-gray-800 mb-1"></h3>
                                <p class="text-sm text-gray-500">Nộp lúc: <span id="sub_modal_ngay_nop" class="font-medium text-gray-700"></span></p>
                            </div>
                            <div class="mt-3 md:mt-0 text-right">
                                <div class="text-sm text-gray-500 mb-1">Điểm Số</div>
                                <span id="sub_modal_diem_so" class="text-3xl font-black text-indigo-600 bg-white px-4 py-2 rounded shadow-sm border">--</span>
                                <span class="text-gray-400 text-sm">/ 10</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2">
                                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2"><i class="bi bi-file-earmark-text mr-1"></i> Bài Làm Của Bạn</h4>
                                
                                <div id="sub_modal_file_wrapper" style="display: none;" class="bg-blue-50 p-4 rounded-lg border border-blue-200 flex items-center">
                                    <i class="bi bi-file-earmark-arrow-down-fill text-3xl text-blue-500 mr-4"></i>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-blue-600 uppercase font-bold">File đính kèm</p>
                                        <a id="sub_modal_file_link" href="#" target="_blank" class="text-blue-800 font-medium hover:underline truncate block">
                                            <span id="sub_modal_file_name">filename.pdf</span>
                                        </a>
                                    </div>
                                    <a id="sub_modal_download_btn" href="#" target="_blank" class="bg-white text-blue-600 px-3 py-1 rounded border border-blue-300 hover:bg-blue-100 text-sm">Tải về</a>
                                </div>
                                
                                <div id="sub_modal_essay_wrapper" style="display: none;">
                                     <pre id="sub_modal_essay_text" class="text-sm leading-relaxed"></pre>
                                </div>
                                
                                <div id="sub_modal_mcq_wrapper" style="display: none;">
                                     <div id="sub_modal_mcq_answers" class="max-h-96 overflow-y-auto pr-2 space-y-3">
                                         </div>
                                 </div>
                            </div>

                            <div class="lg:col-span-1">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 h-full">
                                    <h4 class="font-bold text-yellow-800 mb-3 flex items-center">
                                        <i class="bi bi-chat-quote-fill mr-2"></i> Lời Phê Của Giáo Viên
                                    </h4>
                                    <div id="sub_modal_nhan_xet" class="text-sm text-gray-800 italic leading-relaxed whitespace-pre-wrap">
                                        Chưa có nhận xét.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="deleteSubmissionNotification" class="mt-4 text-sm font-semibold text-center"></div>
                     </div>
                 </div>
                 
                 <div class="bg-gray-100 p-4 flex justify-between items-center border-t">
                     <button id="deleteSubmissionBtn" data-id="" class="text-red-600 hover:text-red-800 font-medium text-sm transition hidden">
                         <i class="bi bi-trash mr-1"></i> Hủy Bài Nộp (Làm lại)
                     </button>
                    <button class="bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition" onclick="closeModal('submissionDetailModal')">Đóng</button>
                </div>
            </div>
        </div>


         <div id="multipleChoiceSection" class="content-section max-w-5xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden">
            <div id="timerBar" class="bg-indigo-600 text-white px-6 py-3 flex justify-between items-center shadow-md sticky top-0 z-50" style="display: none;">
                <span class="font-medium text-indigo-100"><i class="bi bi-clock-history mr-2"></i>Thời gian còn lại:</span>
                <span id="countdownTimer" class="text-2xl font-bold font-mono bg-indigo-800 px-3 py-1 rounded shadow-inner">--:--</span>
            </div>

            <div class="p-6 md:p-8">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Bài Làm: <span id="mcqAssignmentName" class="text-indigo-600"></span></h2>
                    <button class="text-sm text-gray-500 hover:text-gray-800 flex items-center transition" onclick="switchSection('listSection')">
                        <i class="bi bi-x-lg mr-1"></i> Thoát
                    </button>
                </div>
                
                <div id="mcqInstructions" class="mb-6 p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg text-sm flex items-start">
                    <i class="bi bi-exclamation-triangle-fill mr-3 text-xl mt-0.5"></i>
                    <div></div> </div>

                <form id="mcqForm">
                    <div id="mcqLoading" class="text-center p-12 text-gray-500">
                        <div class="spinner-border text-indigo-500 inline-block mb-3" role="status"></div>
                        <p>Đang tải câu hỏi, vui lòng đợi...</p>
                    </div>
                    <div id="questionsContainer" class="space-y-8" style="display: none;"></div>
                    
                    <div id="mcqNotification" class="mt-6 text-center"></div>
                    
                    <div class="mt-8 pt-6 border-t">
                        <button type="submit" id="submitMultipleChoice" class="w-full md:w-auto md:min-w-[200px] bg-green-600 text-white py-3 px-8 rounded-lg hover:bg-green-700 font-bold text-lg shadow-lg transform hover:-translate-y-0.5 transition duration-200 block mx-auto disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="bi bi-send-fill mr-2"></i> Nộp Bài
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="essaySection" class="content-section max-w-4xl mx-auto bg-white shadow-xl rounded-lg p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                    Nộp Bài: <span id="essayAssignmentName" class="text-indigo-600"></span>
                    <span id="essayAssignmentTypeDisplay" class="ml-2 text-sm font-normal text-white bg-gray-500 px-2 py-1 rounded"></span>
                </h2>
                 <button class="text-sm bg-gray-100 text-gray-600 py-2 px-4 rounded-lg hover:bg-gray-200 transition" onclick="switchSection('listSection')">
                    &larr; Quay lại
                 </button>
            </div>

            <div id="essayInstructions" class="mb-6 p-5 bg-blue-50 text-blue-900 border border-blue-100 rounded-lg text-sm"></div>
            
            <div class="flex border-b border-gray-200 mb-6">
                <button id="tabTypingBtn" class="tab-button active py-3 px-6 rounded-t-lg text-sm font-medium border-b-2 border-indigo-600 text-indigo-600 bg-white"><i class="bi bi-keyboard-fill mr-2"></i>Nhập Văn Bản</button>
                <button id="tabUploadBtn" class="tab-button py-3 px-6 rounded-t-lg text-sm font-medium text-gray-500 hover:text-gray-700"><i class="bi bi-cloud-upload-fill mr-2"></i>Tải File Lên</button>
            </div>

            <div id="typingTabContent" class="tab-content animate-fade-in">
                <form id="typingForm" class="space-y-4">
                    <textarea id="essayAnswerInput" rows="12" class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm font-sans text-base leading-relaxed" placeholder="Nhập nội dung bài làm của bạn tại đây..."></textarea>
                    <div id="typingNotification" class="mt-2"></div>
                    <button type="submit" id="submitTyping" class="mt-4 bg-green-600 text-white py-3 px-8 rounded-lg hover:bg-green-700 font-bold w-full md:w-auto shadow-md transition">
                        <i class="bi bi-send-fill mr-2"></i> Nộp Bài
                    </button>
                </form>
            </div>

            <div id="uploadTabContent" class="tab-content animate-fade-in" style="display: none;">
                <form id="uploadForm" class="space-y-6">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 transition cursor-pointer relative">
                        <input type="file" id="uploadFile" name="file_bai_lam" accept=".pdf,.docx,.doc,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"/>
                        <i class="bi bi-cloud-arrow-up-fill text-5xl text-indigo-300 mb-3 block"></i>
                        <p class="text-gray-700 font-medium">Kéo thả file vào đây hoặc click để chọn</p>
                        <p class="text-sm text-gray-500 mt-1">Chấp nhận PDF, DOCX (Tối đa 5MB)</p>
                        <p id="fileNameDisplay" class="mt-4 text-indigo-600 font-bold hidden"></p>
                    </div>
                    
                    <div id="uploadNotification" class="mt-2"></div>
                    <button type="submit" id="submitUpload" class="bg-green-600 text-white py-3 px-8 rounded-lg hover:bg-green-700 font-bold w-full md:w-auto shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="bi bi-send-fill mr-2"></i> Xác Nhận Nộp File
                    </button>
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
    let countdownInterval = null;

    // --- Helper Functions ---
    function switchSection(targetId) {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
            section.classList.remove('active');
        });
        const target = document.getElementById(targetId);
        if (target) {
            target.style.display = 'block';
            void target.offsetWidth; 
            target.classList.add('active');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function getAssignmentTypeDisplay(type) {
         switch(type) {
             case 'TracNghiem': return 'Trắc Nghiệm';
             case 'TuLuan': return 'Tự Luận';
             case 'UploadFile': return 'Tập Tin';
             default: return type;
         }
    }

    function getStatusBadge(status) {
        let colorClasses = 'bg-gray-100 text-gray-800';
        let icon = '<i class="bi bi-circle"></i>';
        
        if (!status) return `<span class="${colorClasses} py-1 px-3 rounded-full text-xs font-semibold whitespace-nowrap">Lỗi</span>`;
        
        if (status.startsWith('Chưa Làm')) { colorClasses = 'bg-blue-100 text-blue-800'; icon = '<i class="bi bi-circle"></i>'; }
        else if (status.startsWith('Đã Nộp')) { colorClasses = 'bg-green-100 text-green-800'; icon = '<i class="bi bi-check-circle"></i>'; }
        else if (status.startsWith('Chờ Chấm')) { colorClasses = 'bg-purple-100 text-purple-800'; icon = '<i class="bi bi-hourglass-split"></i>'; }
        else if (status.startsWith('Hoàn Thành')) { colorClasses = 'bg-teal-100 text-teal-800'; icon = '<i class="bi bi-award-fill"></i>'; }
        else if (status.startsWith('Quá Hạn')) { colorClasses = 'bg-orange-100 text-orange-800'; icon = '<i class="bi bi-exclamation-circle"></i>'; }
        
        return `<span class="${colorClasses} py-1 px-3 rounded-full text-xs font-bold whitespace-nowrap shadow-sm border border-black/5">${icon} ${status}</span>`;
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
        let bgColor = 'bg-green-50'; let textColor = 'text-green-800'; let borderColor = 'border-green-200'; let icon = 'bi-check-circle-fill';
        
        if (type === 'error') { bgColor = 'bg-red-50'; textColor = 'text-red-800'; borderColor = 'border-red-200'; icon = 'bi-exclamation-triangle-fill'; }
        else if (type === 'info') { bgColor = 'bg-blue-50'; textColor = 'text-blue-800'; borderColor = 'border-blue-200'; icon = 'bi-info-circle-fill'; }
        
        notification.innerHTML = `
            <div class="flex items-center p-4 mb-4 text-sm ${textColor} ${bgColor} border ${borderColor} rounded-lg shadow-sm" role="alert">
                <i class="bi ${icon} text-lg mr-3"></i>
                <span class="font-medium">${message}</span>
            </div>`;
            
        if (duration > 0) {
             setTimeout(() => { if (notification) notification.innerHTML = ''; }, duration);
        }
    }
    
    function updateAssignmentStatus(id, newStatus, newSubmissionId = null) {
        const assignmentIndex = assignmentsData.findIndex(a => a.ma_bai_tap == id); 
        if (assignmentIndex > -1) {
            assignmentsData[assignmentIndex].trang_thai_final = newStatus; 
            if (newSubmissionId) {
                assignmentsData[assignmentIndex].ma_bai_nop = newSubmissionId;
            }
            renderAssignmentList(); 
        }
    }
    
    // Xử lý Tabs Tự Luận/Upload
    function setupEssayTabs() {
        const typingBtn = document.getElementById('tabTypingBtn');
        const uploadBtn = document.getElementById('tabUploadBtn');
        const typingContent = document.getElementById('typingTabContent');
        const uploadContent = document.getElementById('uploadTabContent');
         if (!typingBtn || !uploadBtn || !typingContent || !uploadContent) return;

         const assignmentType = currentAssignment?.loai_bai_tap;
         
         typingBtn.style.display = (assignmentType === 'TuLuan') ? 'block' : 'none';
         uploadBtn.style.display = (assignmentType === 'UploadFile') ? 'block' : 'none';
         
        const switchTab = (activeBtn, inactiveBtn, activeContent, inactiveContent) => {
             activeBtn.classList.add('active', 'bg-white', 'text-indigo-600', 'border-b-2', 'border-indigo-600');
             activeBtn.classList.remove('text-gray-500', 'bg-transparent', 'border-0');
             inactiveBtn.classList.remove('active', 'bg-white', 'text-indigo-600', 'border-b-2', 'border-indigo-600');
             inactiveBtn.classList.add('text-gray-500', 'bg-transparent', 'border-0');
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

    // Hiển thị tên file khi chọn
    document.getElementById('uploadFile').addEventListener('change', function(e) {
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        if (this.files && this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
            fileNameDisplay.classList.remove('hidden');
        } else {
            fileNameDisplay.classList.add('hidden');
        }
    });


    // --- Core Functions ---

    // 1. Render Danh Sách Bài Tập
    function renderAssignmentList() {
        const tableBody = document.getElementById('assignmentTableBody');
        if (!tableBody) return;
        tableBody.innerHTML = ''; 

        if (!Array.isArray(assignmentsData) || assignmentsData.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-gray-500 text-lg">Chưa có bài tập nào được giao.</td></tr>';
            return;
        }

        assignmentsData.forEach((assignment, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-50 hover:bg-gray-50 transition duration-150 ease-in-out';
            
            const dueDateObj = safeParseDate(assignment.han_nop);
            const isValidDate = dueDateObj !== null;
            const isOverdue = isValidDate && (new Date() > dueDateObj);
            const status = String(assignment.trang_thai_final); 

            const formatDate = (dateObj) => {
                if (!isValidDate) return '<span class="text-gray-400">Không thời hạn</span>';
                return dateObj.toLocaleString('vi-VN', { 
                    hour: '2-digit', minute: '2-digit', 
                    day: '2-digit', month: '2-digit', year: 'numeric' 
                });
            };
            const dueDateDisplay = formatDate(dueDateObj);
            
            let actionButtonHtml = '';
            if (status.startsWith('Hoàn Thành') || status.startsWith('Đã Nộp') || status.startsWith('Chờ Chấm')) {
                // Nút Xem Lại (Màu xanh dương)
                actionButtonHtml = `<button class="bg-blue-100 text-blue-700 py-1.5 px-4 rounded-lg hover:bg-blue-200 text-xs font-bold transition action-button" 
                                        data-id="${assignment.ma_bai_tap}" 
                                        data-action="xem-lai">
                                        <i class="bi bi-eye-fill mr-1"></i> Xem Lại
                                    </button>`;
            } else if (status.startsWith('Quá Hạn') || isOverdue) {
                // Nút Quá Hạn (Xám)
                actionButtonHtml = `<button class="bg-gray-100 text-gray-400 py-1.5 px-4 rounded-lg text-xs font-bold cursor-not-allowed border" disabled>Hết Hạn</button>`;
            } else if (status.startsWith('Chưa Làm')) {
                // Nút Làm Bài (Tím đậm)
                actionButtonHtml = `<button class="bg-indigo-600 text-white py-1.5 px-4 rounded-lg hover:bg-indigo-700 text-xs font-bold shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 action-button" 
                                        data-id="${assignment.ma_bai_tap}" 
                                        data-action="lam-bai">
                                        Làm Ngay
                                    </button>`;
            } else {
                 actionButtonHtml = `<button class="bg-gray-100 text-gray-400 py-1.5 px-3 rounded text-xs" disabled>Lỗi</button>`;
            }
            
            row.innerHTML = `
                <td class="py-4 px-4 text-center font-bold text-gray-400">${index + 1}</td>
                <td class="py-4 px-4 font-bold text-gray-800">${escapeHtml(assignment.ten_bai_tap)}</td>
                <td class="py-4 px-4 text-gray-600 hidden md:table-cell">${escapeHtml(assignment.ten_mon_hoc || 'N/A')}</td>
                <td class="py-4 px-4 text-gray-600 text-sm">${getAssignmentTypeDisplay(assignment.loai_bai_tap)}</td>
                <td class="py-4 px-4 ${isOverdue && !status.startsWith('Đã') && !status.startsWith('Hoàn') ? 'text-red-600 font-bold' : 'text-gray-600'}">${dueDateDisplay}</td>
                <td class="py-4 px-4">${getStatusBadge(status)}</td>
                <td class="py-4 px-4 text-center">${actionButtonHtml}</td>
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
                } else { 
                    console.error("Assignment not found with ID:", id); 
                    alert("Lỗi dữ liệu. Vui lòng tải lại trang!");
                }
            });
        });
    }

    // 2. Mở Modal Chi Tiết (Bắt đầu làm bài)
    function openModal(assignment) {
        currentAssignment = assignment;
        document.getElementById('modalSubject').textContent = escapeHtml(assignment.ten_mon_hoc || 'N/A');
        document.getElementById('modalAssignmentName').textContent = escapeHtml(assignment.ten_bai_tap);
        document.getElementById('modalAssignmentTypeDisplay').textContent = getAssignmentTypeDisplay(assignment.loai_bai_tap);
        
        const assignedDateObj = safeParseDate(assignment.ngay_giao);
        document.getElementById('modalAssignedDate').textContent = assignedDateObj ? assignedDateObj.toLocaleDateString('vi-VN') : 'N/A';
        
        const dueDateObj = safeParseDate(assignment.han_nop);
        const dueDateDisplay = dueDateObj ? dueDateObj.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) : 'Không giới hạn';
        document.getElementById('modalDueDate').textContent = dueDateDisplay;
        
        document.getElementById('modalContent').innerHTML = assignment.mo_ta ? escapeHtml(assignment.mo_ta).replace(/\n/g, '<br>') : '<i class="text-gray-400">Không có mô tả chi tiết.</i>';

        const attachmentLink = document.getElementById('modalAttachment');
        if (assignment.file_dinh_kem) {
            // attachmentLink.href = `${BASE_URL}/public/${assignment.file_dinh_kem}`;
            attachmentLink.href = `${BASE_URL}/${assignment.file_dinh_kem}`;
             // Thêm /public/ nếu file trong thư mục public
            attachmentLink.textContent = assignment.file_dinh_kem.split('/').pop();
            attachmentLink.closest('div').parentElement.style.display = 'flex'; // Hiện block
        } else {
            attachmentLink.closest('div').parentElement.style.display = 'none'; // Ẩn block
        }

        const startBtn = document.getElementById('startAssignmentBtn');
        const isOverdue = dueDateObj && (new Date() > dueDateObj);
        
        // Reset nút
        startBtn.classList.remove('hidden', 'bg-gray-400', 'cursor-not-allowed', 'bg-indigo-600', 'hover:bg-indigo-700');
        startBtn.disabled = false;

        if (isOverdue) {
            startBtn.textContent = 'Đã Hết Hạn';
            startBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            startBtn.disabled = true;
        } else {
            startBtn.textContent = 'Bắt Đầu Làm Bài';
            startBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            startBtn.onclick = async () => {
                closeModal('assignmentModal'); 
                if (assignment.loai_bai_tap === 'TracNghiem') {
                    // Gọi API bắt đầu làm bài để lấy giờ server
                    try {
                        const startResponse = await fetch(`${BASE_URL}/baitap/batDauLamBai/${assignment.ma_bai_tap}`);
                        const startResult = await startResponse.json();
                        if (!startResult.success) { throw new Error(startResult.message); }
                        
                        // Reload chi tiết để lấy gio_bat_dau chuẩn
                        const detailResponse = await fetch(`${BASE_URL}/baitap/getChiTiet/${assignment.ma_bai_tap}`);
                        const detailResult = await detailResponse.json();
                        if (detailResult.success) { currentAssignment = detailResult.assignment; }
                        
                        loadMultipleChoiceSection(currentAssignment);
                    } catch (err) {
                        alert(`Không thể bắt đầu làm bài: ${err.message}`);
                    }
                } else {
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
        }
    }

    // 3. Mở Modal Xem Lại Bài Nộp
    async function openSubmissionDetailModal(assignment) {
        currentAssignment = assignment; 
        const modal = document.getElementById('submissionDetailModal');
        const loadingDiv = document.getElementById('submissionModalLoading');
        const contentDiv = document.getElementById('submissionModalContent');
        const deleteBtn = document.getElementById('deleteSubmissionBtn');

        // Reset modal state
        loadingDiv.style.display = 'block';
        contentDiv.style.display = 'none';
        deleteBtn.classList.add('hidden');
        document.getElementById('deleteSubmissionNotification').innerHTML = '';
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Hủy Bài Nộp (Làm lại)';

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Lấy ma_bai_nop từ object JS (đã được update khi nộp hoặc render)
        // Nếu API danh sách chưa trả về ma_bai_nop, ta phải dựa vào API getBaiNopChiTietApi để tìm
        // Tuy nhiên ở đây code JS renderAssignmentList đã dùng assignment.ma_bai_nop
        // Nếu assignment.ma_bai_nop undefined, ta sẽ thử dùng ma_bai_nop từ lần nộp trước (nếu có)
        
        let submissionId = assignment.ma_bai_nop;
        
        // Nếu không có ID bài nộp, ta cần một API khác hoặc logic khác. 
        // Nhưng logic hiện tại của bạn là render danh sách đã bao gồm ma_bai_nop (từ PHP).
        // Nếu không có, ta báo lỗi.
        if (!submissionId) {
             loadingDiv.innerHTML = '<p class="text-red-500 font-semibold">Lỗi dữ liệu: Không tìm thấy mã bài nộp.</p>';
             return;
        }

        try {
            // Gọi API lấy chi tiết bài nộp
            const response = await fetch(`${BASE_URL}/baitap/getBaiNopChiTietApi?ma_bai_nop=${submissionId}`);
            if (!response.ok) throw new Error('Lỗi kết nối server');
            
            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            const subData = result.data;

            // --- ĐIỀN DỮ LIỆU VÀO MODAL ---
            document.getElementById('sub_modal_title').textContent = subData.ten_bai_tap;
            document.getElementById('sub_modal_ngay_nop').textContent = subData.ngay_nop_vietnam || 'N/A';
            
            // Điểm số
            const diemElem = document.getElementById('sub_modal_diem_so');
            if (subData.diem_so !== null) {
                diemElem.textContent = subData.diem_so;
                diemElem.className = 'text-3xl font-black text-indigo-600 bg-white px-4 py-2 rounded shadow-sm border';
            } else {
                diemElem.textContent = '--';
                diemElem.className = 'text-3xl font-black text-gray-300 bg-gray-50 px-4 py-2 rounded border border-dashed';
            }

            // Nhận xét (Quan trọng: hiển thị lời phê)
            const nhanXetElem = document.getElementById('sub_modal_nhan_xet');
            if (subData.nhan_xet) {
                nhanXetElem.textContent = subData.nhan_xet;
                nhanXetElem.classList.remove('italic', 'text-gray-400');
                nhanXetElem.classList.add('text-gray-800');
            } else {
                nhanXetElem.textContent = 'Giáo viên chưa có nhận xét.';
                nhanXetElem.classList.add('italic', 'text-gray-400');
                nhanXetElem.classList.remove('text-gray-800');
            }

            // Ẩn hết các phần nội dung trước khi bật
            document.getElementById('sub_modal_file_wrapper').style.display = 'none';
            document.getElementById('sub_modal_essay_wrapper').style.display = 'none';
            document.getElementById('sub_modal_mcq_wrapper').style.display = 'none';

            // Logic hiển thị nội dung bài làm
            if (subData.loai_bai_tap === 'UploadFile') {
                const fileWrapper = document.getElementById('sub_modal_file_wrapper');
                fileWrapper.style.display = 'flex';
                if (subData.file_nop) {
                    const fileName = subData.file_nop.split('/').pop();
                    document.getElementById('sub_modal_file_name').textContent = fileName;
                    document.getElementById('sub_modal_file_link').href = `${BASE_URL}/baitap/downloadBaiNopApi?ma_bai_nop=${submissionId}`;
                    document.getElementById('sub_modal_download_btn').href = `${BASE_URL}/baitap/downloadBaiNopApi?ma_bai_nop=${submissionId}`;
                } else {
                    document.getElementById('sub_modal_file_name').textContent = "Lỗi: File không tồn tại";
                }
            } 
            else if (subData.loai_bai_tap === 'TuLuan') {
                document.getElementById('sub_modal_essay_wrapper').style.display = 'block';
                document.getElementById('sub_modal_essay_text').textContent = subData.noi_dung_tra_loi || '(Trống)';
            } 
            else if (subData.loai_bai_tap === 'TracNghiem') {
                document.getElementById('sub_modal_mcq_wrapper').style.display = 'block';
                // Parse câu hỏi và đáp án
                let questions = [];
                let userAnswers = {};
                try {
                    const qData = JSON.parse(subData.danh_sach_cau_hoi);
                    questions = qData.questions || [];
                    userAnswers = JSON.parse(subData.noi_dung_tra_loi || '{}');
                } catch (e) { console.error("Parse JSON Error", e); }
                
                renderMCQReview(questions, userAnswers);
            }

            // Xử lý nút Hủy Bài (Chỉ hiện khi chưa chấm)
            if (String(subData.trang_thai) !== 'HoanThanh') {
                deleteBtn.classList.remove('hidden');
                deleteBtn.dataset.id = subData.ma_bai_tap; // API hủy cần ma_bai_tap
            } else {
                deleteBtn.classList.add('hidden');
            }

            loadingDiv.style.display = 'none';
            contentDiv.style.display = 'block';

        } catch (err) {
            console.error(err);
            loadingDiv.innerHTML = `<div class="text-red-500 font-bold p-4 bg-red-50 rounded border border-red-200">Lỗi: ${err.message}</div>`;
        }
    }
    
    // Render đáp án trắc nghiệm khi xem lại
    function renderMCQReview(questions, userAnswers) {
         const container = document.getElementById('sub_modal_mcq_answers');
         container.innerHTML = '';
         if(!questions.length) { container.innerHTML = '<p class="text-gray-500">Không có dữ liệu câu hỏi.</p>'; return; }
         
         questions.forEach((q, idx) => {
             const uAns = userAnswers[`q${q.id}`] || null;
             const isCorrect = uAns === q.correct;
             let colorClass = uAns ? (isCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') : 'bg-gray-50 border-gray-200';
             let icon = uAns ? (isCorrect ? '<i class="bi bi-check-circle-fill text-green-600"></i>' : '<i class="bi bi-x-circle-fill text-red-600"></i>') : '<span class="text-gray-400">Bỏ qua</span>';
             
             container.innerHTML += `
                <div class="p-3 border rounded-lg ${colorClass} text-sm">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-gray-700">Câu ${idx + 1}: ${escapeHtml(q.text)}</span>
                        <span>${icon}</span>
                    </div>
                    <div class="ml-2 text-gray-600">
                        Bạn chọn: <span class="font-bold">${uAns || 'Không chọn'}</span> 
                        ${!isCorrect && uAns ? `<span class="text-green-600 ml-2">(Đúng: ${q.correct})</span>` : ''}
                    </div>
                </div>
             `;
         });
    }
    

    // 4. Load Trang Trắc Nghiệm
    async function loadMultipleChoiceSection(assignment) {
        switchSection('multipleChoiceSection');
        document.getElementById('mcqAssignmentName').textContent = escapeHtml(assignment.ten_bai_tap);
        const instructionsDiv = document.getElementById('mcqInstructions');
        const timerBar = document.getElementById('timerBar');
        const timerDisplay = document.getElementById('countdownTimer');

        // Reset
        instructionsDiv.querySelector('div').innerHTML = '';
        timerBar.style.display = 'none';
        timerDisplay.textContent = '--:--';
        timerDisplay.classList.remove('warning');
        delete timerDisplay.dataset.alerted; 

        document.getElementById('mcqNotification').innerHTML = '';
        document.getElementById('questionsContainer').innerHTML = '';
        document.getElementById('mcqLoading').style.display = 'block';
        document.getElementById('questionsContainer').style.display = 'none';
        document.getElementById('submitMultipleChoice').disabled = true;

        if (countdownInterval) clearInterval(countdownInterval);

        try {
            // Dữ liệu assignment đã có sẵn questions do gọi API getChiTiet trước đó
            let questions = [];
            
            // Xử lý parse JSON câu hỏi (an toàn)
            if (assignment.questions) {
                questions = assignment.questions;
            } else if (assignment.content) {
                try {
                    const parsed = JSON.parse(assignment.content);
                    questions = parsed.questions || [];
                } catch (e) {
                    throw new Error("Lỗi định dạng câu hỏi từ server.");
                }
            }
            // Gán lại để dùng lúc nộp
            currentAssignment.questions = questions;

            // Timer Setup
            const duration = parseInt(assignment.thoi_gian_lam_bai) || 0;
            const startTimeStr = assignment.gio_bat_dau_lam_bai;
            
            if (!duration || !startTimeStr) throw new Error("Thiếu thông tin thời gian làm bài.");
            
            const startTime = safeParseDate(startTimeStr);
            const endTime = new Date(startTime.getTime() + duration * 60000);
            
            timerBar.style.display = 'flex'; // Hiện thanh timer

            // Hàm đếm ngược
            const updateTimer = () => {
                const now = new Date();
                const diff = endTime - now;
                
                if (diff <= 0) {
                    clearInterval(countdownInterval);
                    timerDisplay.textContent = "00:00";
                    submitMcqForm(true); // Auto submit
                } else {
                    const m = Math.floor(diff / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    timerDisplay.textContent = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
                    
                    if (m < 5) timerDisplay.classList.add('warning');
                    else timerDisplay.classList.remove('warning');
                }
            };
            
            updateTimer(); // Chạy ngay
            countdownInterval = setInterval(updateTimer, 1000);

            // Render UI
            const qContainer = document.getElementById('questionsContainer');
            qContainer.innerHTML = '';
            
            instructionsDiv.querySelector('div').innerHTML = `
                <p class="font-bold text-yellow-900">Lưu ý quan trọng:</p>
                <ul class="list-disc ml-5 mt-1 space-y-1">
                    <li>Thời gian làm bài: <strong>${duration} phút</strong>.</li>
                    <li>Hệ thống sẽ tự động nộp bài khi hết giờ.</li>
                    <li>Không thoát trình duyệt khi đang làm bài.</li>
                </ul>
            `;

            if(questions.length === 0) {
                qContainer.innerHTML = '<div class="text-center text-red-500">Đề bài chưa có câu hỏi.</div>';
            } else {
                questions.forEach((q, idx) => {
                    const div = document.createElement('div');
                    div.className = 'bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm';
                    div.innerHTML = `
                        <p class="font-bold text-gray-800 text-lg mb-4"><span class="text-indigo-600">Câu ${idx+1}:</span> ${escapeHtml(q.text)}</p>
                        <div class="space-y-3 pl-2">
                            ${(q.options || []).map(opt => `
                                <label class="flex items-center p-3 rounded border border-transparent hover:bg-white hover:border-gray-200 cursor-pointer transition">
                                    <input type="radio" name="q${q.id}" value="${String(opt).charAt(0)}" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-3 text-gray-700">${escapeHtml(opt)}</span>
                                </label>
                            `).join('')}
                        </div>
                    `;
                    qContainer.appendChild(div);
                });
            }

            document.getElementById('mcqLoading').style.display = 'none';
            qContainer.style.display = 'block';
            document.getElementById('submitMultipleChoice').disabled = false;

        } catch (err) {
            console.error(err);
            document.getElementById('mcqLoading').innerHTML = `<div class="text-red-500 font-bold">Lỗi tải đề: ${err.message}</div>`;
            if (countdownInterval) clearInterval(countdownInterval);
        }
    }

    // 5. Load Trang Tự Luận/Upload (Giữ nguyên logic)
    function loadEssaySection(assignment) {
        currentAssignment = assignment; 
        switchSection('essaySection');
        
        const dueDateObj = safeParseDate(assignment.han_nop);
        const isOverdue = dueDateObj && (new Date() > dueDateObj);
        
        document.getElementById('essayAssignmentName').textContent = escapeHtml(assignment.ten_bai_tap);
        document.getElementById('essayAssignmentTypeDisplay').textContent = getAssignmentTypeDisplay(assignment.loai_bai_tap);
        
        // Instruction Box
        const instructEl = document.getElementById('essayInstructions');
        instructEl.innerHTML = `
            <h4 class="font-bold text-blue-800 mb-2 flex items-center"><i class="bi bi-card-text mr-2"></i> Yêu Cầu Chi Tiết:</h4>
            <div class="bg-white p-4 rounded border border-blue-100 text-gray-800 whitespace-pre-wrap font-mono text-sm max-h-60 overflow-y-auto shadow-inner mb-3">
                ${escapeHtml(assignment.mo_ta || 'Không có mô tả.')}
            </div>
            <div class="flex items-center text-sm ${isOverdue ? 'text-red-600 font-bold' : 'text-gray-600'}">
                <i class="bi bi-calendar-event mr-2"></i> Hạn nộp: ${dueDateObj ? dueDateObj.toLocaleString('vi-VN') : 'Không giới hạn'}
                ${isOverdue ? '<span class="ml-2 bg-red-100 text-red-800 px-2 py-0.5 rounded text-xs">ĐÃ HẾT HẠN</span>' : ''}
            </div>
        `;

        // Reset Inputs
        document.getElementById('essayAnswerInput').value = ''; 
        document.getElementById('uploadFile').value = '';
        document.getElementById('fileNameDisplay').classList.add('hidden');
        document.getElementById('typingNotification').innerHTML = ''; 
        document.getElementById('uploadNotification').innerHTML = '';

        const submitTypingBtn = document.getElementById('submitTyping');
        const submitUploadBtn = document.getElementById('submitUpload');
        
        if (isOverdue) {
            submitTypingBtn.disabled = true;
            submitUploadBtn.disabled = true;
            submitTypingBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitUploadBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            submitTypingBtn.disabled = false;
            submitUploadBtn.disabled = false;
            submitTypingBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitUploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        setupEssayTabs(); 
    }

    // --- SUBMIT HANDLERS ---

    // Nộp Trắc Nghiệm
    async function submitMcqForm(isAutoSubmit = false) {
        const assignment = currentAssignment;
        if (!assignment) return;

        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        
        const formEl = document.getElementById('mcqForm');
        const formData = new FormData(formEl);
        const answers = {};
        
        // Gom đáp án
        if (assignment.questions) {
            assignment.questions.forEach(q => {
                answers[`q${q.id}`] = formData.get(`q${q.id}`);
            });
        }

        if (!isAutoSubmit) {
            const answered = Object.values(answers).filter(v => v !== null).length;
            const total = assignment.questions.length;
            if (answered < total && !confirm(`Bạn mới làm ${answered}/${total} câu. Chắc chắn nộp?`)) return;
            if (answered === total && !confirm("Xác nhận nộp bài?")) return;
        }
        
        const btn = document.getElementById('submitMultipleChoice');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
        
        try {
            const res = await fetch(`${BASE_URL}/baitap/nopBaiTracNghiem`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ ma_bai_tap: assignment.ma_bai_tap, answers })
            });
            const data = await res.json();
            
            if (data.success) {
                alert(`Nộp thành công! Điểm số: ${data.diem_so}`);
                // Cập nhật trạng thái + ma_bai_nop mới nhận được
                updateAssignmentStatus(assignment.ma_bai_tap, data.newStatus, data.ma_bai_nop);
                switchSection('listSection');
            } else {
                throw new Error(data.message);
            }
        } catch (e) {
            alert("Lỗi nộp bài: " + e.message);
            btn.disabled = false;
            btn.textContent = "Nộp Bài";
        }
    }
    
    document.getElementById('mcqForm').addEventListener('submit', (e) => { e.preventDefault(); submitMcqForm(false); });

    // Nộp Tự Luận (Gõ)
    document.getElementById('typingForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const content = document.getElementById('essayAnswerInput').value.trim();
        if(content.length < 10) { alert("Nội dung quá ngắn."); return; }
        if(!confirm("Xác nhận nộp bài?")) return;

        const btn = document.getElementById('submitTyping');
        btn.disabled = true;
        btn.innerHTML = 'Đang nộp...';

        try {
            const res = await fetch(`${BASE_URL}/baitap/nopBaiGoTrucTiep`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ ma_bai_tap: currentAssignment.ma_bai_tap, noi_dung: content })
            });
            const data = await res.json();
            
            if(data.success) {
                showNotification("Nộp thành công!", 'success');
                updateAssignmentStatus(currentAssignment.ma_bai_tap, data.newStatus, data.ma_bai_nop);
                setTimeout(() => switchSection('listSection'), 1500);
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            showNotification(err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill mr-2"></i> Nộp Bài';
        }
    });

    // Nộp Upload
    document.getElementById('uploadForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fileInput = document.getElementById('uploadFile');
        if(!fileInput.files.length) { alert("Chưa chọn file."); return; }
        if(!confirm("Xác nhận nộp file này?")) return;

        const btn = document.getElementById('submitUpload');
        btn.disabled = true;
        btn.innerHTML = 'Đang tải lên...';

        const fd = new FormData();
        fd.append('ma_bai_tap', currentAssignment.ma_bai_tap);
        fd.append('file_bai_lam', fileInput.files[0]);

        try {
            const res = await fetch(`${BASE_URL}/baitap/nopBaiUpload`, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success) {
                showNotification("Upload thành công!", 'success', 'uploadNotification');
                updateAssignmentStatus(currentAssignment.ma_bai_tap, data.newStatus, data.ma_bai_nop);
                setTimeout(() => switchSection('listSection'), 1500);
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            showNotification(err.message, 'error', 'uploadNotification');
            btn.disabled = false;
            btn.innerHTML = 'Xác Nhận Nộp File';
        }
    });

    // Hủy Bài Nộp
    document.getElementById('deleteSubmissionBtn').addEventListener('click', async (e) => {
        if(!confirm("Hủy bài nộp để làm lại? (Dữ liệu cũ sẽ mất)")) return;
        const btn = e.target;
        const id = btn.dataset.id; // ma_bai_tap
        
        try {
            const res = await fetch(`${BASE_URL}/baitap/huyBaiNop`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ ma_bai_tap: id })
            });
            const data = await res.json();
            
            if(data.success) {
                alert("Đã hủy bài nộp.");
                updateAssignmentStatus(id, 'Chưa Làm'); // Reset trạng thái
                closeModal('submissionDetailModal');
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            alert(err.message);
        }
    });

    // Init
    document.addEventListener('DOMContentLoaded', () => { renderAssignmentList(); });
</script>
</body>
</html>