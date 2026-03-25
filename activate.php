<?php
session_start();
$email = $_SESSION['user_email'] ?? '';
$otp_in = $_POST['otp'] ?? '';
$file = 'requests.txt';
$lines = file($file);
$found = false;

foreach ($lines as $i => $line) {
    $p = explode("|", trim($line));
    if ($p[0] == $email && $p[3] == $otp_in) {
        $lines[$i] = "$p[0]|$p[1]|active|DONE\n";
        $found = true;
        break;
    }
}

if ($found) {
    file_put_contents($file, implode("", $lines));
    echo "<body style='background:#050505; color:#fff; text-align:center; font-family:sans-serif; padding-top:100px;'>
          <h1 style='color:#00ff00;'>✓ VERIFIED</h1>
          <p>Your account has been successfully created.</p>
          <p>When your server is ready, you will get a notification in email.</p>
          <a href='index.php' style='color:#D4AF37;'>Go Home</a></body>";
} else {
    echo "Invalid OTP. <a href='dashboard.php'>Try again</a>";
}
?>