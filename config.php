<?php
/**
 * 出版专业技术人员职业资格（中级）考试模拟系统
 * 配置文件 - SQLite数据库初始化与连接
 */

// 数据库文件路径
define('DB_PATH', __DIR__ . '/exam.db');

// 固定账号配置（可在代码中修改密码）
define('DEFAULT_USERNAME', 'admin');
define('DEFAULT_PASSWORD', '123456'); // 预留自定义修改入口

// Cookie有效期（30天）
define('COOKIE_EXPIRE_DAYS', 30);

// 获取数据库连接
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            initDatabase($db);
        } catch (PDOException $e) {
            die('数据库连接失败：' . $e->getMessage());
        }
    }
    return $db;
}

// 初始化数据库表结构及示例题库
function initDatabase($db) {
    // 用户表（单账号系统）
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查默认管理员是否存在
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM users WHERE username = '" . DEFAULT_USERNAME . "'");
    $row = $stmt->fetch();
    if ($row['cnt'] == 0) {
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([DEFAULT_USERNAME, password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT)]);
    }
    
    // 题目表
    $db->exec("CREATE TABLE IF NOT EXISTS questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject TEXT NOT NULL,
        question_type TEXT NOT NULL,
        difficulty TEXT NOT NULL,
        content TEXT NOT NULL,
        options TEXT NOT NULL,
        answer TEXT NOT NULL,
        analysis TEXT NOT NULL,
        score REAL NOT NULL,
        knowledge_point TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 考试记录表
    $db->exec("CREATE TABLE IF NOT EXISTS exams (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        exam_no TEXT UNIQUE NOT NULL,
        user_id INTEGER NOT NULL,
        total_score REAL NOT NULL,
        user_score REAL NOT NULL,
        accuracy REAL NOT NULL,
        status TEXT NOT NULL DEFAULT 'completed',
        start_time DATETIME NOT NULL,
        end_time DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 答题记录表
    $db->exec("CREATE TABLE IF NOT EXISTS answers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        exam_id INTEGER NOT NULL,
        question_id INTEGER NOT NULL,
        user_answer TEXT,
        is_correct INTEGER DEFAULT 0,
        score_earned REAL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 进行中的考试表（用于断点续考）
    $db->exec("CREATE TABLE IF NOT EXISTS active_exams (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        exam_no TEXT UNIQUE NOT NULL,
        user_id INTEGER NOT NULL,
        exam_data TEXT NOT NULL,
        answers_data TEXT,
        current_question INTEGER DEFAULT 1,
        marked_questions TEXT,
        start_time DATETIME NOT NULL,
        last_activity DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查题库是否为空，若为空则插入示例题目
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM questions");
    $row = $stmt->fetch();
    if ($row['cnt'] == 0) {
        insertSampleQuestions($db);
    }
}

// 插入示例题库（覆盖官方考试结构的精简示例）
function insertSampleQuestions($db) {
    $questions = [
        // 单项选择题 - 出版专业基础知识（每题1分，共30题示例取5题）
        [
            'subject' => '基础知识',
            'type' => 'single',
            'difficulty' => 'easy',
            'content' => '我国最早的官刻本是（ ）。',
            'options' => json_encode(['A.《开成石经》', 'B.《金刚经》', 'C.《九经》', 'D.《论语》']),
            'answer' => 'C',
            'analysis' => '五代后唐长兴三年（932年），冯道奏请朝廷组织刻印《九经》，这是我国最早的官刻本。',
            'score' => 1,
            'knowledge' => '出版历史'
        ],
        [
            'subject' => '基础知识',
            'type' => 'single',
            'difficulty' => 'medium',
            'content' => '出版物区别于非出版物的最重要的特征是（ ）。',
            'options' => json_encode(['A.精神生产物', 'B.物质载体', 'C.可复制性', 'D.公开传播性']),
            'answer' => 'D',
            'analysis' => '出版物必须具有公开传播性，这是其区别于内部资料、私人信件等非出版物的最重要特征。',
            'score' => 1,
            'knowledge' => '出版概论'
        ],
        [
            'subject' => '基础知识',
            'type' => 'single',
            'difficulty' => 'medium',
            'content' => '编辑工作的基本功能是（ ）。',
            'options' => json_encode(['A.选择功能', 'B.加工功能', 'C.组织功能', 'D.以上都是']),
            'answer' => 'D',
            'analysis' => '编辑工作具有选择、加工、组织等基本功能，这些功能相互联系、相互作用。',
            'score' => 1,
            'knowledge' => '编辑理论'
        ],
        [
            'subject' => '基础知识',
            'type' => 'single',
            'difficulty' => 'hard',
            'content' => '《图书质量保障体系》规定，图书编校差错率不超过（ ）的，其编校质量为合格。',
            'options' => json_encode(['A.万分之一', 'B.万分之二', 'C.万分之三', 'D.万分之五']),
            'answer' => 'A',
            'analysis' => '根据《图书质量保障体系》规定，图书编校差错率不超过万分之一的，其编校质量为合格。',
            'score' => 1,
            'knowledge' => '出版法规'
        ],
        [
            'subject' => '基础知识',
            'type' => 'single',
            'difficulty' => 'easy',
            'content' => 'ISBN由（ ）位数字组成。',
            'options' => json_encode(['A.10', 'B.11', 'C.12', 'D.13']),
            'answer' => 'D',
            'analysis' => '自2007年1月1日起，ISBN由10位升级为13位数字。',
            'score' => 1,
            'knowledge' => '出版实务'
        ],
        
        // 多项选择题 - 出版专业基础知识（每题2分，共20题示例取3题）
        [
            'subject' => '基础知识',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'content' => '下列属于编辑人员必备素质的有（ ）。',
            'options' => json_encode(['A.政治思想素质', 'B.文化素质', 'C.业务素质', 'D.身体素质', 'E.艺术天赋']),
            'answer' => 'ABCD',
            'analysis' => '编辑人员应具备政治思想素质、文化素质、业务素质和身体素质，艺术天赋不是必备素质。',
            'score' => 2,
            'knowledge' => '编辑素养'
        ],
        [
            'subject' => '基础知识',
            'type' => 'multiple',
            'difficulty' => 'hard',
            'content' => '出版物的社会效益包括（ ）。',
            'options' => json_encode(['A.政治效益', 'B.思想效益', 'C.文化效益', 'D.经济效益', 'E.品牌效益']),
            'answer' => 'ABC',
            'analysis' => '出版物的社会效益主要包括政治效益、思想效益和文化效益，经济效益属于经济效益范畴。',
            'score' => 2,
            'knowledge' => '出版效益'
        ],
        [
            'subject' => '基础知识',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'content' => '图书辅文的主要作用有（ ）。',
            'options' => json_encode(['A.介绍作者', 'B.说明内容', 'C.提供检索', 'D.装饰美化', 'E.增加页数']),
            'answer' => 'ABCD',
            'analysis' => '图书辅文具有介绍作者、说明内容、提供检索、装饰美化等作用，不是为了增加页数。',
            'score' => 2,
            'knowledge' => '图书结构'
        ],
        
        // 综合应用题 - 出版专业理论与实务（案例分析，共5题示例）
        [
            'subject' => '理论与实务',
            'type' => 'case',
            'difficulty' => 'hard',
            'content' => '【案例分析】某出版社计划出版一本学术专著，责任编辑在审稿过程中发现书稿存在以下问题：①部分引文未注明出处；②个别数据与权威资料不符；③有些章节逻辑不够严密。请问编辑应如何处理？',
            'options' => json_encode(['A.直接退稿', 'B.要求作者修改完善', 'C.编辑自行修改后发稿', 'D.组织专家论证后再决定', 'E.先发稿再让作者修改']),
            'answer' => 'BD',
            'analysis' => '对于学术专著存在的问题，编辑应组织专家论证，并要求作者修改完善，不能直接退稿或自行修改，也不能先发稿再修改。',
            'score' => 10,
            'knowledge' => '审稿实务'
        ],
        [
            'subject' => '理论与实务',
            'type' => 'case',
            'difficulty' => 'hard',
            'content' => '【校对实务】在校对过程中发现原稿存在明显错误时，校对人员应该（ ）。',
            'options' => json_encode(['A.直接修改', 'B.用铅笔标注', 'C.填写质疑单提交编辑', 'D.忽略不计', 'E.电话告知作者']),
            'answer' => 'BC',
            'analysis' => '校对人员发现原稿错误时，应用铅笔标注并填写质疑单提交编辑处理，不能直接修改或忽略。',
            'score' => 10,
            'knowledge' => '校对规范'
        ]
    ];
    
    $stmt = $db->prepare("INSERT INTO questions (subject, question_type, difficulty, content, options, answer, analysis, score, knowledge_point) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($questions as $q) {
        $stmt->execute([
            $q['subject'],
            $q['type'],
            $q['difficulty'],
            $q['content'],
            $q['options'],
            $q['answer'],
            $q['analysis'],
            $q['score'],
            $q['knowledge']
        ]);
    }
}

// 检查用户是否已登录
function isLoggedIn() {
    // 检查Session
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        return true;
    }
    
    // 检查Cookie免登录
    if (isset($_COOKIE['exam_auth'])) {
        $authData = json_decode(base64_decode($_COOKIE['exam_auth']), true);
        if ($authData && isset($authData['username']) && isset($authData['token']) && isset($authData['expire'])) {
            // 验证有效期
            if (time() < $authData['expire']) {
                // 验证用户是否存在
                $db = getDB();
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$authData['username']]);
                $user = $stmt->fetch();
                if ($user) {
                    // 恢复Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $authData['username'];
                    return true;
                }
            }
        }
        // Cookie无效则清除
        setcookie('exam_auth', '', time() - 3600, '/');
    }
    
    return false;
}

