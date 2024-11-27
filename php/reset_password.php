<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'your_database';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get token and new password
$token = $_POST['token'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];

if ($password !== $confirmPassword) {
    die("Passwords do not match.");
}

// Check token validity
$sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Hash new password and update
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashedPassword, $token);
    $stmt->execute();

    echo "Password has been reset successfully.";
} else {
    echo "Invalid or expired token.";
}
?>
