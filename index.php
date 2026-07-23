<?php
/**
 * 出版专业技术人员职业资格（中级）考试模拟系统
 * 主入口文件
 */
session_start();
require_once 'config.php';

// 判断是否已登录
$isLoggedIn = isLoggedIn();
$currentUsername = $_SESSION['username'] ?? null;

// 处理页面路由
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// 未登录用户只能访问登录页
if (!$isLoggedIn && $page !== 'login') {
    $page = 'login';
}

// 已登录用户访问登录页则跳转到主页
if ($isLoggedIn && $page === 'login') {
    $page = 'home';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出版专业技术人员职业资格（中级）考试模拟系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* 登录页面样式 */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .remember-me label {
            color: #666;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .error-msg {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }
        
        /* 顶部导航 */
        .navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-brand {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nav-user span {
            color: #666;
        }
        
        .btn-logout {
            padding: 8px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-logout:hover {
            background: #c0392b;
        }
        
        /* 主内容区域 */
        .main-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }
        
        /* 统计卡片 */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* 功能按钮区 */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .action-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .action-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .action-desc {
            color: #666;
            font-size: 14px;
        }
        
        .action-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .action-card.disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* 历史记录表格 */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .history-table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        
        .history-table tr:hover {
            background: #f8f9fa;
        }
        
        .btn-view {
            padding: 6px 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .btn-view:hover {
            background: #5a6fd6;
        }
        
        /* 考试页面 */
        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .exam-info h3 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .exam-info p {
            color: #666;
            font-size: 14px;
        }
        
        .timer {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
            background: white;
            padding: 10px 20px;
            border-radius: 5px;
        }
        
        .timer.warning {
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .exam-body {
            display: flex;
            gap: 20px;
        }
        
        .question-area {
            flex: 1;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
        }
        
        .question-nav {
            width: 250px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .question-nav h4 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }
        
        .nav-item {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .nav-item:hover {
            border-color: #667eea;
        }
        
        .nav-item.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .nav-item.answered {
            background: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }
        
        .nav-item.marked {
            border-color: #f39c12;
            background: #fef5e7;
        }
        
        .question-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .question-text {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 20px;
        }
        
        .options-list {
            list-style: none;
        }
        
        .options-list li {
            padding: 12px 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .options-list li:hover {
            background: #e9ecef;
        }
        
        .options-list li.selected {
            background: #e8f4fd;
            border-color: #667eea;
        }
        
        .options-list li.correct {
            background: #d4edda;
            border-color: #28a745;
        }
        
        .options-list li.incorrect {
            background: #f8d7da;
            border-color: #dc3545;
        }
        
        .question-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        
        .btn-mark {
            padding: 10px 20px;
            background: #f39c12;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-mark.marked {
            background: #e67e22;
        }
        
        .btn-prev, .btn-next {
            padding: 10px 25px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-submit {
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-submit:hover {
            background: #218838;
        }
        
        .section-divider {
            margin: 30px 0 20px;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 5px;
            font-weight: bold;
        }
        
        /* 结果页面 */
        .result-summary {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .result-score {
            font-size: 72px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        
        .result-detail {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
        }
        
        .result-item {
            text-align: center;
        }
        
        .result-item-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .result-item-label {
            color: #666;
            font-size: 14px;
        }
        
        .analysis-section {
            margin-top: 30px;
        }
        
        .analysis-item {
            background: white;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .analysis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .analysis-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        
        .status-correct {
            background: #d4edda;
            color: #155724;
        }
        
        .status-incorrect {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-unanswered {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .analysis-content {
            line-height: 1.8;
            color: #555;
        }
        
        .analysis-answer {
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .analysis-explanation {
            margin-top: 15px;
            padding: 15px;
            background: #e8f4fd;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        
        /* 提示信息 */
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .hidden {
            display: none;
        }
        
        /* 模态框 */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn-cancel {
            padding: 10px 25px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-confirm {
            padding: 10px 25px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php if ($page === 'login'): ?>
    <!-- 登录页面 -->
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-title">出版专业资格考试模拟系统</h1>
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">账号</label>
                    <input type="text" id="username" name="username" required placeholder="请输入账号">
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required placeholder="请输入密码">
                </div>
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" checked>
                    <label for="remember">30天内免登录</label>
                </div>
                <button type="submit" class="btn">登录</button>
                <div id="loginError" class="error-msg"></div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username, password, remember})
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = '?page=home';
                } else {
                    document.getElementById('loginError').textContent = result.error;
                }
            } catch (error) {
                document.getElementById('loginError').textContent = '网络错误，请重试';
            }
        });
    </script>
    
    <?php else: ?>
    <!-- 已登录页面 -->
    <div class="container">
        <!-- 顶部导航 -->
        <nav class="navbar">
            <div class="nav-brand">📚 出版专业资格考试模拟系统（中级）</div>
            <div class="nav-user">
                <span>欢迎，<?php echo htmlspecialchars($currentUsername); ?></span>
                <button class="btn-logout" onclick="logout()">退出登录</button>
            </div>
        </nav>
        
        <!-- 主内容区 -->
        <div class="main-content">
            <?php if ($page === 'home'): ?>
            <h2 class="page-title">考试中心</h2>
            
            <!-- 统计信息 -->
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-value" id="totalExams">0</div>
                    <div class="stat-label">已考次数</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="avgAccuracy">0%</div>
                    <div class="stat-label">平均正确率</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="bestScore">0</div>
                    <div class="stat-label">最高得分</div>
                </div>
            </div>
            
            <!-- 继续考试提示 -->
            <div id="continueExamAlert" class="alert alert-warning hidden">
                <strong>⚠️ 发现未完成的考试！</strong>
                <p style="margin-top: 10px;">
                    <button class="btn" style="width: auto; padding: 10px 30px;" onclick="continueExam()">继续考试</button>
                </p>
            </div>
            
            <!-- 功能操作区 -->
            <div class="action-grid">
                <div class="action-card" id="startExamCard" onclick="startNewExam()">
                    <div class="action-icon">📝</div>
                    <div class="action-title">开始新考试</div>
                    <div class="action-desc">按官方标准自动组卷，总分200分</div>
                </div>
                <div class="action-card" onclick="showPage('practice')">
                    <div class="action-icon">📖</div>
                    <div class="action-title">专项练习</div>
                    <div class="action-desc">按科目/题型/难度针对性刷题</div>
                </div>
                <div class="action-card" onclick="showPage('history')">
                    <div class="action-icon">📊</div>
                    <div class="action-title">历史记录</div>
                    <div class="action-desc">查看历次考试成绩与解析</div>
                </div>
            </div>
            
            <!-- 最近考试记录 -->
            <h3 style="margin: 30px 0 15px; color: #333;">最近考试记录</h3>
            <table class="history-table" id="recentHistoryTable">
                <thead>
                    <tr>
                        <th>试卷编号</th>
                        <th>考试时间</th>
                        <th>总分</th>
                        <th>得分</th>
                        <th>正确率</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="recentHistoryBody">
                    <tr><td colspan="6" style="text-align: center; color: #999;">暂无考试记录</td></tr>
                </tbody>
            </table>
            
            <?php elseif ($page === 'exam'): ?>
            <!-- 考试页面 -->
            <div id="examContainer">
                <div class="exam-header">
                    <div class="exam-info">
                        <h3 id="examNo">试卷编号：加载中...</h3>
                        <p>单项选择题 + 多项选择题 + 综合应用题 | 总分200分</p>
                    </div>
                    <div class="timer" id="examTimer">75:00</div>
                </div>
                
                <div class="exam-body">
                    <div class="question-area">
                        <div id="questionContent">
                            <!-- 题目内容动态加载 -->
                        </div>
                        
                        <div class="question-actions">
                            <button class="btn-mark" id="markBtn" onclick="toggleMark()">⭐ 标记本题</button>
                            <div>
                                <button class="btn-prev" id="prevBtn" onclick="prevQuestion()">上一题</button>
                                <button class="btn-next" id="nextBtn" onclick="nextQuestion()">下一题</button>
                                <button class="btn-submit" id="submitBtn" onclick="confirmSubmit()" style="display: none;">提交试卷</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="question-nav">
                        <h4>答题卡</h4>
                        <div class="nav-grid" id="navGrid">
                            <!-- 导航按钮动态生成 -->
                        </div>
                        <div style="margin-top: 20px; font-size: 12px; color: #666;">
                            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                <div style="width: 15px; height: 15px; background: #2ecc71; margin-right: 5px; border-radius: 3px;"></div>
                                已答
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                <div style="width: 15px; height: 15px; background: white; border: 2px solid #ddd; margin-right: 5px; border-radius: 3px;"></div>
                                未答
                            </div>
                            <div style="display: flex; align-items: center;">
                                <div style="width: 15px; height: 15px; background: #fef5e7; border: 2px solid #f39c12; margin-right: 5px; border-radius: 3px;"></div>
                                标记
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php elseif ($page === 'result'): ?>
            <!-- 成绩结果页面 -->
            <h2 class="page-title">考试结果</h2>
            <div id="resultContent">
                <!-- 结果内容动态加载 -->
            </div>
            
            <?php elseif ($page === 'history'): ?>
            <!-- 历史记录页面 -->
            <h2 class="page-title">历史考试记录</h2>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>试卷编号</th>
                        <th>考试时间</th>
                        <th>总分</th>
                        <th>得分</th>
                        <th>正确率</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    <!-- 历史记录动态加载 -->
                </tbody>
            </table>
            
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 提交确认模态框 -->
    <div id="submitModal" class="modal hidden">
        <div class="modal-content">
            <h3 style="margin-bottom: 15px;">确认提交试卷？</h3>
            <p id="submitWarning" style="color: #e74c3c;"></p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="closeSubmitModal()">取消</button>
                <button class="btn-confirm" onclick="submitExam()">确认提交</button>
            </div>
        </div>
    </div>
    
    <script>
        // 全局变量
        let currentExamNo = '';
        let currentPaper = null;
        let currentQuestionIndex = 0;
        let userAnswers = {};
        let markedQuestions = [];
        let examTimer = null;
        let timeRemaining = 75 * 60; // 75分钟
        
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', function() {
            const page = '<?php echo $page; ?>';
            
            if (page === 'home') {
                loadStats();
                loadRecentHistory();
                checkActiveExam();
            } else if (page === 'exam') {
                initExam();
            } else if (page === 'history') {
                loadHistory();
            }
        });
        
        // 退出登录
        async function logout() {
            try {
                await fetch('api.php?action=logout', {method: 'POST'});
                window.location.href = '?page=login';
            } catch (error) {
                alert('退出失败，请重试');
            }
        }
        
        // 页面跳转
        function showPage(pageName) {
            window.location.href = '?page=' + pageName;
        }
        
        // 加载统计数据
        async function loadStats() {
            try {
                const response = await fetch('api.php?action=get_stats');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('totalExams').textContent = result.stats.total_exams;
                    document.getElementById('avgAccuracy').textContent = result.stats.avg_accuracy + '%';
                    document.getElementById('bestScore').textContent = result.stats.best_score;
                }
            } catch (error) {
                console.error('加载统计失败:', error);
            }
        }
        
        // 检查是否有进行中的考试
        async function checkActiveExam() {
            try {
                const response = await fetch('api.php?action=get_active_exam');
                const result = await response.json();
                
                if (result.has_active) {
                    document.getElementById('continueExamAlert').classList.remove('hidden');
                    document.getElementById('startExamCard').classList.add('disabled');
                    currentExamNo = result.exam_no;
                }
            } catch (error) {
                console.error('检查考试状态失败:', error);
            }
        }
        
        // 继续考试
        function continueExam() {
            if (currentExamNo) {
                window.location.href = '?page=exam&exam_no=' + currentExamNo;
            }
        }
        
        // 开始新考试
        async function startNewExam() {
            if (document.getElementById('startExamCard').classList.contains('disabled')) {
                alert('存在未完成考试，请先完成并提交试卷');
                return;
            }
            
            try {
                const response = await fetch('api.php?action=generate_paper', {method: 'POST'});
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = '?page=exam&exam_no=' + result.exam_no;
                } else {
                    alert(result.error || '组卷失败');
                }
            } catch (error) {
                alert('网络错误，请重试');
            }
        }
        
        // 加载最近历史记录
        async function loadRecentHistory() {
            try {
                const response = await fetch('api.php?action=get_history');
                const result = await response.json();
                
                if (result.success && result.records.length > 0) {
                    const tbody = document.getElementById('recentHistoryBody');
                    tbody.innerHTML = result.records.slice(0, 5).map(record => `
                        <tr>
                            <td>${record.exam_no}</td>
                            <td>${record.created_at}</td>
                            <td>${record.total_score}</td>
                            <td>${record.user_score}</td>
                            <td>${record.accuracy}%</td>
                            <td><button class="btn-view" onclick="viewExam('${record.exam_no}')">查看详情</button></td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('加载历史记录失败:', error);
            }
        }
        
        // 加载完整历史记录
        async function loadHistory() {
            try {
                const response = await fetch('api.php?action=get_history');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('historyBody');
                    if (result.records.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #999; padding: 40px;">暂无考试记录</td></tr>';
                    } else {
                        tbody.innerHTML = result.records.map(record => `
                            <tr>
                                <td>${record.exam_no}</td>
                                <td>${record.created_at}</td>
                                <td>${record.total_score}</td>
                                <td>${record.user_score}</td>
                                <td>${record.accuracy}%</td>
                                <td><button class="btn-view" onclick="viewExam('${record.exam_no}')">查看详情</button></td>
                            </tr>
                        `).join('');
                    }
                }
            } catch (error) {
                console.error('加载历史记录失败:', error);
            }
        }
        
        // 查看考试详情
        function viewExam(examNo) {
            // TODO: 实现查看详情功能
            alert('查看试卷详情功能开发中，试卷编号：' + examNo);
        }
        
        // 初始化考试
        async function initExam() {
            const urlParams = new URLSearchParams(window.location.search);
            currentExamNo = urlParams.get('exam_no');
            
            if (!currentExamNo) {
                alert('无效的考试编号');
                window.location.href = '?page=home';
                return;
            }
            
            try {
                const response = await fetch('api.php?action=get_active_exam');
                const result = await response.json();
                
                if (result.has_active && result.exam_no === currentExamNo) {
                    currentPaper = result.paper;
                    renderExam();
                    startTimer();
                    
                    // 定时保存进度
                    setInterval(saveProgress, 30000); // 每30秒保存一次
                    
                    // 页面关闭前保存
                    window.addEventListener('beforeunload', saveProgress);
                } else {
                    alert('考试不存在或已结束');
                    window.location.href = '?page=home';
                }
            } catch (error) {
                alert('加载考试失败');
                window.location.href = '?page=home';
            }
        }
        
        // 渲染考试界面
        function renderExam() {
            document.getElementById('examNo').textContent = '试卷编号：' + currentExamNo;
            renderNavGrid();
            showQuestion(0);
        }
        
        // 渲染答题卡
        function renderNavGrid() {
            const navGrid = document.getElementById('navGrid');
            const totalQuestions = currentPaper.questions.length;
            
            navGrid.innerHTML = Array.from({length: totalQuestions}, (_, i) => {
                const qNum = i + 1;
                const isAnswered = userAnswers[currentPaper.questions[i].id] ? 'answered' : '';
                const isMarked = markedQuestions.includes(qNum) ? 'marked' : '';
                const isActive = i === currentQuestionIndex ? 'active' : '';
                
                return `<div class="nav-item ${isAnswered} ${isMarked} ${isActive}" onclick="showQuestion(${i})">${qNum}</div>`;
            }).join('');
        }
        
        // 显示题目
        function showQuestion(index) {
            currentQuestionIndex = index;
            const question = currentPaper.questions[index];
            const userAnswer = userAnswers[question.id] || '';
            
            const options = JSON.parse(question.options);
            const isMultiple = question.question_type === 'multiple';
            
            document.getElementById('questionContent').innerHTML = `
                <div class="question-content">
                    <div class="section-divider">
                        ${question.subject} - ${getQuestionTypeName(question.question_type)}（第${index + 1}题，${question.score}分）
                    </div>
                    <div class="question-text">${question.content}</div>
                    <ul class="options-list">
                        ${options.map((opt, i) => {
                            const optionLabel = String.fromCharCode(65 + i);
                            const isSelected = userAnswer.includes(optionLabel);
                            return `<li data-option="${optionLabel}" class="${isSelected ? 'selected' : ''}" onclick="selectOption('${optionLabel}', ${isMultiple})">${opt}</li>`;
                        }).join('')}
                    </ul>
                </div>
            `;
            
            // 更新按钮状态
            document.getElementById('prevBtn').style.display = index === 0 ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = index === currentPaper.questions.length - 1 ? 'none' : 'inline-block';
            document.getElementById('submitBtn').style.display = index === currentPaper.questions.length - 1 ? 'inline-block' : 'none';
            
            // 更新标记按钮状态
            const markBtn = document.getElementById('markBtn');
            if (markedQuestions.includes(index + 1)) {
                markBtn.classList.add('marked');
                markBtn.textContent = '⭐ 已标记';
            } else {
                markBtn.classList.remove('marked');
                markBtn.textContent = '⭐ 标记本题';
            }
            
            renderNavGrid();
        }
        
        // 获取题型名称
        function getQuestionTypeName(type) {
            const names = {
                'single': '单项选择题',
                'multiple': '多项选择题',
                'case': '综合应用题'
            };
            return names[type] || type;
        }
        
        // 选择选项
        function selectOption(optionLabel, isMultiple) {
            const question = currentPaper.questions[currentQuestionIndex];
            let currentAnswer = userAnswers[question.id] || '';
            
            if (isMultiple) {
                // 多选题
                if (currentAnswer.includes(optionLabel)) {
                    currentAnswer = currentAnswer.replace(optionLabel, '');
                } else {
                    currentAnswer += optionLabel;
                }
            } else {
                // 单选题
                currentAnswer = optionLabel;
            }
            
            userAnswers[question.id] = currentAnswer;
            
            // 立即保存答案
            saveAnswer(question.id, currentAnswer);
            
            // 重新渲染
            showQuestion(currentQuestionIndex);
        }
        
        // 保存答案
        async function saveAnswer(questionId, answer) {
            try {
                await fetch('api.php?action=save_answer', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        exam_no: currentExamNo,
                        question_id: questionId,
                        answer: answer
                    })
                });
            } catch (error) {
                console.error('保存答案失败:', error);
            }
        }
        
        // 切换标记
        function toggleMark() {
            const qNum = currentQuestionIndex + 1;
            const index = markedQuestions.indexOf(qNum);
            
            if (index > -1) {
                markedQuestions.splice(index, 1);
            } else {
                markedQuestions.push(qNum);
            }
            
            // 保存标记状态
            saveProgress();
            showQuestion(currentQuestionIndex);
        }
        
        // 上一题
        function prevQuestion() {
            if (currentQuestionIndex > 0) {
                showQuestion(currentQuestionIndex - 1);
            }
        }
        
        // 下一题
        function nextQuestion() {
            if (currentQuestionIndex < currentPaper.questions.length - 1) {
                showQuestion(currentQuestionIndex + 1);
            }
        }
        
        // 启动计时器
        function startTimer() {
            updateTimerDisplay();
            examTimer = setInterval(() => {
                timeRemaining--;
                updateTimerDisplay();
                
                if (timeRemaining <= 0) {
                    clearInterval(examTimer);
                    confirmSubmit();
                }
            }, 1000);
        }
        
        // 更新计时器显示
        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const timerEl = document.getElementById('examTimer');
            timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemaining <= 300) { // 最后5分钟
                timerEl.classList.add('warning');
            }
        }
        
        // 保存进度
        async function saveProgress() {
            try {
                await fetch('api.php?action=update_progress', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        exam_no: currentExamNo,
                        current_question: currentQuestionIndex + 1,
                        marked_questions: markedQuestions
                    })
                });
            } catch (error) {
                console.error('保存进度失败:', error);
            }
        }
        
        // 确认提交
        function confirmSubmit() {
            const totalQuestions = currentPaper.questions.length;
            const answeredCount = Object.keys(userAnswers).filter(k => userAnswers[k]).length;
            const unansweredCount = totalQuestions - answeredCount;
            
            const warningEl = document.getElementById('submitWarning');
            if (unansweredCount > 0) {
                warningEl.textContent = `尚有 ${unansweredCount} 道题目未作答，是否确认提交？`;
            } else {
                warningEl.textContent = '';
            }
            
            document.getElementById('submitModal').classList.remove('hidden');
        }
        
        // 关闭提交模态框
        function closeSubmitModal() {
            document.getElementById('submitModal').classList.add('hidden');
        }
        
        // 提交试卷
        async function submitExam() {
            closeSubmitModal();
            
            try {
                const response = await fetch('api.php?action=submit_exam', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({exam_no: currentExamNo})
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // 清除定时器
                    clearInterval(examTimer);
                    
                    // 跳转到结果页
                    alert(`考试已完成！\n总分：${result.total_score}\n得分：${result.user_score}\n正确率：${result.accuracy}%`);
                    window.location.href = '?page=history';
                } else {
                    alert(result.error || '提交失败');
                }
            } catch (error) {
                alert('网络错误，请重试');
            }
        }
    </script>
    <?php endif; ?>
</body>
</html>
