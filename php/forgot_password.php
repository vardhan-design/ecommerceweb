<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

include 'db_connect.php';


if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get email from POST request
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Check if email exists
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Generate a unique token
    $token = bin2hex(random_bytes(50));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Save token and expiry in database
    $sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $token, $expiry, $email);
    $stmt->execute();

    // Send reset email
    $resetLink = "reset_password.php?token=" . $token;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'johnsnowtagerian@gmail.com'; // Your Gmail address
    $mail->Password = 'ilea ofrl udkt hnkp'; // App password from Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Set email sender and recipient
    $mail->setFrom('johnsnowtagerian@gmail.com', 'Sender Name');
            $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Hi,<br><br>Click <a href='$resetLink'>here</a> to reset your password. This link is valid for 1 hour.<br><br>Regards,<br>Your Website Team";

        $mail->send();
        echo "Reset link sent to your email.";
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "No account found with that email.";
}
?>
