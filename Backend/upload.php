I can certainly add the necessary CORS headers to your upload.php file. The headers need to be at the very top of the script to ensure they are sent before any other output.

Here is your updated upload.php file with the CORS headers added.

PHP

<?php
// Add CORS headers to allow requests from your Netlify frontend
header("Access-Control-Allow-Origin: https://klimbnowdocumentsupload.netlify.app");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Always send JSON response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    // Create uploads folder if not exists
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFiles = [];

    // Handle multiple file uploads
    if (!empty($_FILES['documents']['name'][0])) {
        foreach ($_FILES['documents']['name'] as $key => $filename) {
            $tmpName    = $_FILES['documents']['tmp_name'][$key];
            $targetFile = $uploadDir . basename($filename);

            if (move_uploaded_file($tmpName, $targetFile)) {
                $uploadedFiles[] = $targetFile;
            }
        }
    }

    // Send email with attachments
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host         = 'smtp.gmail.com';
        $mail->SMTPAuth     = true;
        $mail->Username     = 'ajaym4654@gmail.com';     // Your Gmail
        $mail->Password     = 'yzgmxqxtesujfoel';       // Gmail App Password (no spaces)
        $mail->SMTPSecure   = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port         = 587;

        $mail->setFrom('ajaym4654@gmail.com', 'Candidate Document Upload');
        $mail->addAddress('ajaym4654@gmail.com');        // Where you receive docs

        // Attach uploaded files
        foreach ($uploadedFiles as $filePath) {
            $mail->addAttachment($filePath);
        }

        // Email body
        $mail->isHTML(true);
        $mail->Subject = 'New Candidate Documents Uploaded';
        $mail->Body    = "Name: {$name}<br>Email: {$email}";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Documents sent successfully']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
