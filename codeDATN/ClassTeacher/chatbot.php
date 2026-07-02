<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';
include '../Includes/chatbot_config.php';

function safeText($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

/**
 * Lấy thông tin sinh viên đã điểm danh theo lớp của giáo viên
 */
function getAttendanceByDate($dateFilter = null, $conn) {
    $classId = $conn->real_escape_string($_SESSION['classId']);
    $classArmId = $conn->real_escape_string($_SESSION['classArmId']);
    
    $sql = "SELECT DISTINCT a.dateTimeTaken, 
                   s.firstName, s.lastName, s.admissionNumber,
                   a.status
            FROM tblattendance a
            INNER JOIN tblstudents s ON s.admissionNumber = a.admissionNo
            WHERE a.classId = '$classId' AND a.classArmId = '$classArmId'";
    
    if ($dateFilter) {
        $dateFilter = $conn->real_escape_string($dateFilter);
        $sql .= " AND a.dateTimeTaken = '$dateFilter'";
    } else {
        // Lấy điểm danh hôm nay hoặc gần nhất
        $sql .= " ORDER BY a.dateTimeTaken DESC LIMIT 50";
    }
    
    $result = $conn->query($sql);
    if (!$result || $result->num_rows === 0) {
        return [];
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Lấy danh sách ngày điểm danh của lớp
 */
function getAttendanceDates($conn) {
    $classId = $conn->real_escape_string($_SESSION['classId']);
    $classArmId = $conn->real_escape_string($_SESSION['classArmId']);
    
    $sql = "SELECT DISTINCT dateTimeTaken FROM tblattendance 
            WHERE classId = '$classId' AND classArmId = '$classArmId'
            ORDER BY dateTimeTaken DESC LIMIT 10";
    
    $result = $conn->query($sql);
    if (!$result || $result->num_rows === 0) {
        return [];
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row['dateTimeTaken'];
    }
    return $rows;
}

/**
 * Tìm sinh viên theo tên hoặc mã số
 */
function searchStudents($keyword, $conn) {
    $keyword = $conn->real_escape_string($keyword);
    $classId = $conn->real_escape_string($_SESSION['classId']);
    $classArmId = $conn->real_escape_string($_SESSION['classArmId']);
    
    $sql = "SELECT firstName, lastName, admissionNumber FROM tblstudents ";
    $sql .= "WHERE (firstName LIKE '%$keyword%' OR lastName LIKE '%$keyword%' OR admissionNumber LIKE '%$keyword%') ";
    $sql .= "AND classId = '$classId' AND classArmId = '$classArmId' LIMIT 8";
    
    $result = $conn->query($sql);
    if (!$result || $result->num_rows === 0) {
        return null;
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Đếm số sinh viên của lớp
 */
function getStudentCount($conn) {
    $classId = $conn->real_escape_string($_SESSION['classId']);
    $classArmId = $conn->real_escape_string($_SESSION['classArmId']);
    
    $query = "SELECT COUNT(*) AS total FROM tblstudents 
              WHERE classId = '$classId' AND classArmId = '$classArmId'";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

/**
 * Đếm số lần điểm danh của lớp
 */
function getAttendanceCount($conn) {
    $classId = $conn->real_escape_string($_SESSION['classId']);
    $classArmId = $conn->real_escape_string($_SESSION['classArmId']);
    
    $query = "SELECT COUNT(DISTINCT dateTimeTaken) AS total FROM tblattendance 
              WHERE classId = '$classId' AND classArmId = '$classArmId'";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

/**
 * Lấy thông tin lớp hiện tại
 */
function getClassInfo($conn) {
    $userId = $conn->real_escape_string($_SESSION['userId']);
    
    $query = "SELECT tblclass.className, tblclassarms.classArmName 
              FROM tblclassteacher 
              INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId 
              INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId 
              WHERE tblclassteacher.Id = '$userId' LIMIT 1";
    
    $result = $conn->query($query);
    return $result && $result->num_rows ? $result->fetch_assoc() : null;
}

/**
 * Tạo context tối ưu cho Gemini - chỉ gửi dữ liệu cần thiết
 * Phân loại câu hỏi để gửi đúng dữ liệu
 */
function getOptimizedDbContext($message, $conn) {
    $normalized = mb_strtolower($message, 'UTF-8');
    
    $classInfo = getClassInfo($conn);
    $studentCount = getStudentCount($conn);
    $attendanceCount = getAttendanceCount($conn);
    
    $context = "🏫 Thông tin lớp học:\n";
    if ($classInfo) {
        $context .= "- Lớp: " . $classInfo['className'] . "\n";
        $context .= "- Tổ: " . $classInfo['classArmName'] . "\n";
    }
    $context .= "- Tổng sinh viên: $studentCount\n";
    $context .= "- Lần điểm danh: $attendanceCount\n";
    
    // Nếu hỏi về điểm danh hôm nay hoặc ngày cụ thể
    if (preg_match('/\b(hôm nay|ngày|điểm danh)\b/i', $message)) {
        // Kiểm tra xem có ngày cụ thể không
        $date = null;
        if (preg_match('/(\d{2}[-\/]\d{2}[-\/]\d{4}|\d{4}[-\/]\d{2}[-\/]\d{2})/', $message, $matches)) {
            $date = $matches[1];
            $date = str_replace('/', '-', $date); // Chuẩn hóa format
        }
        
        $attendanceData = getAttendanceByDate($date, $conn);
        if (!empty($attendanceData)) {
            $context .= "\n📋 Thông tin điểm danh:\n";
            $groupedByDate = [];
            foreach ($attendanceData as $record) {
                $groupedByDate[$record['dateTimeTaken']][] = $record;
            }
            
            // Giới hạn chỉ hiển thị 5 ngày gần nhất
            $dateCount = 0;
            foreach (array_slice($groupedByDate, 0, 5) as $date => $records) {
                $context .= "  📅 " . $date . ":\n";
                foreach (array_slice($records, 0, 10) as $record) { // Max 10 sinh viên/ngày
                    $context .= "    • " . $record['firstName'] . " " . $record['lastName'] . 
                               " (" . $record['admissionNumber'] . ") - " . $record['status'] . "\n";
                }
                if (count($records) > 10) {
                    $context .= "    • ... và " . (count($records) - 10) . " sinh viên khác\n";
                }
                $dateCount++;
            }
        }
    }
    // Nếu tìm sinh viên cụ thể
    else if (preg_match('/\b(tìm|search|tim|tên)\b/i', $message)) {
        $keyword = preg_replace('/.*\b(tìm|search|tim|tên|người|sinh viên)\b/i', '', $message);
        $keyword = trim($keyword);
        
        if (!empty($keyword)) {
            $students = searchStudents($keyword, $conn);
            if ($students && count($students) > 0) {
                $context .= "\n🔍 Kết quả tìm kiếm \"$keyword\":\n";
                foreach (array_slice($students, 0, 8) as $student) {
                    $context .= "  • " . $student['firstName'] . " " . $student['lastName'] . 
                               " (Mã: " . $student['admissionNumber'] . ")\n";
                }
            }
        }
    }
    
    $context .= "\nHãy trả lời bằng tiếng Việt. Nếu câu hỏi liên quan đến lớp, sinh viên hay điểm danh, hãy dùng dữ liệu trên.";
    return $context;
}

/**
 * Gọi API Gemini hoặc OpenAI
 */
function callAIModel($message, $conn) {
    if (defined('CHATBOT_PROVIDER') && CHATBOT_PROVIDER === 'gemini' && !empty(CHATBOT_GEMINI_API_KEY)) {
        $model = defined('CHATBOT_GEMINI_MODEL') ? CHATBOT_GEMINI_MODEL : 'gemini-1.5-flash';
        $apiKey = CHATBOT_GEMINI_API_KEY;
        $timeout = defined('CHATBOT_API_TIMEOUT') ? CHATBOT_API_TIMEOUT : 30;
        
        $endpoints = [
            "https://generativelanguage.googleapis.com/v1/models/$model:generateContent?key=$apiKey",
            "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey",
        ];

        foreach ($endpoints as $apiUrl) {
            $optimizedContext = getOptimizedDbContext($message, $conn);
            
            $payload = [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [[
                        'text' => $optimizedContext . "\n\nCâu hỏi: " . trim($message)
                    ]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 500,
                    'topP' => 0.95,
                ],
            ];

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            // Xử lý phản hồi thành công
            if ($response && $status === 200) {
                $data = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($data['candidates'][0]['content']['parts'][0]['text']);
                }
                return 'API Gemini trả về định dạng không mong đợi';
            }

            // Xử lý lỗi API
            if ($response) {
                $data = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['error'])) {
                    $errorMsg = $data['error']['message'] ?? 'Lỗi không xác định';
                    if ($status === 401) {
                        return '❌ Lỗi xác thực API (HTTP 401): API key không hợp lệ. Vui lòng kiểm tra lại API key tại https://aistudio.google.com/apikey';
                    }
                    return "❌ Lỗi Gemini API (HTTP $status): $errorMsg";
                }
            }

            if (!empty($error)) {
                return "❌ Lỗi kết nối: $error";
            }
        }

        return '❌ Không thể kết nối tới Gemini API sau nhiều lần thử.';
    }

    // Fallback to OpenAI
    if (!defined('CHATBOT_PROVIDER') || CHATBOT_PROVIDER !== 'openai' || empty(CHATBOT_OPENAI_API_KEY)) {
        return false;
    }

    $apiUrl = 'https://api.openai.com/v1/chat/completions';
    $optimizedContext = getOptimizedDbContext($message, $conn);
    $timeout = defined('CHATBOT_API_TIMEOUT') ? CHATBOT_API_TIMEOUT : 30;
    
    $payload = [
        'model' => defined('CHATBOT_OPENAI_MODEL') ? CHATBOT_OPENAI_MODEL : 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Bạn là trợ lý ảo cho giáo viên trong hệ thống điểm danh. Trả lời bằng tiếng Việt.'
            ],
            [
                'role' => 'user',
                'content' => $optimizedContext . "\n\nCâu hỏi: " . trim($message)
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 500,
        'top_p' => 0.95,
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . CHATBOT_OPENAI_API_KEY,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if (!$response || $status !== 200) {
        if (!empty($response)) {
            $data = json_decode($response, true);
            $detail = (json_last_error() === JSON_ERROR_NONE && isset($data['error'])) ? ($data['error']['message'] ?? json_encode($data['error'])) : substr($response, 0, 300);
            return "❌ Lỗi OpenAI (HTTP $status): $detail";
        }
        return "❌ Lỗi kết nối: $error";
    }

    $data = json_decode($response, true);
    if (!isset($data['choices'][0]['message']['content'])) {
        return 'OpenAI trả về định dạng không mong đợi';
    }

    return trim($data['choices'][0]['message']['content']);
}

/**
 * Xử lý câu hỏi - ưu tiên AI, fallback local logic
 */
function handleChat($message, $conn) {
    $aiReply = callAIModel($message, $conn);
    if ($aiReply !== false) {
        return $aiReply;
    }

    // Fallback local logic nếu AI không khả dụng
    $normalized = mb_strtolower($message, 'UTF-8');

    if (trim($message) === '') {
        return '👋 Xin ch��o! Bạn có thể hỏi:\n• "Hôm nay có những sinh viên nào điểm danh?"\n• "Danh sách sinh viên"\n• "Tìm học sinh Nguyễn"\n• "Tổng số sinh viên là bao nhiêu?"';
    }

    if (preg_match('/\b(hôm nay|ngày|điểm danh|attendance)\b/i', $message)) {
        $attendance = getAttendanceByDate(null, $conn);
        if (!empty($attendance)) {
            $grouped = [];
            foreach ($attendance as $record) {
                $grouped[$record['dateTimeTaken']][] = $record;
            }
            $response = "📋 **Thông tin điểm danh:**\n";
            foreach (array_slice($grouped, 0, 3) as $date => $records) {
                $response .= "**$date:**\n";
                foreach (array_slice($records, 0, 5) as $r) {
                    $response .= "  • " . $r['firstName'] . " " . $r['lastName'] . " - " . $r['status'] . "\n";
                }
                if (count($records) > 5) {
                    $response .= "  • ... và " . (count($records) - 5) . " sinh viên khác\n";
                }
            }
            return $response;
        }
        return "Chưa có thông tin điểm danh.";
    }

    if (preg_match('/\b(danh sách|list|sinh viên)\b/i', $message)) {
        $count = getStudentCount($conn);
        return "Lớp của bạn có tổng cộng **$count sinh viên**.";
    }

    if (preg_match('/\b(tổng|số lượng|bao nhiêu)\b/i', $message)) {
        $count = getStudentCount($conn);
        return "Số lượng sinh viên trong lớp: **$count người**.";
    }

    if (preg_match('/\b(tìm|search|tim)\b/i', $message)) {
        $keyword = preg_replace('/.*\b(tìm|search|tim|tên|người|sinh viên)\b/i', '', $message);
        $keyword = trim($keyword);
        if ($keyword === '') {
            return 'Hãy nhập tên hoặc mã số sinh viên để tìm kiếm.';
        }
        $students = searchStudents($keyword, $conn);
        if (!$students) {
            return "Không tìm thấy sinh viên nào khớp với \"" . safeText($keyword) . "\".";
        }
        $output = "🔍 **Tìm thấy " . count($students) . " sinh viên:**\n";
        foreach ($students as $student) {
            $output .= "• " . $student['firstName'] . " " . $student['lastName'] . " (Mã: " . $student['admissionNumber'] . ")\n";
        }
        return $output;
    }

    return "Tôi có thể giúp bạn với: điểm danh, danh sách sinh viên, tìm sinh viên theo tên/mã số.";
}

// Xử lý AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: application/json; charset=utf-8');
    $reply = handleChat($_POST['message'], $conn);
    echo json_encode(['reply' => $reply]);
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Chatbot AI - Hệ thống điểm danh</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/admin.min.css" rel="stylesheet">
  <style>
    .chatbot-card { min-height: 560px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
    .chat-window { display: flex; flex-direction: column; height: 100%; }
    .chat-messages { 
        flex: 1; 
        overflow-y: auto; 
        padding: 1.5rem; 
        background: linear-gradient(135deg, #f8f9fc 0%, #f0f3f7 100%);
        border: 1px solid #dee2e6; 
        border-radius: .35rem;
    }
    .chat-message { margin-bottom: 1rem; display: flex; animation: slideIn 0.3s ease; }
    .chat-message.user { justify-content: flex-end; }
    .chat-message.bot { justify-content: flex-start; }
    .chat-bubble { 
        display: inline-block; 
        padding: .75rem 1rem; 
        border-radius: 1rem; 
        font-size: .95rem; 
        line-height: 1.4;
        max-width: 80%;
        word-wrap: break-word;
    }
    .chat-bubble.user { background: #4e73df; color: #fff; border-radius: 1rem 0.2rem 0.2rem 1rem; }
    .chat-bubble.bot { background: #e2e8f0; color: #212529; border-radius: 0.2rem 1rem 1rem 0.2rem; }
    .chat-input-group { margin-top: 1rem; }
    .chat-hero { margin-bottom: 1rem; padding: 1rem; background: #e7f3ff; border-left: 4px solid #4e73df; border-radius: 0.25rem; }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .loading-indicator { display: none; text-align: center; padding: 1rem; }
    .loading-indicator.active { display: block; }
  </style>
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-robot text-primary"></i> Chatbot AI</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">Chatbot AI</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-xl-12">
              <div class="card chatbot-card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments"></i> Chat với trợ lý AI</h6>
                  <small class="text-muted">Được hỗ trợ bởi Gemini API</small>
                </div>
                <div class="card-body chat-window">
                  <div class="chat-hero">
                    <p class="mb-0"><strong>💡 Gợi ý:</strong> Hỏi về sinh viên đã điểm danh, danh sách lớp, hoặc tìm sinh viên theo tên/mã số.</p>
                  </div>
                  <div id="chatMessages" class="chat-messages"></div>
                  <div id="loadingIndicator" class="loading-indicator">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                      <span class="sr-only">Đang xử lý...</span>
                    </div>
                  </div>
                  <form id="chatForm" class="chat-input-group">
                    <div class="input-group">
                      <input id="chatInput" type="text" class="form-control" placeholder="Nhập câu hỏi..." aria-label="Chat message" autocomplete="off">
                      <div class="input-group-append">
                        <button class="btn btn-primary" type="submit" id="sendBtn"><i class="fas fa-paper-plane"></i> Gửi</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php include 'includes/footer.php'; ?>
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/admin.min.js"></script>
  <script>
    const chatMessages = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');

    function addMessage(type, text) {
      const wrapper = document.createElement('div');
      wrapper.className = 'chat-message ' + type;
      const bubble = document.createElement('div');
      bubble.className = 'chat-bubble ' + type;
      bubble.innerHTML = text; // Cho phép markdown cơ bản
      wrapper.appendChild(bubble);
      chatMessages.appendChild(wrapper);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendChat(message) {
      addMessage('user', message);
      chatInput.value = '';
      sendBtn.disabled = true;
      loadingIndicator.classList.add('active');

      fetch('chatbot.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body: 'message=' + encodeURIComponent(message)
      })
      .then(response => response.text())
      .then(text => {
        try {
          const data = JSON.parse(text);
          addMessage('bot', data.reply || 'Xin lỗi, đã xảy ra lỗi khi xử lý câu trả lời.');
        } catch (err) {
          addMessage('bot', '❌ Lỗi máy chủ: ' + text.substring(0, 200));
        }
      })
      .catch(error => {
        addMessage('bot', '❌ Không thể gửi câu hỏi. Vui lòng kiểm tra kết nối.');
      })
      .finally(() => {
        sendBtn.disabled = false;
        loadingIndicator.classList.remove('active');
        chatInput.focus();
      });
    }

    chatForm.addEventListener('submit', function(event) {
      event.preventDefault();
      const message = chatInput.value.trim();
      if (!message) return;
      sendChat(message);
    });

    // Welcome message
    addMessage('bot', '👋 Xin chào! Tôi là Chatbot AI của hệ thống điểm danh.<br>Bạn có thể hỏi tôi về:<br>• Sinh viên đã điểm danh hôm nay<br>• Danh sách sinh viên lớp<br>• Tìm sinh viên theo tên/mã số<br>• Thông tin lớp học');
    chatInput.focus();
  </script>
</body>
</html>
