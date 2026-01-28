<?php

// =====================
// CORS SETTINGS
// =====================
header("Access-Control-Allow-Origin: https://klimbnowdocumentsupload.netlify.app");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// =====================
// PHPMailer Load
// =====================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// =====================
// Validate Request
// =====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$name  = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

if (!$name || !$email) {
    echo json_encode(['status' => 'error', 'message' => 'Name and Email required']);
    exit;
}

// =====================
// Upload Files
// =====================
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadedFiles = [];

if (!empty($_FILES['documents']['name'][0])) {
    foreach ($_FILES['documents']['name'] as $key => $filename) {

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            continue;
        }

        $tmpName = $_FILES['documents']['tmp_name'][$key];
        $newName = time() . "_" . basename($filename);
        $target  = $uploadDir . $newName;

        if (move_uploaded_file($tmpName, $target)) {
            $uploadedFiles[] = $target;
        }
    }
}

// =====================
// SEND EMAIL (BREVO)
// =====================
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apikey';
    $mail->Password = getenv('SMTP_PASS'); // From Render ENV
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('ajaym4654@gmail.com', 'Klimbnow Documents');
    $mail->addAddress('ajay.m@klimbnow.com');

    foreach ($uploadedFiles as $file) {
        $mail->addAttachment($file);
    }

    $mail->isHTML(true);
    $mail->Subject = "New Candidate Documents";
    $mail->Body = "
        <h3>New Upload Received</h3>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
    ";

    $mail->send();

    echo json_encode([
        'status' => 'success',
        'message' => 'Documents sent successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Mail failed: ' . $mail->ErrorInfo
    ]);
}
