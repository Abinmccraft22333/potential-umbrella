<?php
use PHPMailer\PHPMailer\PHPMailer; use PHPMailer\PHPMailer\Exception;
require 'phpmailer/Exception.php'; require 'phpmailer/PHPMailer.php'; require 'phpmailer/SMTP.php';
session_start();

$file = 'requests.txt';
$ticket_file = 'tickets.txt';
$app_name = "AMC HOSTING";

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: login.php"); exit(); }

// --- THE MASTER EMAIL FUNCTION ---
function sendAMCEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
        $mail->Username = 'amchostings@gmail.com'; $mail->Password = 'ybggxmindtmvezvy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
        $mail->SMTPOptions = array('ssl'=>array('verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true));
        
        $mail->setFrom('amchostings@gmail.com', 'AMC HOSTING <noreply@amchostings.com>');
        $mail->addAddress($to); $mail->isHTML(true); $mail->Subject = $subject;
        
        if(file_exists('email.png')) { $mail->addEmbeddedImage('email.png', 'banner'); }

        $mail->Body = "
        <div style='background:#050505; max-width:600px; margin:auto; border:2px solid #D4AF37; font-family:sans-serif; overflow:hidden;'>
            <img src='cid:banner' style='width:100%; display:block;'>
            <div style='padding:30px; text-align:center;'>
                <h2 style='color:#D4AF37;'>$subject</h2>
                <div style='color:#eee; line-height:1.6;'>$message</div>
                <p style='color:#444; font-size:10px; margin-top:20px;'>AMC HOSTING SYSTEM</p>
            </div>
        </div>";
        $mail->send();
        return true;
    } catch (Exception $e) { return false; }
}

// --- BROADCAST LOGIC ---
if (isset($_POST['do_broadcast'])) {
    $msg = nl2br(htmlspecialchars($_POST['broadcast_content']));
    if (file_exists($file)) {
        $users = file($file);
        foreach ($users as $u) {
            $p = explode("|", trim($u));
            if(!empty($p[0])) sendAMCEmail($p[0], "Global Announcement", $msg);
        }
        $msg_status = "Broadcast sent successfully!";
    }
}

// --- TICKET REPLY LOGIC ---
if (isset($_POST['reply_ticket'])) {
    $user_email = $_POST['target_user'];
    $reply_text = nl2br(htmlspecialchars($_POST['reply_content']));
    sendAMCEmail($user_email, "Support Ticket Reply", "<b>Admin Reply:</b><br><br>$reply_text");
    $msg_status = "Reply sent to $user_email";
}

// --- DELETE TICKET LOGIC ---
if (isset($_GET['del_ticket'])) {
    $index = $_GET['del_ticket'];
    $tix = file($ticket_file);
    unset($tix[$index]);
    file_put_contents($ticket_file, implode("", $tix));
    header("Location: admin.php"); exit();
}

