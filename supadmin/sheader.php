<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stylish Buttons</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #ff9966, #ff5e62);
            font-family: Arial, sans-serif;
        }
        .btn-container {
            display: flex;
            gap: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            background: #333;
            border-radius: 30px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }
        .btn:hover {
            background: #ffcc00;
            color: #333;
            transform: translateY(-3px);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="btn-container">
        <a href="home1.php" class="btn">Pending</a>
        <a href="approved.php" class="btn">Approved</a>
        <a href="rejected.php" class="btn">Rejected</a>
    </div>
</body>
</html>
