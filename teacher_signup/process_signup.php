<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form data ko fetch karna
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $teacher_name = $_POST['teacher_name'];
    $faculty = $_POST['faculty'];
    $department = $_POST['department'];
    $subject = $_POST['subject'];
    $university = $_POST['university'];

    // Aap yahan data ko database mein store kar sakte hain agar aap database use kar rahe hain.
    // Filhal ke liye, hum sirf data ko display karenge.
    
    echo "<h2>Signup Successful!</h2>";
    echo "<p>Username: $username</p>";
    echo "<p>Email: $email</p>";
    echo "<p>Gender: $gender</p>";
    echo "<p>Teacher Name: $teacher_name</p>";
    echo "<p>Faculty: $faculty</p>";
    echo "<p>Department: $department</p>";
    echo "<p>Subject: $subject</p>";
    echo "<p>University: $university</p>";
} else {
    echo "<p>Invalid request method.</p>";
}
?>