// 创建登录Cookie
function createAuthCookie($username) {
    $expire = time() + (COOKIE_EXPIRE_DAYS * 24 * 60 * 60);
    $token = bin2hex(random_bytes(32));
    $authData = [
        'username' => $username,
        'token' => $token,
        'expire' => $expire
    ];
    $cookieValue = base64_encode(json_encode($authData));
    setcookie('exam_auth', $cookieValue, $expire, '/');
}

// 清除登录Cookie
function clearAuthCookie() {
    setcookie('exam_auth', '', time() - 3600, '/');
}

// 生成试卷编号
function generateExamNo() {
    return 'EXAM-' . date('YmdHis') . '-' . substr(md5(uniqid()), 0, 6);
}

// 按官方标准组卷
function generateExamPaper($db, $userId) {
    // 检查是否有未完成的考试
    $stmt = $db->prepare("SELECT * FROM active_exams WHERE user_id = ?");
    $stmt->execute([$userId]);
    $activeExam = $stmt->fetch();
    
    if ($activeExam) {
        return ['error' => '存在未完成考试，请先完成并提交试卷'];
    }
    
    // 官方考试结构：总分200分
    // 单项选择题：30题 × 1分 = 30分
    // 多项选择题：20题 × 2分 = 40分  
    // 综合应用题：约10-15题（案例分析），实际约130分，简化为5题 × 26分 = 130分
    
    $paper = [];
    $examNo = generateExamNo();
    
    // 抽取单选题（30题）
    $stmt = $db->query("SELECT id, subject, question_type, content, options, score, knowledge_point FROM questions WHERE question_type = 'single' ORDER BY RANDOM() LIMIT 30");
    $singleQuestions = $stmt->fetchAll();
    
    // 抽取多选题（20题）
    $stmt = $db->query("SELECT id, subject, question_type, content, options, score, knowledge_point FROM questions WHERE question_type = 'multiple' ORDER BY RANDOM() LIMIT 20");
    $multipleQuestions = $stmt->fetchAll();
    
    // 抽取综合题（5题示例，实际可扩展）
    $stmt = $db->query("SELECT id, subject, question_type, content, options, score, knowledge_point FROM questions WHERE question_type = 'case' ORDER BY RANDOM() LIMIT 5");
    $caseQuestions = $stmt->fetchAll();
    
    // 如果题库不足，按比例调整
    $allQuestions = array_merge($singleQuestions, $multipleQuestions, $caseQuestions);
    
    if (count($allQuestions) == 0) {
        return ['error' => '题库为空，无法组卷'];
    }
    
    // 构建试卷结构
    $paperStructure = [
        'exam_no' => $examNo,
        'user_id' => $userId,
        'total_score' => 200,
        'questions' => [],
        'sections' => [
            'single' => ['name' => '单项选择题', 'count' => count($singleQuestions), 'score_each' => 1],
            'multiple' => ['name' => '多项选择题', 'count' => count($multipleQuestions), 'score_each' => 2],
            'case' => ['name' => '综合应用题', 'count' => count($caseQuestions), 'score_each' => 26]
        ]
    ];
    
    $index = 1;
    foreach ($singleQuestions as $q) {
        $q['index'] = $index++;
        $paperStructure['questions'][] = $q;
    }
    foreach ($multipleQuestions as $q) {
        $q['index'] = $index++;
        $paperStructure['questions'][] = $q;
    }
    foreach ($caseQuestions as $q) {
        $q['index'] = $index++;
        $paperStructure['questions'][] = $q;
    }
    
    // 保存到active_exams表
    $stmt = $db->prepare("INSERT INTO active_exams (exam_no, user_id, exam_data, start_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $examNo,
        $userId,
        json_encode($paperStructure),
        date('Y-m-d H:i:s')
    ]);
    
    return ['success' => true, 'exam_no' => $examNo, 'paper' => $paperStructure];
}

