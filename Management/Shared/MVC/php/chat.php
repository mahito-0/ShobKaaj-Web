<?php

set_time_limit(0);

// Disable output buffering for streaming
while (ob_get_level() > 0) {
    ob_end_flush();
}

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

$input = json_decode(file_get_contents("php://input"), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(["error" => "No message provided"]);
    exit;
}

// Ollama Configuration - Optimized for Speed
$data = [
    "model" => "qwen3:8b",
    "keep_alive" => "1h", // Keeps model in memory to prevent slow "cold starts"
    "options" => [
        "num_ctx" => 2048  // Limits memory footprint for faster processing
    ],
    "messages" => [
        [
            "role" => "system",
            "content" => "You are the AI assistant for ShobKaaj, a BD job marketplace. Be extremely polite, concise, and professional. Guide users on hiring (post job -> review -> hire) or working (create profile -> browse -> apply). Payment is via bKash/Nagad/Bank/COD. Registration is free. Email info@shobkaaj.com for support. Keep your answers brief."
        ],
        [
            "role" => "user",
            "content" => $userMessage
        ]
    ],
    "stream" => true
];

// Send to Ollama (Localhost Port 11434)
$ch = curl_init("http://localhost:11434/api/chat");

// Check if initialization failed
if ($ch === false) {
    echo json_encode(["error" => "Failed to initialize cURL"]);
    exit;
}

// We do NOT want return transfer, we want to write it out immediately
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); 
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

// Stream handler: Called every time a chunk of data is received from Ollama
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) {
    $lines = explode("\n", trim($chunk));
    foreach ($lines as $line) {
        if (!empty($line)) {
            $decoded = json_decode($line, true);
            if (isset($decoded['message']['content'])) {
                $content = $decoded['message']['content'];
                // Format for SSE
                echo "data: " . json_encode(["reply" => $content]) . "\n\n";
                flush(); // Force output to client immediately
            }
        }
    }
    return strlen($chunk); // Must return length of processed chunk
});

// Execute the request (this will block until streaming completes)
curl_exec($ch);

// Handle any cURL level connection errors
if (curl_errno($ch)) {
    $error = curl_error($ch);
    echo "data: " . json_encode(["error" => "Connection Error: " . $error]) . "\n\n";
    flush();
}

// Close the handle
curl_close($ch);

