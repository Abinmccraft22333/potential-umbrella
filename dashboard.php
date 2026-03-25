<?php
use PHPMailer\PHPMailer\PHPMailer; use PHPMailer\PHPMailer\Exception;
require 'phpmailer/Exception.php'; require 'phpmailer/PHPMailer.php'; require 'phpmailer/SMTP.php';
session_start();

$app_name = "AMC HOSTING";
$panel_url = "https://panel.amchostings.com"; // Change this to your actual Pterodactyl/Panel URL
$email = $_SESSION['user_email'] ?? '';
$file = 'requests.txt'; $user_info = null;

if ($email && file_exists($file)) {
    $lines = file($file);
    foreach ($lines as $line) {
        $p = explode("|", trim($line));
        if ($p[0] == $email) { $user_info = $p; break; }
    }
}
if (!$user_info) { header("Location: index.php"); exit(); }

$current_status = strtolower(trim($user_info[4]));

// --- SUPPORT TICKET LOGIC ---
if (isset($_POST['ticket_msg'])) {
    $msg = htmlspecialchars($_POST['ticket_msg']);
    $timestamp = date('Y-m-d H:i');
    file_put_contents('tickets.txt', "$email|$msg|$timestamp\n", FILE_APPEND);
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
        $mail->Username = 'amchostings@gmail.com'; $mail->Password = 'ybggxmindtmvezvy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
        $mail->SMTPOptions = array('ssl'=>array('verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true));
        $mail->setFrom('amchostings@gmail.com', 'AMC SUPPORT');
        $mail->addAddress('amchostings@gmail.com'); 
        $mail->Subject = "New Ticket: $email";
        $mail->Body = "User $email is requesting help: \n\n $msg";
        $mail->send();
        $ticket_sent = true;
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $app_name; ?></title>
    <style>
        body { background: #050505; color: #fff; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 20px; display: flex; justify-content: center; min-height: 100vh; }
        .container { width: 100%; max-width: 500px; background: #0a0a0a; border: 1px solid #D4AF37; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); box-sizing: border-box; }
        .gold { color: #D4AF37; }
        
        /* Pulse Animation for Queue */
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
        .pulse { animation: pulse 2s infinite; }

        .status-card { background: #111; border: 1px solid #222; padding: 20px; border-radius: 10px; margin-bottom: 25px; text-align: center; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 11px; text-transform: uppercase; border: 1px solid; margin-top: 10px; }
        
        .status-queue { background: rgba(255, 165, 0, 0.1); color: #ffa500; border-color: #ffa500; }
        .status-active { background: rgba(0, 255, 0, 0.1); color: #0f0; border-color: #0f0; }

        .warning-notice { background: rgba(255, 0, 0, 0.1); border: 1px solid #ff4444; color: #ff4444; padding: 15px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; text-align: center; }
        
        .btn-panel { background: linear-gradient(45deg, #BF953F, #FCF6BA, #B38728); color: #000; text-decoration: none; display: block; text-align: center; padding: 15px; font-weight: bold; border-radius: 8px; margin-bottom: 20px; transition: 0.3s; box-shadow: 0 0 15px rgba(212, 175, 55, 0.3); }
        .btn-panel:hover { transform: scale(1.02); filter: brightness(1.1); }

        .notice-box { background: rgba(255,255,255,0.03); border-left: 3px solid #D4AF37; padding: 15px; font-size: 13px; color: #aaa; margin: 20px 0; }
        textarea { width: 100%; background: #000; border: 1px solid #333; color: #fff; padding: 15px; border-radius: 8px; box-sizing: border-box; resize: none; }
        .btn-ticket { background: #222; color: #fff; border: 1px solid #444; padding: 12px; width: 100%; cursor: pointer; border-radius: 8px; margin-top: 10px; font-weight: bold; transition: 0.3s; }
        .btn-ticket:hover { background: #D4AF37; color: #000; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand-header" style="text-align: center; margin-bottom: 20px;">
            <h1 class="gold">AMC <span style="color:#fff;">HOSTING</span></h1>
            <p style="color:#555; font-size: 12px;"><?php echo htmlspecialchars($email); ?></p>
        </div>

        <?php if ($current_status === 'active'): ?>
            <a href="<?php echo $panel_url; ?>" target="_blank" class="btn-panel">
                🚀 ACCESS GAME PANEL
            </a>
            <div style="text-align:center; font-size:12px; color:#0f0; margin-bottom:20px;">
                Your node has been successfully deployed!
            </div>
        <?php elseif ($current_status === 'queue'): ?>
            <div class="warning-notice pulse">
                ⚠️ <b>DEPLOYMENT IN PROGRESS</b><br>
                You are currently in the queue. Please wait while our administrators allocate your dedicated Minecraft resources.
            </div>
        <?php endif; ?>
        
        <div class="status-card">
            <div style="font-size: 12px; color: #555;">ASSIGNED SERVER</div>
            <div style="font-size: 18px; font-weight: bold; margin: 5px 0;"><?php echo htmlspecialchars($user_info[3]); ?></div>
            <div class="status-badge <?php echo ($current_status === 'active') ? 'status-active' : 'status-queue'; ?>">
                ● <?php echo strtoupper($current_status); ?>
            </div>
        </div>

        <hr style="border:0; border-top:1px solid #1a1a1a; margin:25px 0;">

        <h3 style="margin: 0; font-size: 16px;">Support Ticket</h3>
        <div class="notice-box">
            Describe your technical issue below. Our staff will respond via email within 24 hours.
        </div>

        <?php if(isset($ticket_sent)): ?>
            <p style="color:#0f0; font-size:13px; text-align:center;">✅ Ticket Sent Successfully!</p>
        <?php endif; ?>

        <form method="POST">
            <textarea name="ticket_msg" rows="3" placeholder="Example: Need help with plugins..." required></textarea>
            <button type="submit" class="btn-ticket">SUBMIT REQUEST</button>
        </form>
        
        <div style="text-align:center; margin-top:25px;">
            <a href="logout.php" style="color:#444; font-size:11px; text-decoration:none;">LOGOUT SESSION</a>
        </div>
    </div>
</body>
</html>