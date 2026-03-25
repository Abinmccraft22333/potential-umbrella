<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Connection Lost</title>
    <style>
        body {
            background-image: url('https://minecraft.net/static/theme/img/background/bg-dirt.png');
            background-repeat: repeat;
            background-size: 64px;
            image-rendering: pixelated;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
            color: white;
            text-align: center;
        }
        .overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.75);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        h1 { font-size: 24px; text-shadow: 2px 2px #000; margin-bottom: 10px; }
        p { font-size: 18px; color: #AAAAAA; text-shadow: 2px 2px #000; margin-bottom: 30px; }
        .btn-mc {
            background: #404040;
            border: 2px solid #000;
            box-shadow: inset -2px -4px #000, inset 2px 2px #808080;
            color: #E0E0E0;
            padding: 10px 40px;
            text-decoration: none;
            font-size: 18px;
            cursor: pointer;
            width: 300px;
            display: inline-block;
        }
        .btn-mc:hover {
            background: #606060;
            color: #FFFF99;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <h1>Connection Lost</h1>
        <p>Internal Exception: io.netty.handler.codec.DecoderException: <br>Page Not Found (404)</p>
        
        <a href="/home" class="btn-mc">Back to Server List</a>
    </div>
</body>
</html>