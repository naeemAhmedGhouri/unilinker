<?php
// Database connection include
include 'db.php'; // Ensure this file has valid database connection code

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collecting form data and sanitizing inputs
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']); // Plaintext storage
    $gender = $conn->real_escape_string($_POST['gender']);
    $teacher_name = $conn->real_escape_string($_POST['teacher_name']);
    $faculty = $conn->real_escape_string($_POST['faculty']);
    $department = $conn->real_escape_string(implode(", ", $_POST['department'] ?? []));
    $batch = $conn->real_escape_string(implode(", ", $_POST['batch'] ?? []));
    $university = $conn->real_escape_string($_POST['university']);

    // SQL query to insert data
    $sql = "INSERT INTO teachers (username, email, password, gender, teacher_name, faculty, department, batch, university) 
            VALUES ('$username', '$email', '$password', '$gender', '$teacher_name', '$faculty', '$department', '$batch', '$university')";

    // Execute query and check for success
    if ($conn->query($sql) === TRUE) {
        // echo "Registration successful!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Signup</title>
  <style>
    /* Import Google Font */
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap");

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: whitesmoke;
      font-family: "Poppins", sans-serif;
    }

    .container {
      width: 100%;
      max-width: 400px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
      color: #333;
    }

    .container header {
      font-size: 2rem;
      font-weight: 600;
      text-align: center;
      margin-bottom: 20px;
      color: #2f5ae5;
    }

    .input-box {
      margin-bottom: 20px;
    }

    .input-box label {
      font-weight: 500;
      margin-bottom: 8px;
      display: block;
      color: #2f5ae5;
    }

    .input-box input[type="text"],
    .input-box input[type="email"],
    .input-box input[type="password"],
    .input-box select {
      width: 100%;
      padding: 12px;
      border: 2px solid #2f5ae5;
      border-radius: 8px;
      font-size: 1rem;
      color: #333;
      background: whitesmoke;
      outline: none;
      transition: border-color 0.3s ease;
    }

    .input-box input:focus,
    .input-box select:focus {
      border-color: whitesmoke;
    }

    .checkbox-group {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .checkbox-group label {
      font-size: 1rem;
      color: #2f5ae5;
    }

    .checkbox-group input {
      margin-right: 5px;
    }

    .input-box button {
      width: 100%;
      padding: 12px;
      background: #2f5ae5;
      color: #fff;
      font-size: 1rem;
      font-weight: 500;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .input-box button:hover {
      background: #1e47b2;
    }

    .text {
      text-align: center;
      margin-top: 10px;
    }

    .text a {
      color: #2e53ca;
      text-decoration: none;
      font-weight: 500;
    }

    .text a:hover {
      text-decoration: underline;
    }

    @media (max-width: 500px) {
      .container {
        padding: 15px;
      }

      header {
        font-size: 1.5rem;
      }
    }

    h3 {
      color: #2f5ae5;
    }

    label {
      color: blue;
    }

    input[type="radio"]:checked,
    input[type="checkbox"]:checked {
      accent-color: blue;
    }

    .radio-group label {
      margin-right: 16px;
      margin-top: 5px;
      margin-bottom: 8px;
      padding: 5px 2px;
      color: blue;
    }

    .radio-group label:hover {
      color: darkblue;
    }
  </style>
</head>
<body>
  <section class="container">
    <header>Teacher Signup</header>
    <form method="POST" action="">
      <div class="input-box">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter username" required />
      </div>

      <div class="input-box">
        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required />
      </div>

      <div class="input-box">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required />
      </div>

      <div class="gender-box">
        <h3>Gender</h3>
        <label><input type="radio" name="gender" value="Male" required /> Male</label>
        <label><input type="radio" name="gender" value="Female" required /> Female</label>
      </div>

      <div class="input-box">
        <label>Teacher Name</label>
        <input type="text" name="teacher_name" placeholder="Enter Teacher Name" required />
      </div>

      <div class="input-box">
        <h3>Faculty</h3>
        <label><input type="radio" name="faculty" value="BS" required onclick="toggleDepartments()" /> BS</label>
        <label><input type="radio" name="faculty" value="BE" required onclick="toggleDepartments()" /> BE</label>
      </div>

      <div class="input-box" id="department-container" style="display: none;">
        <label>Department</label>
        <div id="bs-departments" style="display: none;">
          <label><input type="checkbox" name="department[]" value="IT" /> Information Technology</label>
          <label><input type="checkbox" name="department[]" value="CS" /> Computer Science</label>
        </div>
        <div id="be-departments" style="display: none;">
          <label><input type="checkbox" name="department[]" value="Civil" /> Civil Engineering</label>
          <label><input type="checkbox" name="department[]" value="Mechanical" /> Mechanical Engineering</label>
        </div>
      </div>

      <div class="input-box">
        <label>Batch</label>
        <div>
          <label><input type="checkbox" name="batch[]" value="2021" /> 2021</label>
          <label><input type="checkbox" name="batch[]" value="2022" /> 2022</label>
          <label><input type="checkbox" name="batch[]" value="2023" /> 2023</label>
        </div>
      </div>

      <div class="input-box">
        <label>University</label>
        <input type="text" name="university" placeholder="Enter your university name" required />
      </div>

      <div class="input-box">
        <button type="submit">Register</button>
        <div class="text">
          <h3>Already have an account? <a href="Login.php">Login now</a></h3>
        </div>
      </div>
    </form>
  </section>

  <script>
    function toggleDepartments() {
      const faculty = document.querySelector('input[name="faculty"]:checked').value;
      const bsDepartments = document.getElementById('bs-departments');
      const beDepartments = document.getElementById('be-departments');
      const departmentContainer = document.getElementById('department-container');

      // Show department container
      departmentContainer.style.display = 'block';

      if (faculty === 'BS') {
        bsDepartments.style.display = 'block';
        beDepartments.style.display = 'none';
      } else if (faculty === 'BE') {
        beDepartments.style.display = 'block';
        bsDepartments.style.display = 'none';
      }
    }
  </script>
</body>
</html>
