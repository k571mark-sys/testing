<?php
// Redirect to auth if already logged in
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /aptitude-test/public/admin/dashboard.php');
    } else {
        header('Location: /aptitude-test/public/candidate/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCF Ibadan - Aptitude Test Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e9 100%);
            color: #1a1a18;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        header {
            background: linear-gradient(135deg, #1e88e5 0%, #43a047 100%);
            color: white;
            padding: 24px 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-align: center;
        }
        
        header h1 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        header p {
            font-size: 18px;
            font-weight: 300;
            opacity: 0.95;
            margin: 0;
        }
        
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }
        
        .container {
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }
        
        .content-left {
            padding: 48px 40px;
            background: linear-gradient(135deg, rgba(227, 242, 253, 0.6) 0%, rgba(232, 245, 233, 0.6) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .content-right {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: white;
        }
        
        .logo-section {
            margin-bottom: 24px;
            text-align: center;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e88e5 0%, #43a047 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: white;
            margin: 0 auto 16px;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
        }
        
        .content-left h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1e88e5;
            margin-bottom: 16px;
        }
        
        .content-left p {
            font-size: 17px;
            line-height: 1.8;
            color: #424242;
            margin-bottom: 16px;
        }
        
        .content-left ul {
            list-style: none;
            margin: 24px 0;
            font-size: 16px;
        }
        
        .content-left ul li {
            padding: 12px 0;
            padding-left: 32px;
            position: relative;
            color: #424242;
            line-height: 1.6;
        }
        
        .content-left ul li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #43a047;
            font-weight: bold;
            font-size: 20px;
        }
        
        .info-box {
            background: linear-gradient(135deg, #c8e6c9 0%, #b3e5fc 100%);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            border-left: 4px solid #1e88e5;
        }
        
        .info-box h3 {
            font-size: 16px;
            color: #1565c0;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-box p {
            font-size: 15px;
            color: #2e7d32;
            margin: 0;
            line-height: 1.6;
        }
        
        .right-content h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e88e5;
            margin-bottom: 12px;
            text-align: center;
        }
        
        .right-content p {
            font-size: 16px;
            color: #424242;
            margin-bottom: 32px;
            text-align: center;
            line-height: 1.6;
        }
        
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
        }
        
        .btn {
            padding: 16px 32px;
            font-size: 18px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(30, 136, 229, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 160, 71, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(67, 160, 71, 0.4);
        }
        
        footer {
            background: linear-gradient(135deg, #1e88e5 0%, #43a047 100%);
            color: white;
            padding: 24px;
            text-align: center;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .highlight {
            color: #43a047;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }
            
            .content-left, .content-right {
                padding: 32px 24px;
            }
            
            header h1 {
                font-size: 28px;
            }
            
            header p {
                font-size: 16px;
            }
            
            .content-left h2 {
                font-size: 24px;
            }
            
            .content-left p, .content-left ul li {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>FCF Ibadan</h1>
        <p>Federal College of Forestry</p>
    </header>
    
    <main>
        <div class="container">
            <div class="content-wrapper">
                <div class="content-left">
                    <div class="logo-section">
                        <div class="school-logo">FCF</div>
                        <h3 style="color: #1e88e5; font-size: 18px; margin: 0;">Online Aptitude Test</h3>
                    </div>
                    
                    <h2>Welcome to the Aptitude Test Platform</h2>
                    <p>Evaluate your skills and measure your aptitude with our comprehensive online assessment system.</p>
                    
                    <ul>
                        <li>Comprehensive aptitude assessments</li>
                        <li>Real-time scoring and detailed feedback</li>
                        <li>Secure and proctored environment</li>
                        <li>Instant results with performance breakdown</li>
                        <li>Track your progress and improvements</li>
                    </ul>
                    
                    <div class="info-box">
                        <h3>📋 Assessment Overview</h3>
                        <p>Our platform provides a robust aptitude testing solution covering Verbal Reasoning, Numerical Aptitude, and Logical Reasoning. Each test is carefully designed to assess your competency comprehensively.</p>
                    </div>
                </div>
                
                <div class="content-right">
                    <div class="right-content">
                        <h2>Get Started</h2>
                        <p>Sign in to your account to access available tests and begin your assessment journey.</p>
                        
                        <div class="btn-container">
                            <a href="auth/login.php" class="btn btn-primary">Sign In</a>
                            <a href="auth/register.php" class="btn btn-secondary">Create Account</a>
                        </div>
                        
                        <div style="margin-top: 32px; padding-top: 24px; border-top: 2px solid #e0e0e0;">
                            <p style="font-size: 14px; color: #666; margin-bottom: 12px;">
                                <strong style="color: #1e88e5;">Demo Credentials (Testing Only):</strong>
                            </p>
                            <div style="background: #f5f5f5; padding: 12px; border-radius: 6px; font-size: 13px; color: #424242; text-align: left;">
                                <p style="margin: 6px 0;"><strong>Candidate:</strong> candidate@mail.com / password123</p>
                                <p style="margin: 6px 0;"><strong>Admin:</strong> admin@mail.com / admin123</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2024-2026 <span class="highlight">Federal College of Forestry, Ibadan</span>. All rights reserved.</p>
        <p style="margin-top: 8px; opacity: 0.8;">Empowering students through quality assessment</p>
    </footer>
</body>
</html>