// --- SERVER ACTIONS ---
if (isset($_GET['action'])) {
    $act = $_GET['action']; $target = $_GET['email'];
    $lines = file($file); $new_lines = [];
    foreach ($lines as $line) {
        $p = explode("|", trim($line));
        if ($p[0] === $target) {
            if ($act == 'revoke') continue;
            $status = ($act == 'activate') ? 'active' : 'suspended';
            $line = "$p[0]|$p[1]|$p[2]|$p[3]|$status|DONE\n";
            sendAMCEmail($target, "Server Status Update", "Your server is now: ".strtoupper($status));
        }
        $new_lines[] = $line;
    }
    file_put_contents($file, implode("", $new_lines));
    header("Location: admin.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMC ADMIN PANEL</title>
    <style>
        body { background: #050505; color: #D4AF37; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 20px; }
        .admin-container { max-width: 1000px; margin: auto; }
        h1 { text-align: center; color: #fff; margin-bottom: 30px; letter-spacing: 2px; }
        
        .panel { background: #0a0a0a; border: 1px solid #222; padding: 20px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
        .panel h2 { margin-top: 0; font-size: 18px; text-transform: uppercase; color: #fff; border-bottom: 1px solid #333; padding-bottom: 10px; }
        
        textarea { width: 100%; background: #000; border: 1px solid #444; color: #fff; padding: 12px; border-radius: 6px; box-sizing: border-box; resize: vertical; }
        
        .btn-gold { 
            background: linear-gradient(45deg, #BF953F, #FCF6BA, #B38728); 
            border: none; padding: 12px 25px; font-weight: bold; cursor: pointer; 
            border-radius: 6px; margin-top: 10px; text-transform: uppercase; transition: 0.3s;
        }
        .btn-gold:hover { transform: scale(1.02); filter: brightness(1.1); }

        /* Responsive Table Wrapper */
        .table-wrapper { width: 100%; overflow-x: auto; border-radius: 8px; background: #000; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th, td { padding: 15px; border-bottom: 1px solid #1a1a1a; text-align: left; font-size: 14px; }
        th { background: #111; color: #D4AF37; }
        tr:hover { background: #080808; }

        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .active { background: rgba(0, 255, 0, 0.1); color: #0f0; }
        .suspended { background: rgba(255, 0, 0, 0.1); color: #f00; }

        @media (max-width: 600px) {
            body { padding: 10px; }
            h1 { font-size: 22px; }
            .panel { padding: 15px; }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>AMC <span style="color:#D4AF37;">ADMIN</span> CONTROL</h1>

    <?php if(isset($msg_status)): ?>
        <div style="color:#000; background: #D4AF37; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center;">
            <?php echo $msg_status; ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <h2>📢 Global Broadcast</h2>
        <form method="POST">
            <textarea name="broadcast_content" rows="3" placeholder="Write an announcement to all users..." required></textarea>
            <button type="submit" name="do_broadcast" class="btn-gold">🚀 SEND BROADCAST</button>
        </form>
    </div>

    <div class="panel">
        <h2>🎫 Support Tickets</h2>
        <div class="table-wrapper">
            <table>
                <tr><th>User</th><th>Message</th><th>Action</th></tr>
                <?php
                if(file_exists($ticket_file)){
                    $tix = file($ticket_file);
                    foreach($tix as $idx => $t){
                        $tp = explode("|", trim($t));
                        $t_user = $tp[0] ?? 'Unknown';
                        $t_msg = $tp[1] ?? 'No Message';
                        echo "<tr>
                            <td>$t_user</td>
                            <td style='color:#ccc; font-size:12px;'>$t_msg</td>
                            <td>
                                <form method='POST' style='display:flex; gap:5px;'>
                                    <input type='hidden' name='target_user' value='$t_user'>
                                    <input type='text' name='reply_content' placeholder='Reply...' required style='background:#111; color:#fff; border:1px solid #333; padding:5px; border-radius:4px; font-size:12px;'>
                                    <button type='submit' name='reply_ticket' style='background:#D4AF37; border:none; padding:5px 10px; cursor:pointer; font-weight:bold; border-radius:4px;'>REPLY</button>
                                </form>
                                <a href='admin.php?del_ticket=$idx' style='color:#f44; text-decoration:none; font-size:18px; margin-left:10px;' onclick='return confirm(\"Clear?\")'>✖</a>
                            </td>
                        </tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>

    <div class="panel">
        <h2>👥 User Database</h2>
        <div class="table-wrapper">
            <table>
                <tr><th>Email</th><th>Server</th><th>Status</th><th>Control</th></tr>
                <?php
                if(file_exists($file)){
                    $lines = file($file);
                    foreach($lines as $line){
                        $p = explode("|", trim($line)); if(count($p)<4)continue;
                        $stat_class = ($p[4] == 'active') ? 'active' : 'suspended';
                        echo "<tr>
                            <td style='font-size:12px;'>$p[0]</td>
                            <td><b>$p[3]</b></td>
                            <td><span class='status-badge $stat_class'>".strtoupper($p[4])."</span></td>
                            <td>
                                <a href='admin.php?action=activate&email=$p[0]' style='color:#0f0; text-decoration:none; font-weight:bold; font-size:12px;'>ACTIVATE</a> 
                                <span style='color:#333'>|</span>
                                <a href='admin.php?action=suspend&email=$p[0]' style='color:#f90; text-decoration:none; font-weight:bold; font-size:12px;'>SUSPEND</a>
                            </td>
                        </tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>
    
    <div style="text-align:center;">
        <a href="logout.php" style="color:#555; text-decoration:none; font-size:12px;">Logout Admin Panel</a>
    </div>
</div>

</body>
</html>