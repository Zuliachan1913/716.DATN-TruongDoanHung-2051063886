<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';
include '../Includes/chatbot_config.php';

function safeText($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

function searchStudents($keyword, $conn) {
    $keyword = $conn->real_escape_string($keyword);
    $sql = "SELECT firstName, lastName, admissionNumber, classId, classArmId FROM tblstudents ";
    $sql .= "WHERE (firstName LIKE '%$keyword%' OR lastName LIKE '%$keyword%' OR admissionNumber LIKE '%$keyword%') ";
    $sql .= "AND classId = '".$_SESSION['classId']."' AND classArmId = '".$_SESSION['classArmId']."' LIMIT 8";
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

function getStudentCount($conn) {
    $query = "SELECT COUNT(*) AS total FROM tblstudents WHERE classId = '".$_SESSION['classId']."' AND classArmId = '".$_SESSION['classArmId']."'";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

function getAttendanceCount($conn) {
    $query = "SELECT COUNT(*) AS total FROM tblattendance WHERE classId = '".$_SESSION['classId']."' AND classArmId = '".$_SESSION['classArmId']."'";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

function getClassInfo($conn) {
    $query = "SELECT tblclass.className, tblclassarms.classArmName FROM tblclassteacher ";
    $query .= "INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId ";
    $query .= "INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId ";
    $query .= "WHERE tblclassteacher.Id = '".$_SESSION['userId']."' LIMIT 1";
    $result = $conn->query($query);
    return $result && $result->num_rows ? $result->fetch_assoc() : null;
}

function getStudentList($conn, $limit = 5) {
    $query = "SELECT firstName, lastName, admissionNumber FROM tblstudents WHERE classId = '".$_SESSION['classId']."' AND classArmId = '".$_SESSION['classArmId']."' LIMIT $limit";
    $result = $conn->query($query);
    if (!$result || $result->num_rows === 0) {
        return [];
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function getDbContextSummary($conn) {
    $classInfo = getClassInfo($conn);
    $studentCount = getStudentCount($conn);
    $attendanceCount = getAttendanceCount($conn);
    $students = getStudentList($conn, 5);

    $summary = "Dữ liệu lớp hiện tại:\n";
    if ($classInfo) {
        $summary .= "- Lớp: " . $classInfo['className'] . "\n";
        $summary .= "- Tổ: " . $classInfo['classArmName'] . "\n";
    }
    $summary .= "- Số học sinh: $studentCount\n";
    $summary .= "- Số lần điểm danh: $attendanceCount\n";
    if (!empty($students)) {
        $summary .= "- Một vài học sinh trong lớp:\n";
        foreach ($students as $student) {
            $summary .= "  * " . $student['firstName'] . " " . $student['lastName'] . " (Mã: " . $student['admissionNumber'] . ")\n";
        }
    }
    $summary .= "\nHãy trả lời bằng tiếng Việt, dùng dữ liệu trên nếu câu hỏi liên quan đến lớp hoặc học sinh.";
    return $summary;
}

function callAIModel($message, $conn) {
    if (defined('CHATBOT_PROVIDER') && CHATBOT_PROVIDER === 'gemini' && !empty(CHATBOT_GEMINI_API_KEY)) {
        $model = defined('CHATBOT_GEMINI_MODEL') ? CHATBOT_GEMINI_MODEL : 'gemini-1.5-pro';
        $apiKey = CHATBOT_GEMINI_API_KEY;
        $endpoints = [
            "https://generativelanguage.googleapis.com/v1/models/$model:generateContent?key=$apiKey",
            "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey",
        ];

        foreach ($endpoints as $apiUrl) {
            $payload = [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [[
                        'text' => getDbContextSummary($conn) . "\n\nCâu hỏi: " . trim($message)
                    ]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 500,
                    'topP' => 1.0,
                ],
            ];

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response && $status === 200) {
                $data = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        return trim($data['candidates'][0]['content']['parts'][0]['text']);
                    }
                    if (isset($data['candidates'][0]['content'])) {
                        return trim($data['candidates'][0]['content']);
                    }
                }
                return 'API Gemini trả về định dạng không mong đợi: ' . substr($response, 0, 300);
            }

            if ($response) {
                $data = json_decode($response, true);
                $detail = '';
                if (json_last_error() === JSON_ERROR_NONE && isset($data['error'])) {
                    $detail = ' ' . ($data['error']['message'] ?? json_encode($data['error']));
                } else {
                    $detail = ' ' . substr($response, 0, 300);
                }
                return 'Lỗi Gemini API (HTTP ' . $status . '):' . $detail;
            }

            if (!empty($error)) {
                return 'Lỗi CURL khi gọi Gemini: ' . $error;
            }
        }

        return 'Không thể kết nối tới Gemini API.';
    }

    if (!defined('CHATBOT_PROVIDER') || CHATBOT_PROVIDER !== 'openai' || empty(CHATBOT_OPENAI_API_KEY)) {
        return false;
    }

    $apiUrl = 'https://api.openai.com/v1/chat/completions';
    $payload = [
        'model' => defined('CHATBOT_OPENAI_MODEL') ? CHATBOT_OPENAI_MODEL : 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Bạn là trợ lý ảo cho giáo viên trong hệ thống điểm danh. Trả lời bằng tiếng Việt. Nếu câu hỏi liên quan đến lớp học hoặc học sinh, chỉ sử dụng dữ liệu được cung cấp trong ngữ cảnh. Nếu không, trả lời kiến thức chung một cách lịch sự và ngắn gọn.'
            ],
            [
                'role' => 'user',
                'content' => getDbContextSummary($conn) . "\n\nCâu hỏi: " . trim($message)
            ]
        ],
        'temperature' => 0.4,
        'max_tokens' => 500,
        'top_p' => 1,
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . CHATBOT_OPENAI_API_KEY,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if (!$response || $status !== 200) {
        if (!empty($response)) {
            $data = json_decode($response, true);
            $detail = (json_last_error() === JSON_ERROR_NONE && isset($data['error'])) ? ($data['error']['message'] ?? json_encode($data['error'])) : substr($response, 0, 300);
            return 'Lỗi OpenAI API (HTTP ' . $status . '): ' . $detail;
        }
        return 'Lỗi CURL khi gọi OpenAI: ' . $error;
    }

    $data = json_decode($response, true);
    if (!isset($data['choices'][0]['message']['content'])) {
        return 'OpenAI trả về định dạng không mong đợi: ' . substr($response, 0, 300);
    }

    return trim($data['choices'][0]['message']['content']);
}

function handleChat($message, $conn) {
    $aiReply = callAIModel($message, $conn);
    if ($aiReply !== false) {
        return $aiReply;
    }

    $normalized = mb_strtolower($message, 'UTF-8');
    $help = 'Tôi có thể giúp bạn với: danh sách học sinh, tổng số học sinh, thông tin điểm danh, tìm học sinh theo tên hoặc mã số.';

    if (trim($message) === '') {
        return 'Xin chào! Bạn có thể hỏi: "Tổng số học sinh là bao nhiêu?", "Danh sách học sinh", "Thông tin điểm danh", hoặc "Tìm học sinh Nguyễn".';
    }

    if (strpos($normalized, 'danh sách') !== false && strpos($normalized, 'học sinh') !== false) {
        $count = getStudentCount($conn);
        return "Hiện tại có $count học sinh trong lớp của bạn. Bạn có thể hỏi 'Danh sách học sinh' để nhận 5 học sinh đầu tiên.";
    }

    if (strpos($normalized, 'tổng số') !== false && strpos($normalized, 'học sinh') !== false) {
        $count = getStudentCount($conn);
        return "Số lượng học sinh trong lớp của bạn là $count người.";
    }

    if (strpos($normalized, 'điểm danh') !== false || strpos($normalized, 'attendance') !== false) {
        $count = getAttendanceCount($conn);
        return "Tổng số lần điểm danh của lớp hiện tại là $count.";
    }

    if (strpos($normalized, 'môn') !== false || strpos($normalized, 'lớp') !== false) {
        $classInfo = getClassInfo($conn);
        if ($classInfo) {
            return 'Lớp của bạn: ' . $classInfo['className'] . ' - ' . $classInfo['classArmName'] . '.';
        }
        return 'Tôi chưa lấy được thông tin lớp của bạn.';
    }

    if (preg_match('/\b(tìm|search|tim)\b/i', $message) || preg_match('/\b(người|học sinh)\b/i', $message)) {
        $keyword = preg_replace('/.*\b(tìm|search|tim|người|học sinh)\b/i', '', $message);
        $keyword = trim($keyword);
        if ($keyword === '') {
            return 'Hãy nhập tên hoặc mã số học sinh để tìm.';
        }
        $students = searchStudents($keyword, $conn);
        if (!$students) {
            return 'Không tìm thấy học sinh nào khớp với "' . safeText($keyword) . '" trong lớp của bạn.';
        }
        $output = 'Tìm thấy ' . count($students) . ' học sinh:\n';
        foreach ($students as $student) {
            $output .= '- ' . $student['firstName'] . ' ' . $student['lastName'] . ' (Mã: ' . $student['admissionNumber'] . ')\n';
        }
        return trim($output);
    }

    return $help;
}

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
  <title>Chatbot AI</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/admin.min.css" rel="stylesheet">
  <style>
    .chatbot-card { min-height: 560px; }
    .chat-window { display: flex; flex-direction: column; height: 100%; }
    .chat-messages { flex: 1; overflow-y: auto; padding: 1rem; background: #f8f9fc; border: 1px solid #dee2e6; border-radius: .35rem; }
    .chat-message { margin-bottom: 1rem; max-width: 85%; }
    .chat-message.user { align-self: flex-end; text-align: right; }
    .chat-message.bot { align-self: flex-start; text-align: left; }
    .chat-bubble { display: inline-block; padding: .75rem 1rem; border-radius: 1rem; font-size: .95rem; line-height: 1.4; }
    .chat-bubble.user { background: #4e73df; color: #fff; }
    .chat-bubble.bot { background: #e2e8f0; color: #212529; }
    .chat-input-group { margin-top: 1rem; }
    .chat-hero { margin-bottom: 1rem; }
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
            <h1 class="h3 mb-0 text-gray-800">Chatbot AI</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Chatbot AI</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-xl-12">
              <div class="card chatbot-card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-robot"></i> Chat với trợ lý AI</h6>
                </div>
                <div class="card-body chat-window">
                  <div class="chat-hero">
                    <p>Hỏi tôi về học sinh, điểm danh, lớp học hoặc tìm học sinh theo tên/mã số.</p>
                  </div>
                  <div id="chatMessages" class="chat-messages"></div>
                  <form id="chatForm" class="chat-input-group">
                    <div class="input-group">
                      <input id="chatInput" type="text" class="form-control" placeholder="Nhập câu hỏi của bạn..." aria-label="Chat message">
                      <div class="input-group-append">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Gửi</button>
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

    function addMessage(type, text) {
      const wrapper = document.createElement('div');
      wrapper.className = 'chat-message ' + type;
      const bubble = document.createElement('div');
      bubble.className = 'chat-bubble ' + type;
      bubble.innerText = text;
      wrapper.appendChild(bubble);
      chatMessages.appendChild(wrapper);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendChat(message) {
      addMessage('user', message);
      fetch('chatbot.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body: 'message=' + encodeURIComponent(message)
      })
      .then(response => response.text().then(text => {
        try {
          const data = JSON.parse(text);
          addMessage('bot', data.reply || text);
        } catch (err) {
          addMessage('bot', 'Lỗi máy chủ: ' + text);
        }
      }))
      .catch(error => addMessage('bot', 'Xin lỗi, đã xảy ra lỗi khi gửi câu hỏi.'));
    }

    chatForm.addEventListener('submit', function(event) {
      event.preventDefault();
      const message = chatInput.value.trim();
      if (!message) {
        return;
      }
      chatInput.value = '';
      sendChat(message);
    });

    addMessage('bot', 'Chào bạn! Tôi là Chatbot AI. Bạn có thể hỏi về danh sách học sinh, điểm danh, lớp, hoặc tìm học sinh theo tên/mã số.');
  </script>
</body>
</html>
