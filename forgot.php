<?php
use PHPMailer\PHPMailer\PHPMailer; use PHPMailer\PHPMailer\Exception;
require 'phpmailer/Exception.php'; require 'phpmailer/PHPMailer.php'; require 'phpmailer/SMTP.php';
session_start();

$file = 'requests.txt';
$step = 1; // 1: Email, 2: OTP Verification, 3: Success

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // STEP 1: SEND OTP
    if (isset($_POST['send_otp'])) {
        $email = $_POST['email'];
        $found = false;
        if (file_exists($file)) {
            $lines = file($file);
            foreach ($lines as $line) {
                $p = explode("|", trim($line));
                if ($p[0] === $email) {
                    $found = true;
                    $otp = rand(100000, 999999);
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_otp'] = $otp;
                    $_SESSION['user_pass'] = $p[1]; // Store pass to show later
                    
                    // SEND THE EMAIL
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP(); $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
                        $mail->Username = 'amchostings@gmail.com'; $mail->Password = 'ybggxmindtmvezvy';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
                        $mail->SMTPOptions = array('ssl'=>array('verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true));
                        $mail->setFrom('amchostings@gmail.com', 'AMC HOSTING <noreply@amchostings.com>');
                        $mail->addAddress($email); $mail->isHTML(true);
                        $mail->Subject = "Password Recovery OTP";
                        if(file_exists('email.png')) { $mail->addEmbeddedImage('email.png', 'banner'); }
                        
                        $mail->Body = "<div style='background:#000; color:#fff; padding:20px; border:1px solid #D4AF37; font-family:sans-serif; text-align:center;'>
                            <img src='cid:banner' style='width:100%'>
                            <h2 style='color:#D4AF37;'>Password Recovery</h2>
                            <p>Use the code below to recover your AMC account:</p>
                            <h1 style='font-size:40px; color:#D4AF37;'>$otp</h1>
                        </div>";
                        $mail->send();
                        $step = 2;
                    } catch (Exception $e) { $error = "Mail failed to send."; }
                    break;
                }
            }
        }
        if (!$found) $error = "Email not found in our records.";
    }

    // STEP 2: VERIFY OTP
    if (isset($_POST['verify_otp'])) {
        if ($_POST['otp_input'] == $_SESSION['reset_otp']) {
            $step = 3;
        } else {
            $error = "Invalid OTP code. Try again.";
            $step = 2;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recover Password | AMC HOSTING</title>
    <style>
        body { background: #050505; color: #D4AF37; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #0a0a0a; border: 2px solid #D4AF37; padding: 40px; border-radius: 15px; width: 360px; text-align: center; box-shadow: 0 0 30px rgba(212, 175, 55, 0.1); }
        .heading { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .sub-heading { font-size: 11px; color: #888; text-transform: uppercase; margin-bottom: 25px; display: block; letter-spacing: 1px; }
        p { color: #ccc; font-size: 14px; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #000; border: 1px solid #333; color: #fff; border-radius: 5px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #D4AF37; }
        .btn { background: #D4AF37; color: #000; border: none; padding: 12px; width: 100%; font-weight: bold; cursor: pointer; border-radius: 5px; transition: 0.3s; }
        .btn:hover { background: #fff; transform: translateY(-2px); }
        .success-box { background: #111; padding: 15px; border: 1px solid #0f0; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="heading">AMC <span style="color:#fff;">HOSTING</span></div>
        <span class="sub-heading">Account Recovery</span>

        <?php if(isset($error)) echo "<p style='color:#ff4d4d; font-weight:bold;'>$error</p>"; ?>

        <?php if($step == 1): ?>
            <p>Lost your password? Enter your email to receive a verification code.</p>
            <form method="POST">
                <input type="email" name="email" placeholder="Registered Email Address" required>
                <button type="submit" name="send_otp" class="btn">SEND RECOVERY CODE</button>
            </form>
        <?php elseif($step == 2): ?>
            <p>We've sent a 6-digit code to your inbox. Check your spam folder if you don't see it.</p>
            <form method="POST">
                <input type="text" name="otp_input" placeholder="6-Digit OTP" required>
                <button type="submit" name="verify_otp" class="btn">VERIFY & SHOW PASSWORD</button>
            </form>
        <?php elseif($step == 3): ?>
            <p style="color:#0f0;">Verification Successful!</p>
            <div class="success-box">
                <small style="color:#888; text-transform:uppercase;">Your Password is:</small><br>
                <b style="font-size:22px; color:#0f0; letter-spacing:1px;"><?php echo $_SESSION['user_pass']; ?></b>
            </div>
        <?php endif; ?>

        <div style="margin-top: 25px; border-top: 1px solid #222; padding-top: 15px;">
            <a href="login.php" style="color:#888; font-size:12px; text-decoration:none;">&larr; Return to Login</a>
        </div>
    </div>
</body>
</html>