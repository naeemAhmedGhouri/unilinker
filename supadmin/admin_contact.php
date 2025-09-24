<?php 
include '../components/connect.php';

// Handle message deletion
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
   
   $delete_message = $conn->prepare("DELETE FROM `contact` WHERE id = ?");
   $delete_message->execute([$delete_id]);
   $message[] = 'Message deleted successfully!';
}

// Handle mark as read/unread
if(isset($_GET['toggle_status'])){
   $toggle_id = $_GET['toggle_status'];
   $toggle_id = filter_var($toggle_id, FILTER_SANITIZE_STRING);
   
   // First get current status
   $get_status = $conn->prepare("SELECT status FROM `contact` WHERE id = ?");
   $get_status->execute([$toggle_id]);
   $current = $get_status->fetch(PDO::FETCH_ASSOC);
   
   if($current){
      $new_status = ($current['status'] == 'unread') ? 'read' : 'unread';
      $update_status = $conn->prepare("UPDATE `contact` SET status = ? WHERE id = ?");
      $update_status->execute([$new_status, $toggle_id]);
      $message[] = 'Status updated successfully!';
   }
}

// Get statistics
$total_messages = $conn->prepare("SELECT COUNT(*) as total FROM `contact`");
$total_messages->execute();
$total = $total_messages->fetch(PDO::FETCH_ASSOC)['total'];

$unread_messages = $conn->prepare("SELECT COUNT(*) as unread FROM `contact` WHERE status = 'unread' OR status IS NULL");
$unread_messages->execute();
$unread = $unread_messages->fetch(PDO::FETCH_ASSOC)['unread'];

