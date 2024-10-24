<?php
// Include config file for database connection
include '../src/db.php';

// Include Composer's autoloader for PHPMailer
require '../vendor/autoload.php'; // Adjust path if necessary

// Include PHPMailer and OAuth libraries
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;

//require 'vendor/autoload.php'; // Assuming you installed PHPMailer via Composer

// Initialize an empty error array
$errors = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Simple validation
    if (empty($username) || empty($password) || empty($email)) {
        $errors[] = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    }

    // Check if username or email already exists in the database
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result(); // Store result to prevent issues with multiple queries

    if ($stmt->num_rows > 0) {
        $errors[] = "Username or email already taken!";
    }
    $stmt->close(); // Close the statement to free the result

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $stmt = $db->prepare("INSERT INTO users (username, password, email, verified) VALUES (?, ?, ?, 0)");
        $stmt->bind_param('sss', $username, $hashed_password, $email);
        $stmt->execute();
        $stmt->close(); // Close after execution

        // Generate a verification token
        $verification_token = bin2hex(random_bytes(16));

        // Insert verification token into the database
        $user_id = $db->insert_id;
        $stmt = $db->prepare("INSERT INTO email_verifications (user_id, token) VALUES (?, ?)");
        $stmt->bind_param('is', $user_id, $verification_token);
        $stmt->execute();
        $stmt->close(); // Close after execution

        // Send verification email using PHPMailer with OAuth
        $verification_link = "http://yourdomain.com/verify.php?token=$verification_token";

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->AuthType   = 'XOAUTH2';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
            $mail->Port       = 587;                                    // TCP port to connect to

            // OAuth2 Settings
            $mail->setOAuth(new OAuth([
                'provider' => new League\OAuth2\Client\Provider\Google([
                    'clientId'     => '',
                    'clientSecret' => '',
                ]),
                'clientId'     => '',
                'clientSecret' => '',
                'refreshToken' => '',
                'userName'     => 'joelmuyiwa1@gmail.com',
            ]));

            //Recipients
            $mail->setFrom('your-email@gmail.com', 'Your App Name');
            $mail->addAddress($email);     // Add a recipient

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Email Verification';
            $mail->Body    = "Click the link below to verify your email:<br><a href='$verification_link'>$verification_link</a>";
            $mail->AltBody = "Click the link below to verify your email:\n$verification_link";

            $mail->send();

            // Redirect to index.php after successful registration
            header("Location: index.php?registration=success");
            exit();
        } catch (Exception $e) {
            $errors[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form action="register.php" method="post">
        <div>
            <label>Username</label>
            <input type="text" name="username">
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password">
        </div>
        <div>
            <label>Email</label>
            <input type="text" name="email">
        </div>
        <div>
            <input type="submit" value="Register">
        </div>
        <?php
        // Display errors if any
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p style='color:red;'>$error</p>";
            }
        }
        ?>
    </form>
</body>
</html>
