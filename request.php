<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

session_start();

// --- CONFIGURATION ---
$app_name = "AMC HOSTING";
$file = 'requests.txt';
$selected_plan = $_GET['plan'] ?? 'Free';

// --- THE MASTER EMAIL FUNCTION ---
function sendOTPEmail($to, $otp, $app_name) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'amchostings@gmail.com'; 
        $mail->Password = 'ybggxmindtmvezvy'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPOptions = array(
            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
        );

        $mail->setFrom('amchostings@gmail.com', $app_name . " Support");
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = "[$otp] Your AMC Deployment Code";

        // Embed Banner Image
        if (file_exists('email.png')) {
            $mail->addEmbeddedImage('email.png', 'banner');
        }

        $mail->Body = "
        <div style='background:#050505; max-width:550px; margin:auto; border:1px solid #D4AF37; font-family:sans-serif; overflow:hidden;'>
            <img src='cid:banner' style='width:100%; display:block;'>
            <div style='padding:30px; text-align:center; color:#fff;'>
                <h2 style='color:#D4AF37; margin-top:0;'>SECURITY VERIFICATION</h2>
                <p style='color:#ccc;'>Enter the 6-digit code below to authorize your Minecraft Node allocation.</p>
                <div style='background:#111; padding:20px; border:1px solid #333; display:inline-block; margin:20px 0;'>
                    <h1 style='color:#D4AF37; font-size:48px; margin:0; letter-spacing:10px;'>$otp</h1>
                </div>
                <p style='color:#555; font-size:11px;'>This code was requested for: $to</p>
            </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// --- 1. RESEND LOGIC ---
if (isset($_GET['resend']) && isset($_SESSION['temp_email'])) {
    if (time() - $_SESSION['last_otp_time'] < 60) {
        $error = "Please wait 60 seconds before resending.";
    } else {
        $new_otp = rand(111111, 999999);
        $email = $_SESSION['temp_email'];
        
        $lines = file($file);
        $new_lines = [];
        foreach($lines as $line) {
            $p = explode("|", trim($line));
            if($p[0] === $email) {
                $line = "$p[0]|$p[1]|$p[2]|$p[3]|verifying|$new_otp\n";
            }
            $new_lines[] = $line;
        }
        file_put_contents($file, implode("", $new_lines));
        
        sendOTPEmail($email, $new_otp, $app_name);
        $_SESSION['last_otp_time'] = time();
        $show_otp_form = true;
    }
}

// --- 2. INITIAL SUBMISSION (AFTER TOS) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $s_name = $_POST['server_name'];
    $plan = $_POST['plan'];
    $otp = rand(111111, 999999);

    // Save as verifying
    file_put_contents($file, "$email|$pass|$plan|$s_name|verifying|$otp\n", FILE_APPEND);
    
    $_SESSION['temp_email'] = $email;
    $_SESSION['last_otp_time'] = time();
    
    sendOTPEmail($email, $otp, $app_name);
    $show_otp_form = true;
}