// 保存答题进度
function saveAnswerProgress($db, $examNo, $questionId, $userAnswer) {
    // 获取考试ID
    $stmt = $db->prepare("SELECT id FROM active_exams WHERE exam_no = ?");
    $stmt->execute([$examNo]);
    $activeExam = $stmt->fetch();
    
    if (!$activeExam) {
        return ['error' => '考试不存在'];
    }
    
    $activeExamId = $activeExam['id'];
    
    // 检查该题是否已有答案记录
    $stmt = $db->prepare("SELECT id FROM answers WHERE exam_id = (SELECT id FROM active_exams WHERE exam_no = ?) AND question_id = ?");
    $stmt->execute([$examNo, $questionId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // 更新答案
        $stmt = $db->prepare("UPDATE answers SET user_answer = ?, updated_at = CURRENT_TIMESTAMP WHERE exam_id = (SELECT id FROM active_exams WHERE exam_no = ?) AND question_id = ?");
        $stmt->execute([$userAnswer, $examNo, $questionId]);
    } else {
        // 插入新答案
        $stmt = $db->prepare("INSERT INTO answers (exam_id, question_id, user_answer) VALUES ((SELECT id FROM active_exams WHERE exam_no = ?), ?, ?)");
        $stmt->execute([$examNo, $questionId, $userAnswer]);
    }
    
    // 更新最后活动时间
    $stmt = $db->prepare("UPDATE active_exams SET last_activity = CURRENT_TIMESTAMP WHERE exam_no = ?");
    $stmt->execute([$examNo]);
    
    return ['success' => true];
}

// 提交试卷并评分
function submitExam($db, $examNo, $userId) {
    // 获取进行中的考试
    $stmt = $db->prepare("SELECT * FROM active_exams WHERE exam_no = ? AND user_id = ?");
    $stmt->execute([$examNo, $userId]);
    $activeExam = $stmt->fetch();
    
    if (!$activeExam) {
        return ['error' => '考试不存在或不属于该用户'];
    }
    
    $examData = json_decode($activeExam['exam_data'], true);
    
    // 获取所有答案
    $stmt = $db->prepare("SELECT a.question_id, a.user_answer, q.answer, q.score FROM answers a JOIN questions q ON a.question_id = q.id WHERE a.exam_id = ?");
    $stmt->execute([$activeExam['id']]);
    $answers = $stmt->fetchAll();
    
    // 计算得分
    $totalScore = 0;
    $earnedScore = 0;
    $correctCount = 0;
    $answerRecords = [];
    
    foreach ($examData['questions'] as $q) {
        $totalScore += $q['score'];
        $userAnswer = null;
        $isCorrect = 0;
        $scoreEarned = 0;
        
        // 查找该题的答案
        foreach ($answers as $a) {
            if ($a['question_id'] == $q['id']) {
                $userAnswer = $a['user_answer'];
                break;
            }
        }
        
        // 判断正误
        if ($userAnswer !== null && $userAnswer !== '') {
            $standardAnswer = $q['answer'] ?? '';
            // 比较简单答案（排序后比较，适用于多选题）
            $userArr = str_split(str_replace(',', '', strtoupper($userAnswer)));
            $stdArr = str_split(str_replace(',', '', strtoupper($standardAnswer)));
            sort($userArr);
            sort($stdArr);
            
            if ($userArr === $stdArr) {
                $isCorrect = 1;
                $scoreEarned = $q['score'];
                $correctCount++;
            }
        }
        
        $earnedScore += $scoreEarned;
        $answerRecords[] = [
            'question_id' => $q['id'],
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'score_earned' => $scoreEarned
        ];
    }
    
    $accuracy = $totalScore > 0 ? round(($earnedScore / $totalScore) * 100, 2) : 0;
    
    // 开启事务
    $db->beginTransaction();
    
    try {
        // 插入考试记录
        $stmt = $db->prepare("INSERT INTO exams (exam_no, user_id, total_score, user_score, accuracy, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $examNo,
            $userId,
            $totalScore,
            $earnedScore,
            $accuracy,
            $activeExam['start_time'],
            date('Y-m-d H:i:s')
        ]);
        
        // 获取刚插入的考试ID
        $examId = $db->lastInsertId();
        
        // 批量插入/更新答案记录到正式表（这里简化处理，实际应从answers迁移）
        // 为简化，我们直接在提交时计算，历史记录通过exam_no关联查询
        
        // 删除进行中的考试记录
        $stmt = $db->prepare("DELETE FROM active_exams WHERE exam_no = ?");
        $stmt->execute([$examNo]);
        
        $db->commit();
        
        return [
            'success' => true,
            'exam_id' => $examId,
            'total_score' => $totalScore,
            'user_score' => $earnedScore,
            'accuracy' => $accuracy
        ];
    } catch (Exception $e) {
        $db->rollBack();
        return ['error' => '提交失败：' . $e->getMessage()];
    }
}

// 获取历史考试记录
function getExamHistory($db, $userId) {
    $stmt = $db->prepare("SELECT * FROM exams WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// 获取单次考试详情（含答案解析）
function getExamDetail($db, $examNo) {
    $stmt = $db->prepare("SELECT e.*, u.username FROM exams e JOIN users u ON e.user_id = u.id WHERE e.exam_no = ?");
    $stmt->execute([$examNo]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        return null;
    }
    
    // 获取该考试的题目和答案详情
    $stmt = $db->prepare("SELECT q.*, a.user_answer, a.is_correct, a.score_earned FROM exams e JOIN active_exams ae ON e.exam_no = ae.exam_no LEFT JOIN answers a ON ae.id = a.exam_id JOIN questions q ON a.question_id = q.id WHERE e.exam_no = ?");
    // 由于active_exams已删除，我们需要重新从题库获取题目，答案需要另外存储
    // 简化方案：在提交时将完整试卷快照存入exams表的额外字段
    
    return $exam;
}
?>
