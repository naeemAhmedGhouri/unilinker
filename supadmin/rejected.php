<?php 
include '../components/connect.php';

// Get statistics
$pending_count = $conn->query("SELECT COUNT(*) FROM tutors WHERE status = 'Pending'")->fetchColumn();
$approved_count = $conn->query("SELECT COUNT(*) FROM tutors WHERE status = 'Approved'")->fetchColumn();
$rejected_count = $conn->query("SELECT COUNT(*) FROM tutors WHERE status = 'Rejected'")->fetchColumn();
$total_count = $conn->query("SELECT COUNT(*) FROM tutors")->fetchColumn();

// Handle Status Changes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    if (isset($_POST['approve'])) {
        $status = 'Approved';
        $stmt = $conn->prepare("UPDATE tutors SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo "<script>window.location.href='home1.php';</script>";
    }
    
    if (isset($_POST['reject'])) {
        $status = 'Rejected';
        $stmt = $conn->prepare("UPDATE tutors SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo "<script>window.location.href='home1.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Pending</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1a73e8',
                        secondary: '#6366f1',
                        success: '#10b981',
                        danger: '#ef4444',
                        warning: '#f59e0b'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
   <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wider">Admin Portal</h1>
                </div>
                
                <!-- Search Form and User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search..." 
                               class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                        <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button onclick="toggleDropdown()" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none transition-colors duration-200">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <svg id="chevron" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 opacity-0 invisible transform scale-95 transition-all duration-200 ease-out">
                            <div class="py-1">
                                <a href="#" onclick="handleProfile(event)" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Profile
                                </a>
                                <a href="#" onclick="handleSettings(event)" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Settings
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="#" onclick="handleLogout(event)" class="flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

  
   

        <!-- Content Area -->
       
                <div id="actionLog" class="text-gray-600">
                   
                </div>
           

    <script>
        let isDropdownOpen = false;

        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const chevron = document.getElementById('chevron');
            
            isDropdownOpen = !isDropdownOpen;
            
            if (isDropdownOpen) {
                dropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
                dropdown.classList.add('opacity-100', 'visible', 'scale-100');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                dropdown.classList.add('opacity-0', 'invisible', 'scale-95');
                dropdown.classList.remove('opacity-100', 'visible', 'scale-100');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        function handleProfile(event) {
            event.preventDefault();
            toggleDropdown();
            // Redirect to profile page
            // window.location.href = 'profile.php';
            
            // Demo log
            document.getElementById('actionLog').innerHTML = 
                '<div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">' +
                '<span class="text-blue-600 font-medium">Profile clicked!</span><br>' +
                'Redirecting to profile page...' +
                '</div>';
        }

        function handleSettings(event) {
            event.preventDefault();
            toggleDropdown();
            // Redirect to settings page
            // window.location.href = 'settings.php';
            
            // Demo log
            document.getElementById('actionLog').innerHTML = 
                '<div class="p-4 bg-green-50 border border-green-200 rounded-lg">' +
                '<span class="text-green-600 font-medium">Settings clicked!</span><br>' +
                'Opening settings panel...' +
                '</div>';
        }

        function handleLogout(event) {
            event.preventDefault();
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to logout?')) {
                document.getElementById('actionLog').innerHTML = 
                    '<div class="p-4 bg-red-50 border border-red-200 rounded-lg">' +
                    '<span class="text-red-600 font-medium">Logging out...</span><br>' +
                    'Please wait while we sign you out.' +
                    '</div>';
                
                toggleDropdown();
                
                // Redirect to logout - since superadmin folder to parent directory
                setTimeout(() => {
                    window.location.href = '../login1.php';
                }, 1000);
            } else {
                toggleDropdown();
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const button = event.target.closest('button[onclick="toggleDropdown()"]');
            
            if (!button && !dropdown.contains(event.target) && isDropdownOpen) {
                toggleDropdown();
            }
        });

        // Close dropdown on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && isDropdownOpen) {
                toggleDropdown();
            }
        });

        // Handle search functionality
        const searchInput = document.querySelector('input[placeholder="Search..."]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    const searchQuery = this.value;
                    if (searchQuery.trim()) {
                        document.getElementById('actionLog').innerHTML = 
                            '<div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">' +
                            '<span class="text-yellow-600 font-medium">Search:</span> ' + searchQuery +
                            '</div>';
                    }
                }
            });
        }
    </script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Tutors Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Tutors</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $total_count; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">All registered tutors</span>
                    </div>
                </div>
            </div>

            <!-- Pending Tutors Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $pending_count; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 px-6 py-3">
                    <div class="text-sm text-yellow-700">
                        <span class="font-medium">Currently viewing</span>
                    </div>
                </div>
            </div>

            <!-- Approved Tutors Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Approved</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $approved_count; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 px-6 py-3">
                    <div class="text-sm text-green-700">
                        <a href="approved.php" class="font-medium hover:underline">View approved tutors</a>
                    </div>
                </div>
            </div>

            <!-- Rejected Tutors Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Rejected</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $rejected_count; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-red-50 px-6 py-3">
                    <div class="text-sm text-red-700">
                        <a href="rejected.php" class="font-medium hover:underline">View rejected applications</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <a href="home1.php" class="btn-tab py-4 px-1 border-b-2 font-medium text-sm" data-tab="pending">
                        <span class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Pending</span>
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-yellow-800 bg-yellow-200 rounded-full"><?php echo $pending_count; ?></span>
                        </span>
                    </a>
                    <a href="approved.php" class="btn-tab py-4 px-1 border-b-2 font-medium text-sm" data-tab="approved">
                        <span class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Approved</span>
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-green-800 bg-green-200 rounded-full"><?php echo $approved_count; ?></span>
                        </span>
                    </a>
                    <a href="rejected.php" class="btn-tab py-4 px-1 border-b-2 font-medium text-sm" data-tab="rejected">
                        <span class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Rejected</span>
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-800 bg-red-200 rounded-full"><?php echo $rejected_count; ?></span>
                        </span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content Section -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-bold text-gray-900">Pending Tutors</h2>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="pending-count"><?php echo $rejected_count?> Rejected applications</span>
                </div>
            </div>

            <!-- User Cards Container - All cards remain visible -->
            <div class="grid gap-6" id="user-cards-container">
                <?php
                $stmt = $conn->prepare("SELECT * FROM tutors WHERE status = 'Rejected'");
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($result) > 0) {
                    foreach ($result as $row) {
                        echo "<div class='user-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 hover:-translate-y-1' id='card_{$row['id']}'>";
                        echo "<div class='p-6'>";
                        echo "<div class='flex items-start space-x-4'>";
                        
                        // Profile Image
                        echo "<div class='flex-shrink-0'>";
                        echo "<img src='../uploaded_files/{$row['image']}' alt='Profile' class='w-16 h-16 rounded-full object-cover ring-2 ring-gray-100'>";
                        echo "</div>";
                        
                        // User Info
                        echo "<div class='flex-1 min-w-0'>";
                        echo "<div class='grid grid-cols-1 md:grid-cols-3 gap-4'>";
                        echo "<div>";
                        echo "<p class='text-sm font-medium text-gray-500'>Full Name</p>";
                        echo "<p class='text-lg font-semibold text-gray-900'>{$row['name']}</p>";
                        echo "</div>";
                        echo "<div>";
                        echo "<p class='text-sm font-medium text-gray-500'>Email</p>";
                        echo "<p class='text-sm text-gray-900'>{$row['email']}</p>";
                        echo "</div>";
                        echo "<div>";
                        echo "<p class='text-sm font-medium text-gray-500'>University</p>";
                        echo "<p class='text-sm text-gray-900'>{$row['university']}</p>";
                        echo "</div>";
                        echo "</div>";
                        
                        // Status Badge
                        echo "<div class='mt-3'>";
                        echo "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'>";
                        echo "<svg class='w-3 h-3 mr-1' fill='currentColor' viewBox='0 0 8 8'>";
                        echo "<circle cx='4' cy='4' r='3'></circle>";
                        echo "</svg>";
                        echo "Rejected";
                        echo "</span>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        
                        // Action Buttons
                        echo "<div class='mt-6 flex items-center justify-between'>";
                        echo "<button onclick='viewProfile(\"{$row['id']}\")' class='inline-flex items-center px-4 py-2 text-sm font-medium text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors duration-200'>";
                        echo "<svg class='w-4 h-4 mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
                        echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path>";
                        echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'></path>";
                        echo "</svg>";
                        echo "View Details";
                        echo "</button>";
                        
                        echo "<div class='flex space-x-3'>";
                        echo "<form action='' method='post' onsubmit='return confirmAction(this)' class='inline'>";
                        echo "<input type='hidden' name='id' value='{$row['id']}'>";
                        echo "<button type='submit' name='approve' class='inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-success rounded-lg hover:bg-green-600 transition-colors duration-200'>";
                        echo "<svg class='w-4 h-4 mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
                        echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>";
                        echo "</svg>";
                        echo "Approve";
                        echo "</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='empty-state text-center py-12'>";
                    echo "<svg class='mx-auto h-12 w-12 text-gray-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'>";
                    echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'/>";
                    echo "</svg>";
                    echo "<h3 class='mt-2 text-sm font-medium text-gray-900'>No rejected tutors</h3>";
                    echo "<p class='mt-1 text-sm text-gray-500'>No tutors have been rejected yet.</p>";
                    echo "</div>";
                }

                // Handle Status Changes
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve'])) {
                    $id = $_POST['id'];
                    $status = 'Approved';

                    $stmt = $conn->prepare("UPDATE tutors SET status = :status WHERE id = :id");
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();

                    // Refresh page after action
                    echo "<script>window.location.href='rejected.php';</script>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal for Detailed Profile View -->
    <div id="profile-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div id="modal-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmAction(form) {
            return confirm("Are you sure you want to approve this tutor?");
        }

        function viewProfile(userId) {
            // Get tutor data from PHP
            <?php
            echo "const tutorData = {";
            foreach ($result as $row) {
                echo "'{$row['id']}': {";
                echo "id: '{$row['id']}',";
                echo "name: '{$row['name']}',";
                echo "username: '{$row['username']}',";
                echo "email: '{$row['email']}',";
                echo "university: '{$row['university']}',";
                echo "faculty: '{$row['faculty']}',";
                echo "gender: '{$row['gender']}',";
                echo "status: '{$row['status']}',";
                echo "image: '{$row['image']}'";
                echo "},";
            }
            echo "};";
            ?>

             const tutor = tutorData[userId];
            if (!tutor) return;

            // Create modal content
            const modalContent = `
                <div class="relative">
                    <!-- Header with gradient background -->
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-t-xl p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <img src="../uploaded_files/${tutor.image}" alt="Profile" class="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-lg">
                                <div class="text-white">
                                    <h3 class="text-2xl font-bold">${tutor.name}</h3>
                                    <p class="text-green-100">Approved Tutor</p>
                                </div>
                            </div>
                            <button onclick="hideProfile()" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Detailed Information -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Personal Information</h4>
                                <div class="space-y-3">
                                    
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-500">ID</p>
                                            <p class="font-medium">#${tutor.id}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-500">Username</p>
                                            <p class="font-medium">${tutor.username}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-500">Gender</p>
                                            <p class="font-medium">${tutor.gender}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Contact Information</h4>
                                <div class="space-y-3">
                                    
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-500">Email</p>
                                            <p class="font-medium">${tutor.email}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Academic Information</h4>
                                <div class="space-y-3">
                                    
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-500">University</p>
                                            <p class="font-medium">${tutor.university}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-500">Faculty</p>
                                            <p class="font-medium">${tutor.faculty}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 8.232a3.91 3.91 0 001.433-.143 4.001 4.001 0 01-1.433.143zM9 16.243V9.57a2 2 0 00-1.414-.586H5.414A2 2 0 004 10.414v7.072a2 2 0 002 2h2.586A2 2 0 009 18.072v-1.829z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-500">Status</p>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ${tutor.status}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons in detailed view -->
                        <div class="mt-8 flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button onclick="hideProfile()" class="inline-flex items-center px-6 py-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to List
                            </button>
                            
                             <form action="" method="post" onsubmit="return confirmAction('approve', '${tutor.name}')" class="inline">
        <input type="hidden" name="id" value="${tutor.id}">
        <button type="submit" name="approve" class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors duration-200 mr-3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Approve
        </button>
    </form>
                            </form>
                        </div>
                    </div>
                </div>
            `;

            // Show modal
            document.getElementById('modal-content').innerHTML = modalContent;
            document.getElementById('profile-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function hideProfile() {
            document.getElementById('profile-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('profile-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideProfile();
            }
        });

        // Tab management
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".btn-tab");
            
            // Set active tab based on current page
            tabs.forEach(tab => {
                tab.classList.remove('active');
                tab.classList.add('text-gray-500', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
                
                if (window.location.href.includes(tab.getAttribute("href")) || 
                    (window.location.pathname.includes('approved.php') && tab.dataset.tab === 'approved')) {
                    tab.classList.add('active');
                    tab.classList.remove('text-gray-500', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
                    tab.classList.add('text-primary', 'border-primary');
                }
            });
        });

        // Add hover effects and animations
        document.querySelectorAll('.user-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>