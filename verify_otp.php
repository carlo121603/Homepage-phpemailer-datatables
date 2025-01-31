<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_otp = $_POST['otp'];
    $user_id = $_POST['user_id'];
    
    // Verify OTP from session
    if (isset($_SESSION['otp']) && 
        $_SESSION['otp']['code'] === $submitted_otp && 
        $_SESSION['otp']['user_id'] == $user_id && 
        $_SESSION['otp']['expires'] > time()) {
        
        // Update the record to hidden instead of deleting
        $query = "UPDATE users_info SET is_hidden = 1 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Clear the OTP from session
            unset($_SESSION['otp']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to hide the record']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
