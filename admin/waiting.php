<?php
include '../components/connect.php'; // Use correct relative path

// Check if user is logged in via cookie or session
if(isset($_COOKIE['tutor_id'])){
    $tutor_id = $_COOKIE['tutor_id'];
} else {
    // If no cookie, redirect to login
    header('location: login1.php');
    exit();
}

// Check tutor status
$select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ? LIMIT 1");
$select_tutor->execute([$tutor_id]);
$tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);

if($select_tutor->rowCount() > 0){
    // If approved, redirect to dashboard
    if($tutor['status'] == 'Approved'){
        header('location: admin/dashboard.php');
        exit();
    }
    // If rejected, clear cookie and redirect to login with message
    elseif($tutor['status'] == 'Rejected'){
        setcookie('tutor_id', '', time() - 3600, '/'); // Clear cookie
        header('location: ../login1.php?rejected=1');
        exit();
    }
    // If pending, show waiting page
} else {
    // Tutor not found, redirect to login
    header('../location: login1.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Approval - UniLinker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .waiting-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
            backdrop-filter: blur(10px);
        }

        .waiting-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border: 4px solid #667eea;
            border-top: 4px solid transparent;
            border-radius: 50%;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .waiting-container h1 {
            color: #2f5ae5;
            font-size: 2.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .waiting-container h2 {
            color: #666;
            font-size: 1.3rem;
            margin-bottom: 20px;
            font-weight: 400;
        }

        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #2f5ae5;
        }

        .user-info h3 {
            color: #2f5ae5;
            margin-bottom: 10px;
        }

        .user-info p {
            color: #666;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .status-badge {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #8B4000;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            margin: 20px 0;
            display: inline-block;
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .btn-refresh {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-refresh:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-logout {
            background: linear-gradient(135deg, #ff7b7b, #ff9a9e);
            color: white;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 123, 123, 0.4);
        }

        .info-text {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
            margin: 20px 0;
        }

        .contact-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .contact-info p {
            color: #1976d2;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(47, 90, 229, 0.1);
            color: #2f5ae5;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            border: 1px solid rgba(47, 90, 229, 0.3);
        }

        @media (max-width: 600px) {
            .waiting-container {
                padding: 30px 20px;
            }
            
            .waiting-container h1 {
                font-size: 1.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'components/header.php'; ?>
    <div class="waiting-container">
        <div class="waiting-icon"></div>
        
        <h1>Account Under Review</h1>
        <h2>Please wait while we verify your information</h2>
        
        <div class="status-badge">
            Status: Pending Approval
        </div>
        
        <div class="user-info">
            <h3>Your Registration Details</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($tutor['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($tutor['email']); ?></p>
            <p><strong>University:</strong> <?php echo htmlspecialchars($tutor['university']); ?></p>
            <p><strong>Faculty:</strong> <?php echo htmlspecialchars($tutor['faculty']); ?></p>
            <p><strong>Submitted:</strong> Registration completed successfully</p>
        </div>
        
        <div class="info-text">
            <p>Your teacher registration has been submitted successfully. Our admin team is currently reviewing your application and credentials.</p>
            <p>This process typically takes 1-2 business days. You will be able to access your dashboard once approved.</p>
        </div>
        
        <div class="action-buttons">
            <button onclick="refreshStatus()" class="btn btn-refresh">Check Status</button>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
        
        <div class="contact-info">
            <p>Need help? Contact our support team for assistance.</p>
        </div>
    </div>
    
    <div class="refresh-indicator">
        Auto-refresh in <span id="countdown">30</span>s
    </div>

    <script>
        // Auto-refresh functionality
        let countdownTimer = 30;
        
        function updateCountdown() {
            document.getElementById('countdown').textContent = countdownTimer;
            countdownTimer--;
            
            if (countdownTimer < 0) {
                window.location.reload();
            }
        }
        
        // Start countdown
        setInterval(updateCountdown, 1000);
        
        // Manual refresh function
        function refreshStatus() {
            window.location.reload();
        }
        
        // Show loading state when refreshing
        function showLoading() {
            document.querySelector('.btn-refresh').innerHTML = 'Checking...';
            document.querySelector('.btn-refresh').style.opacity = '0.7';
        }
    </script>
</body>
</html>