$read_messages = $conn->prepare("SELECT COUNT(*) as read FROM `contact` WHERE status = 'read'");
$read_messages->execute();
$read = $read_messages->fetch(PDO::FETCH_ASSOC)['read'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Contact Messages</title>
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
   <header class="bg-white shadow-sm border-b sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
         <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
               <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wider">Contact Messages</h1>
            </div>
            
            <!-- Search Form and User Menu -->
            <div class="flex items-center space-x-4">
               <div class="relative">
                  <input type="text" placeholder="Search messages..." 
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
                        <a href="home1.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                           <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v2"></path>
                           </svg>
                           Dashboard
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="../login1.php" class="flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-150">
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

   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <?php
      if(isset($message)){
         foreach($message as $msg){
            echo '<div class="bg-success/10 border border-success/20 text-green-800 px-4 py-3 rounded-lg text-sm mb-6">'.$msg.'</div>';
         }
      }
      ?>

      <!-- Dashboard Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
         <!-- Total Messages Card -->
         <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
               <div class="flex items-center">
                  <div class="flex-shrink-0">
                     <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                     </div>
                  </div>
                  <div class="ml-4">
                     <p class="text-sm font-medium text-gray-500">Total Messages</p>
                     <p class="text-2xl font-bold text-gray-900"><?php echo $total; ?></p>
                  </div>
               </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
               <div class="text-sm text-gray-600">
                  <span class="font-medium">All contact messages</span>
               </div>
            </div>
         </div>

         <!-- Unread Messages Card -->
         <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
               <div class="flex items-center">
                  <div class="flex-shrink-0">
                     <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V8a3 3 0 013-3h10a3 3 0 013 3v4"></path>
                        </svg>
                     </div>
                  </div>
                  <div class="ml-4">
                     <p class="text-sm font-medium text-gray-500">Unread</p>
                     <p class="text-2xl font-bold text-gray-900"><?php echo $unread; ?></p>
                  </div>
               </div>
            </div>
            <div class="bg-yellow-50 px-6 py-3">
               <div class="text-sm text-yellow-700">
                  <span class="font-medium">Need attention</span>
               </div>
            </div>
         </div>

         <!-- Read Messages Card -->
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
                     <p class="text-sm font-medium text-gray-500">Read</p>
                     <p class="text-2xl font-bold text-gray-900"><?php echo $read; ?></p>
                  </div>
               </div>
            </div>
            <div class="bg-green-50 px-6 py-3">
               <div class="text-sm text-green-700">
                  <span class="font-medium">Processed messages</span>
               </div>
            </div>
         </div>
      </div>

      <!-- Messages Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
         <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
               <h2 class="text-xl font-bold text-gray-900">All Messages</h2>
               <div class="flex items-center space-x-2 text-sm text-gray-500">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  <span><?php echo $total; ?> Total Messages</span>
               </div>
            </div>
         </div>

         <div class="divide-y divide-gray-200">
            <?php
            $select_messages = $conn->prepare("SELECT * FROM `contact` ORDER BY id DESC");
            $select_messages->execute();

            if($select_messages->rowCount() > 0){
               while($message_row = $select_messages->fetch(PDO::FETCH_ASSOC)){
                  $status = isset($message_row['status']) && $message_row['status'] != '' ? $message_row['status'] : 'unread';
                  $status_class = $status == 'read' ? 'bg-white' : 'bg-blue-50';
            ?>
            
            <div class="<?php echo $status_class; ?> p-6 hover:bg-gray-50 transition-colors duration-200">
               <div class="flex items-start justify-between">
                  <div class="flex-1 min-w-0">
                     <div class="flex items-center space-x-3 mb-3">
                        <div class="flex-shrink-0">
                           <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                              </svg>
                           </div>
                        </div>
                        <div class="flex-1 min-w-0">
                           <div class="flex items-center space-x-2">
                              <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($message_row['name']); ?></h3>
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status == 'read' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                 <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3"></circle>
                                 </svg>
                                 <?php echo ucfirst($status); ?>
                              </span>
                           </div>
                           <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
                              <div class="flex items-center text-sm text-gray-600">
                                 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                 </svg>
                                 <?php echo htmlspecialchars($message_row['email']); ?>
                              </div>
                              <div class="flex items-center text-sm text-gray-600">
                                 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                 </svg>
                                 Roll: <?php echo htmlspecialchars($message_row['roll']); ?>
                              </div>
                              <div class="flex items-center text-sm text-gray-600">
                                 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                 </svg>
                                 <?php echo htmlspecialchars($message_row['university']); ?>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-primary mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Message:</p>
                        <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($message_row['message'])); ?></p>
                     </div>
                  </div>
                  
                  <div class="flex items-center space-x-2 ml-4">
                     <a href="?toggle_status=<?php echo $message_row['id']; ?>" 
                        class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 <?php echo $status == 'read' ? 'text-yellow-700 bg-yellow-100 hover:bg-yellow-200' : 'text-green-700 bg-green-100 hover:bg-green-200'; ?>">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M<?php echo $status == 'read' ? '15 12a3 3 0 11-6 0 3 3 0 016 0z' : '9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'; ?>"></path>
                           <?php if($status == 'read'): ?>
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                           <?php endif; ?>
                        </svg>
                        <?php echo $status == 'read' ? 'Mark Unread' : 'Mark Read'; ?>
                     </a>
                     <a href="?delete=<?php echo $message_row['id']; ?>" 
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 transition-colors duration-200"
                        onclick="return confirm('Are you sure you want to delete this message?')">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                     </a>
                  </div>
               </div>
            </div>

            <?php 
               }
            } else {
            ?>
            
            <div class="text-center py-12">
               <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
               </svg>
               <h3 class="mt-2 text-sm font-medium text-gray-900">No messages found</h3>
               <p class="mt-1 text-sm text-gray-500">There are no contact messages at the moment.</p>
            </div>

            <?php } ?>
         </div>
      </div>
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
const searchInput = document.querySelector('input[placeholder="Search messages..."]');
if (searchInput) {
    searchInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            const searchQuery = this.value.toLowerCase();
            const messages = document.querySelectorAll('.divide-y > div');
            
            messages.forEach(message => {
                const text = message.textContent.toLowerCase();
                if (text.includes(searchQuery) || searchQuery === '') {
                    message.style.display = 'block';
                } else {
                    message.style.display = 'none';
                }
            });
        }
    });
    
    // Real-time search
    searchInput.addEventListener('input', function(event) {
        const searchQuery = this.value.toLowerCase();
        const messages = document.querySelectorAll('.divide-y > div');
        
        messages.forEach(message => {
            const text = message.textContent.toLowerCase();
            if (text.includes(searchQuery) || searchQuery === '') {
                message.style.display = 'block';
            } else {
                message.style.display = 'none';
            }
        });
    });
}

// Add smooth transitions and hover effects
document.addEventListener('DOMContentLoaded', function() {
    // Add loading animation for actions
    const actionLinks = document.querySelectorAll('a[href*="toggle_status"], a[href*="delete"]');
    actionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.href.includes('delete') && !confirm('Are you sure you want to delete this message?')) {
                e.preventDefault();
                return;
            }
            
            // Add loading state
            const originalHTML = this.innerHTML;
            this.innerHTML = '<svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Loading...';
            this.style.pointerEvents = 'none';
        });
    });
});
</script>

</body>
</html>