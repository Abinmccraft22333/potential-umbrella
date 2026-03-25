<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email']; 
    $pass = $_POST['password'];

    // ADMIN CHECK
    if ($email === "amchostings@gmail.com" && $pass === "9847757186") {
        $_SESSION['admin'] = true; 
        $_SESSION['user_email'] = $email; // Optional: keep track of admin email too
        header("Location: admin.php");
        exit();
    } 
        if ($email === "nattupokk03@gmail.com" && $pass === "nikhildas0301") {
        $_SESSION['admin'] = true; 
        $_SESSION['user_email'] = $email; // Optional: keep track of admin email too
        header("Location: admin.php");
        exit();
    }
    // REGULAR USER CHECK
    else {
        $_SESSION['user_email'] = $email; 
        $_SESSION['admin'] = false; // Ensure they aren't marked as admin
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AMC HOSTING</title>
    <style>
        body { 
            background: #050505; 
            color: #D4AF37; 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        form { 
            background: #0a0a0a; 
            padding: 40px 30px; 
            border-radius: 15px; 
            border: 2px solid #D4AF37; 
            width: 100%;
            max-width: 360px; 
            text-align: center; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .heading {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .sub-heading {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 25px;
            display: block;
            letter-spacing: 1px;
        }
        input { 
            width: 100%; 
            padding: 14px; 
            margin: 10px 0; 
            background: #000; 
            border: 1px solid #333; 
            color: white; 
            border-radius: 6px; 
            box-sizing: border-box; 
            outline: none;
            font-size: 16px; /* Prevents auto-zoom on iOS */
            transition: 0.3s;
        }
        input:focus {
            border-color: #D4AF37;
        }
        .btn { 
            background: #D4AF37; 
            color: #000; 
            border: none; 
            padding: 15px; 
            width: 100%; 
            font-weight: bold; 
            cursor: pointer; 
            border-radius: 6px; 
            margin-top: 15px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn:hover {
            background: #fff;
            transform: translateY(-2px);
        }
        .footer-links {
            margin-top: 25px;
            border-top: 1px solid #1a1a1a;
            padding-top: 20px;
        }
        .footer-links a {
            color: #666;
            font-size: 12px;
            text-decoration: none;
            transition: 0.3s;
        }
        .footer-links a:hover {
            color: #D4AF37;
        }
        
        @media (max-width: 480px) {
            form {
                padding: 30px 20px;
            }
            .heading {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<form method="POST" action="login.php">
    <div class="heading">AMC <span style="color:#fff;">HOSTING</span></div>
    <span class="sub-heading">Client Portal Login</span>

    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>
    
    <button type="submit" class="btn">LOGIN TO DASHBOARD</button>
    
    <div class="footer-links">
        <a href="forgot.php">Forgot Password?</a>
        <span style="color:#222; margin: 0 8px;">|</span>
        <a href="index.php">Back to Home</a>
    </div>
</form>

</body>
</html>