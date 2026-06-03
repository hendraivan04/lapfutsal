<?php
// session_start() will be called across all pages that include this config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Makassar');

$host = 'localhost';
$user = 'root'; // default xampp user
$pass = '';     // default xampp password is empty
$db   = 'db_futsal';

// Initialize the database connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/**
 * Helper function to clean user input and prevent XSS or simple injection
 */
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return mysqli_real_escape_string($conn, $data);
}

/**
 * Helper function to flash sweetalert/session messages
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_msg'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

function display_flash_message() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        
        // Since we'll use sweetalert, we can output a block of JS
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$msg['type']}',
                    title: '" . ucfirst($msg['type']) . "',
                    text: '{$msg['message']}',
                });
            });
        </script>";
        
        unset($_SESSION['flash_msg']);
    }
}

?>
