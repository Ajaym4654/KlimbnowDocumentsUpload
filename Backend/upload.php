<?php
header("Access-Control-Allow-Origin: https://klimbnowdocumentsupload.netlify.app");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$name  = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

if (!$name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Name and Email required']);
    exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadedFiles = [];

if (!empty($_FILES['documents']['name'][0])) {
    foreach ($_FILES['documents']['name'] as $key => $filename) {
        $tmp = $_FILES['documents']['tmp_name'][$key];
        $new = time() . "_" . basename($filename);
        if (move_uploaded_file($tmp, $uploadDir . $new)) {
            $uploadedFiles[] = $uploadDir . $new;
        }
    }
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'a10966001@smtp-brevo.com';
    $mail->Password = getenv('SMTP_PASS');
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
        <p><b>Name:</b> $name</p>
        <p><b>Email:</b> $email</p>
    ";

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Documents sent successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $mail->ErrorInfo
    ]);
}
