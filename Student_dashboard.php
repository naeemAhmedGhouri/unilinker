<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        #sidebar-wrapper {
            height: 100vh;
            overflow-y: auto;
            transition: margin-left 0.3s ease, padding-left 0.3s ease;
            background-color: #343a40;
            color: white;
            padding-left: 10px; /* Added margin for better alignment */
        }
        #sidebar-wrapper.collapsed {
            margin-left: -250px;
            padding-left: 0; /* Remove padding when collapsed */
        }
        .profile-section {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .profile-section img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
        }
        .profile-section h3 {
            margin: 10px 0 0;
        }
        .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .toggle-icon {
            font-size: 24px;
            cursor: pointer;
        }
        .toggle-icon:hover {
            color: #007bff;
        }
        #toggle-sidebar-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark text-white" id="sidebar-wrapper">
            <div class="profile-section">
                <img src="https://via.placeholder.com/100" alt="Profile Picture">
                <h3>Student Name</h3>
                <p>student@example.com</p>
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="#home" class="list-group-item list-group-item-action bg-transparent second-text fw-bold text-white">Home</a>
                <a href="#profile" class="list-group-item list-group-item-action bg-transparent second-text fw-bold text-white">Profile</a>
                <a href="#courses" class="list-group-item list-group-item-action bg-transparent second-text fw-bold text-white">Courses</a>
                <a href="#notifications" class="list-group-item list-group-item-action bg-transparent second-text fw-bold text-white">Notifications</a>
                <a href="#discussion" class="list-group-item list-group-item-action bg-transparent second-text fw-bold text-white">Discussion</a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100">
            <nav class="navbar navbar-expand-lg navbar-light bg-light py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="toggle-icon bi bi-list" id="toggle-sidebar-btn"></i>
                    <h2 class="fs-2 m-0 ms-3">Student Dashboard</h2>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <section id="home" class="my-4">
                    <h2>Welcome to the Dashboard</h2>
                    <p>This is your central hub for all activities.</p>
                </section>

                <section id="profile" class="my-4">
                    <h2>My Profile</h2>
                    <form id="profile-form">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name" required>

                        <label for="email" class="mt-3">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>

                        <label for="password" class="mt-3">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter a new password">

                        <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
                    </form>
                </section>

                <section id="courses" class="my-4">
                    <h2>My Courses</h2>
                    <div class="course-list">
                        <!-- Course data will be loaded dynamically -->
                    </div>
                </section>

                <section id="notifications" class="my-4">
                    <h2>Notifications</h2>
                    <ul class="notifications-list">
                        <!-- Notifications will be loaded dynamically -->
                    </ul>
                </section>

                <section id="discussion" class="my-4">
                    <h2>Discussion Forum</h2>
                    <p>Participate in course discussions here.</p>
                </section>
            </div>
        </div>
        <!-- /#page-content-wrapper -->
    </div>

    <!-- Bootstrap Icons and JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleSidebarBtn = document.getElementById('toggle-sidebar-btn');
        const sidebarWrapper = document.getElementById('sidebar-wrapper');

        toggleSidebarBtn.addEventListener('click', () => {
            sidebarWrapper.classList.toggle('collapsed');
        });
    </script>
</body>
</html>