// --- 3. VERIFY OTP ---
if (isset($_POST['do_verify'])) {
    $user_otp = $_POST['otp_input'];
    $email = $_SESSION['temp_email'];
    
    $lines = file($file);
    $found = false;
    $new_lines = [];

    foreach ($lines as $line) {
        $p = explode("|", trim($line));
        if ($p[0] === $email && trim($p[5] ?? '') === $user_otp) {
            $line = "$p[0]|$p[1]|$p[2]|$p[3]|queue|DONE\n";
            $found = true;
        }
        $new_lines[] = $line;
    }

    if ($found) {
        file_put_contents($file, implode("", $new_lines));
        $_SESSION['user_email'] = $email;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid verification code. Please check your email.";
        $show_otp_form = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deploy | <?php echo $app_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background:#050505; color:#D4AF37; font-family:'Segoe UI', sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
        .card { background:#0a0a0a; padding:35px; border-radius:15px; border:1px solid #D4AF37; width:100%; max-width:400px; text-align:center; box-shadow:0 10px 50px rgba(0,0,0,0.7); }
        
        input { width:100%; padding:14px; margin:10px 0; background:#000; border:1px solid #222; color:#fff; border-radius:8px; box-sizing:border-box; text-align:center; transition:0.3s; }
        input:focus { border-color:#D4AF37; outline:none; }
        
        .btn { background:linear-gradient(45deg, #BF953F, #FCF6BA, #B38728); color:#000; border:none; padding:16px; width:100%; font-weight:bold; cursor:pointer; border-radius:8px; margin-top:10px; text-transform:uppercase; letter-spacing:1px; }
        .btn:disabled { background:#222; color:#555; cursor:not-allowed; }

        /* ToS Modal */
        .modal { display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.95); align-items:center; justify-content:center; padding:20px; }
        .modal-content { background:#0a0a0a; border:2px solid #D4AF37; padding:25px; border-radius:12px; max-width:450px; width:100%; }
        .tos-box { height:220px; overflow-y:scroll; background:#000; padding:15px; border:1px solid #222; margin:15px 0; font-size:12px; color:#aaa; text-align:left; line-height:1.6; }
        .tos-box::-webkit-scrollbar { width:5px; }
        .tos-box::-webkit-scrollbar-thumb { background:#D4AF37; }

        .cert-box { background:#111; border:1px dashed #D4AF37; padding:15px; margin:15px 0; border-radius:8px; font-size:11px; text-align:left; color:#888; }
        #timer-text { font-size:12px; color:#555; margin-top:15px; }
    </style>
</head>
<body>

    <div id="tosModal" class="modal">
        <div class="modal-content">
            <h3 style="margin:0; color:#D4AF37;">SERVER USE AGREEMENT</h3>
            <div id="tosScroll" class="tos-box">
                <b style="color:#fff;">1. PERFORMANCE & TPS</b><br>
                Users must not create "Lag Machines" or clock-loop circuits designed to intentionally lower Server TPS. We monitor performance 24/7.<br><br>
                <b style="color:#fff;">2. RESOURCE ABUSE</b><br>
                Crypto-mining, bot-hosting, or using the node as a proxy/VPN is strictly prohibited. Servers found doing this will be deleted without warning.<br><br>
                <b style="color:#fff;">3. STORAGE POLICY</b><br>
                Free tier nodes use high-speed NVMe storage. To ensure availability, servers inactive for 7+ days will have their world data purged.<br><br>
                <b style="color:#fff;">4. EULA</b><br>
                You must comply with the Mojang/Microsoft Minecraft EULA at all times.<br><br>
                <i style="color:#444;">--- END OF TERMS ---</i>
            </div>
            <button id="agreeBtn" class="btn" disabled onclick="submitFinal()">I AGREE & SEND CODE</button>
        </div>
    </div>

    <div class="card">
        <h1>AMC <span style="color:#fff;">HOSTING</span></h1>

        <?php if (isset($error)) echo "<p style='color:#f44; font-size:12px;'>$error</p>"; ?>

        <?php if (isset($show_otp_form)): ?>
            <div class="cert-box">
                <b style="color:#D4AF37;">📜 ToS COMPLIANCE CERTIFICATE</b><br>
                User: <?php echo $_SESSION['temp_email']; ?><br>
                ID: AMC-<?php echo strtoupper(substr(md5($_SESSION['temp_email']), 0, 8)); ?><br>
                Status: <span style="color:#0f0;">Pending OTP</span>
            </div>

            <form method="POST">
                <input type="text" name="otp_input" placeholder="000000" maxlength="6" required style="font-size:28px; letter-spacing:8px;">
                <button type="submit" name="do_verify" class="btn">VERIFY & DEPLOY</button>
            </form>

            <div id="timer-text">
                Didn't get an email? <span id="timer-count">Wait 60s</span>
                <a id="resend-link" href="request.php?resend=1" style="display:none; color:#D4AF37;">Resend Now</a>
            </div>

            <script>
                let sec = 60;
                let t = setInterval(() => {
                    sec--;
                    document.getElementById('timer-count').innerText = "Wait " + sec + "s";
                    if(sec <= 0) {
                        clearInterval(t);
                        document.getElementById('timer-count').style.display = 'none';
                        document.getElementById('resend-link').style.display = 'inline';
                    }
                }, 1000);
            </script>

        <?php else: ?>
            <p style="font-size:12px; color:#555;">Allocate your dedicated Minecraft node.</p>
            <form id="mainForm" method="POST">
                <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selected_plan); ?>">
                <input type="text" id="s_name" name="server_name" placeholder="Server Name" required>
                <input type="email" id="u_email" name="email" placeholder="Email Address" required>
                <input type="password" id="u_pass" name="password" placeholder="Panel Password" required>
                
                <button type="button" class="btn" onclick="openTos()">DEPLOY SERVER</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        const modal = document.getElementById('tosModal');
        const tosBox = document.getElementById('tosScroll');
        const agreeBtn = document.getElementById('agreeBtn');

        function openTos() {
            if(document.getElementById('s_name').value && document.getElementById('u_email').value && document.getElementById('u_pass').value) {
                modal.style.display = 'flex';
            } else {
                alert("Please fill in all details first.");
            }
        }

        tosBox.addEventListener('scroll', () => {
            if (tosBox.scrollHeight - tosBox.scrollTop <= tosBox.clientHeight + 10) {
                agreeBtn.disabled = false;
            }
        });

        function submitFinal() {
            document.getElementById('mainForm').submit();
        }
    </script>
</body>
</html>