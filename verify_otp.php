<?php
session_start();
$email = $_SESSION['user_email']; $otp_in = $_POST['otp_input'];
$file = 'requests.txt'; $lines = file($file); $success = false;

foreach ($lines as $i => $line) {
    $p = explode("|", trim($line));
    if ($p[0] == $email && $p[5] == $otp_in) {
        $lines[$i] = "$p[0]|$p[1]|$p[2]|$p[3]|queue|DONE\n";
        $success = true; break;
    }
}
if ($success) { file_put_contents($file, implode("", $lines)); header("Location: dashboard.php"); }
else { echo "<script>alert('Wrong OTP'); window.location='dashboard.php';</script>"; }
?>