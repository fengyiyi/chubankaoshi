<?php
/**
 * 出版专业技术人员职业资格（中级）考试模拟系统
 * API接口处理文件
 */

session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// 获取请求方法
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $db = getDB();
} catch (Exception $e) {
    echo json_encode(['error' => '数据库连接失败']);
    exit;
}

// 处理登录请求
if ($action === 'login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $remember = $input['remember'] ?? false;
    
    if (empty($username) || empty($password)) {
        echo json_encode(['error' => '用户名和密码不能为空']);
        exit;
    }
    
    $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // 创建Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // 如果勾选记住我，创建Cookie
        if ($remember) {
            createAuthCookie($username);
        }
        
        echo json_encode(['success' => true, 'username' => $user['username']]);
    } else {
        echo json_encode(['error' => '用户名或密码错误']);
    }
    exit;
}

// 处理退出登录
if ($action === 'logout' && $method === 'POST') {
    // 清除Session
    session_destroy();
    
    // 清除Cookie
    clearAuthCookie();
    
    echo json_encode(['success' => true]);
    exit;
}

// 检查登录状态
if ($action === 'check_login') {
    echo json_encode(['logged_in' => isLoggedIn(), 'username' => $_SESSION['username'] ?? null]);
    exit;
}

// 以下接口需要登录验证
if (!isLoggedIn()) {
    echo json_encode(['error' => '未登录', 'need_login' => true]);
    exit;
}

$userId = $_SESSION['user_id'];

// 生成试卷
if ($action === 'generate_paper' && $method === 'POST') {
    $result = generateExamPaper($db, $userId);
    echo json_encode($result);
    exit;
}

// 获取进行中的考试
if ($action === 'get_active_exam') {
    $stmt = $db->prepare("SELECT * FROM active_exams WHERE user_id = ?");
    $stmt->execute([$userId]);
    $activeExam = $stmt->fetch();
    
    if ($activeExam) {
        $examData = json_decode($activeExam['exam_data'], true);
        echo json_encode([
            'has_active' => true,
            'exam_no' => $activeExam['exam_no'],
            'paper' => $examData,
            'start_time' => $activeExam['start_time']
        ]);
    } else {
        echo json_encode(['has_active' => false]);
    }
    exit;
}

// 保存答案
if ($action === 'save_answer' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $examNo = $input['exam_no'] ?? '';
    $questionId = $input['question_id'] ?? 0;
    $userAnswer = $input['answer'] ?? '';
    
    if (empty($examNo) || empty($questionId)) {
        echo json_encode(['error' => '参数错误']);
        exit;
    }
    
    $result = saveAnswerProgress($db, $examNo, $questionId, $userAnswer);
    echo json_encode($result);
    exit;
}

// 更新当前题目和标记状态
if ($action === 'update_progress' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $examNo = $input['exam_no'] ?? '';
    $currentQuestion = $input['current_question'] ?? 1;
    $markedQuestions = $input['marked_questions'] ?? [];
    
    $stmt = $db->prepare("UPDATE active_exams SET current_question = ?, marked_questions = ?, last_activity = CURRENT_TIMESTAMP WHERE exam_no = ?");
    $stmt->execute([$currentQuestion, json_encode($markedQuestions), $examNo]);
    
    echo json_encode(['success' => true]);
    exit;
}

// 提交试卷
if ($action === 'submit_exam' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $examNo = $input['exam_no'] ?? '';
    
    if (empty($examNo)) {
        echo json_encode(['error' => '参数错误']);
        exit;
    }
    
    $result = submitExam($db, $examNo, $userId);
    echo json_encode($result);
    exit;
}

// 获取历史考试记录
if ($action === 'get_history') {
    $history = getExamHistory($db, $userId);
    echo json_encode(['success' => true, 'records' => $history]);
    exit;
}

// 获取单次考试详情（用于回看）
if ($action === 'get_exam_detail') {
    $examNo = $_GET['exam_no'] ?? '';
    
    if (empty($examNo)) {
        echo json_encode(['error' => '参数错误']);
        exit;
    }
    
    // 获取考试基本信息
    $stmt = $db->prepare("SELECT * FROM exams WHERE exam_no = ? AND user_id = ?");
    $stmt->execute([$examNo, $userId]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        echo json_encode(['error' => '考试记录不存在']);
        exit;
    }
    
    // 由于提交后active_exams已删除，我们需要从answers表获取答题记录
    // 但answers表的exam_id关联的是active_exams.id，这里需要改进
    // 简化方案：在exams表中增加exam_data字段存储完整试卷快照
    
    // 临时方案：返回考试基本信息，题目详情需要从其他方式获取
    echo json_encode([
        'success' => true,
        'exam' => $exam,
        'message' => '详细题目数据需要在提交时保存到exams表'
    ]);
    exit;
}

// 获取统计数据
if ($action === 'get_stats') {
    $stmt = $db->prepare("SELECT COUNT(*) as total, AVG(accuracy) as avg_accuracy, MAX(user_score) as best_score FROM exams WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_exams' => $stats['total'] ?? 0,
            'avg_accuracy' => round($stats['avg_accuracy'] ?? 0, 2),
            'best_score' => $stats['best_score'] ?? 0
        ]
    ]);
    exit;
}

echo json_encode(['error' => '未知操作']);
?>
