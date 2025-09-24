<title>Admin Panel</title>
</head>
<body>
    <h2>Pending Approvals</h2>
    <?php
    $result = $conn->query("SELECT id, name, email, status FROM tuters WHERE status = 'Pending'");
    while ($row = $result->fetch_assoc()) {
        echo "<p>{$row['name']} ({$row['email']})";
        echo "<form action='update_status.php' method='post'>";
        echo "<input type='hidden' name='id' value='{$row['id']}'>";
        echo "<button type='submit' name='approve'>Approve</button>";
        echo "<button type='submit' name='reject'>Reject</button>";
        echo "</form></p>";
    }
    ?>