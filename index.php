<?php
// ============================================================
// REDIRECT ALL TRAFFIC TO bot.php
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle POST requests (Telegram webhook)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/bot.php';
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Webhook setup
    if (isset($_GET['set_webhook'])) {
        require_once __DIR__ . '/bot.php';
        exit;
    }
    
    // Webhook info
    if (isset($_GET['webhook_info'])) {
        require_once __DIR__ . '/bot.php';
        exit;
    }
    
    // Health check
    if (isset($_GET['health']) || $_SERVER['REQUEST_URI'] === '/health') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'healthy',
            'bot' => 'Firebase Dumper Bot',
            'time' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/bot.php'
        ]);
        exit;
    }
    
    // Default: Show status page
    $host = $_SERVER['HTTP_HOST'];
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>🔥 Firebase Dumper Bot</title>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family: 'Segoe UI', Arial, sans-serif; background: #0d0d0d; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .container { max-width: 600px; padding: 40px; background: #1a1a1a; border-radius: 16px; border: 1px solid #333; text-align: center; }
            h1 { font-size: 2.5em; color: #ff6b00; margin-bottom: 10px; }
            .sub { color: #888; margin-bottom: 30px; }
            .status-box { background: #0d0d0d; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left; }
            .status-box p { padding: 5px 0; border-bottom: 1px solid #222; }
            .status-box p:last-child { border-bottom: none; }
            .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
            .badge-green { background: #00c853; color: #fff; }
            .badge-orange { background: #ff6b00; color: #fff; }
            .btn { display: inline-block; padding: 12px 28px; margin: 8px; border-radius: 8px; text-decoration: none; color: #fff; font-weight: bold; transition: all 0.3s; cursor: pointer; border: none; }
            .btn-primary { background: #ff6b00; }
            .btn-primary:hover { background: #ff8800; transform: scale(1.02); }
            .btn-telegram { background: #0088cc; }
            .btn-telegram:hover { background: #0099dd; transform: scale(1.02); }
            .btn-secondary { background: #333; }
            .btn-secondary:hover { background: #444; }
            code { background: #2a2a2a; padding: 2px 8px; border-radius: 4px; font-size: 13px; color: #ff6b00; }
            .footer { margin-top: 30px; color: #555; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🔥 Firebase Dumper Bot</h1>
            <p class='sub'>Extract Firebase data even with <code>read:false</code> rules</p>
            
            <div class='status-box'>
                <p>📡 Status: <span class='badge badge-green'>● ONLINE</span></p>
                <p>🤖 Bot: <span class='badge badge-orange'>● ACTIVE</span></p>
                <p>📅 Time: " . date('Y-m-d H:i:s') . "</p>
                <p>🔗 Webhook: <code>https://{$host}/bot.php</code></p>
            </div>
            
            <div>
                <a href='?set_webhook' class='btn btn-primary'>⚡ Set Webhook</a>
                <a href='?webhook_info' class='btn btn-secondary'>📊 Webhook Info</a>
                <a href='https://t.me/" . (getenv('BOT_USERNAME') ?: 'your_bot') . "' class='btn btn-telegram' target='_blank'>📱 Open Bot</a>
            </div>
            
            <div style='margin-top:20px;'>
                <a href='?health' class='btn btn-secondary' style='padding:8px 16px; font-size:12px;'>❤️ Health Check</a>
            </div>
            
            <div class='footer'>
                <p>Send a Firebase config JSON to extract all data</p>
                <p>Commands: /start, /help, /status, /bypass, /test</p>
            </div>
        </div>
    </body>
    </html>";
    exit;
}
?>
