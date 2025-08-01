<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require 'vendor/autoload.php';

$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

// --- STEP 1: reCAPTCHA ---
if (!$isLocal) {
    $recaptchaSecret = '6Lf0jZYrAAAAAOM-zxATvQWRW-UH2n7xSQIzwiID';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($recaptchaResponse)) {
        $response['message'] = 'reCAPTCHA token was missing.';
        echo json_encode($response);
        exit;
    }

    $recaptchaResult = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify',
        false,
        stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret' => $recaptchaSecret,
                    'response' => $recaptchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ])
            ]
        ])
    );

    $recaptchaJson = json_decode($recaptchaResult);
    if (!$recaptchaJson || !$recaptchaJson->success || $recaptchaJson->score < 0.5) {
        $response['message'] = 'reCAPTCHA verification failed. Please try again.';
        echo json_encode($response);
        exit;
    }
}

// --- STEP 2: Validate and Sanitize ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$formData = [
    'userType' => filter_input(INPUT_POST, 'userType', FILTER_SANITIZE_STRING),
    'purpose' => filter_input(INPUT_POST, 'purpose', FILTER_SANITIZE_STRING),
    'propertyType' => filter_input(INPUT_POST, 'propertyType', FILTER_SANITIZE_STRING),
    'bedrooms' => filter_input(INPUT_POST, 'bedrooms', FILTER_SANITIZE_STRING),
    'location' => filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING),
    'budgetFrom' => filter_input(INPUT_POST, 'budgetFrom', FILTER_SANITIZE_NUMBER_INT),
    'budgetTo' => filter_input(INPUT_POST, 'budgetTo', FILTER_SANITIZE_NUMBER_INT),
    'fullName' => filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING),
    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
    'phone' => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING),
    'contactMethod' => filter_input(INPUT_POST, 'contactMethod', FILTER_SANITIZE_STRING),
    'message' => filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)
];

if (empty($formData['fullName']) || empty($formData['phone']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please fill all required fields correctly.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['agreement'])) {
    $response['message'] = 'You must agree to be contacted.';
    echo json_encode($response);
    exit;
}

// --- STEP 3: Send Email ---
$emailBody = "
    <h2>New Property Inquiry</h2>
    <hr>
    <h3>Inquiry Details</h3>
    <p><strong>I am a:</strong> " . htmlspecialchars($formData['userType']) . "</p>
    <p><strong>Purpose:</strong> " . htmlspecialchars($formData['purpose']) . "</p>
    <p><strong>Property Type:</strong> " . htmlspecialchars($formData['propertyType']) . "</p>
    <p><strong>Bedrooms:</strong> " . htmlspecialchars($formData['bedrooms'] ?? 'Not specified') . "</p>
    <p><strong>Preferred Location:</strong> " . htmlspecialchars($formData['location'] ?? 'Not specified') . "</p>
    <p><strong>Budget Range (AED):</strong> " . htmlspecialchars($formData['budgetFrom']) . " - " . htmlspecialchars($formData['budgetTo']) . "</p>
    <h3>Contact Information</h3>
    <p><strong>Full Name:</strong> " . htmlspecialchars($formData['fullName']) . "</p>
    <p><strong>Email:</strong> " . htmlspecialchars($formData['email']) . "</p>
    <p><strong>Phone:</strong> " . htmlspecialchars($formData['phone']) . "</p>
    <p><strong>Preferred Contact Method:</strong> " . htmlspecialchars($formData['contactMethod']) . "</p>
    <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($formData['message'])) . "</p>
";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'support@tntra.io';
    $mail->Password = 'klat sowk jzqz rvui'; // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('support@tntra.io', 'Property Inquiry');
    $mail->addAddress('amin.tai@tntra.io', 'Recipient Name');
    $mail->addReplyTo($formData['email'], $formData['fullName']);
    $mail->isHTML(true);
    $mail->Subject = 'New Property Inquiry from ' . $formData['fullName'];
    $mail->Body = $emailBody;
    $mail->AltBody = strip_tags($emailBody);

    $mail->send();

    // --- STEP 4: Supabase Insert via cURL ---
    $budgetRange = $formData['budgetFrom'] . ' - ' . $formData['budgetTo'];

    $ch = curl_init('https://zmriofxctebhbprcmdsl.supabase.co/rest/v1/inquiry');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InptcmlvZnhjdGViaGJwcmNtZHNsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTQwNDcwMDAsImV4cCI6MjA2OTYyMzAwMH0.AmvRdbVnTShRA4-PfiGmwo_YmNInL0GcQsQ_oZVDHoA',
        'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InptcmlvZnhjdGViaGJwcmNtZHNsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTQwNDcwMDAsImV4cCI6MjA2OTYyMzAwMH0.AmvRdbVnTShRA4-PfiGmwo_YmNInL0GcQsQ_oZVDHoA',
        'Prefer: return=representation'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'full_name' => $formData['fullName'],
        'email' => $formData['email'],
        'phone_no' => $formData['phone'],
        'contact_method' => $formData['contactMethod'],
        'user_type' => $formData['userType'],
        'purpose' => $formData['purpose'],
        'property_type' => $formData['propertyType'],
        'bedrooms' => $formData['bedrooms'],
        'preferred_location' => $formData['location'], // Hardcoded as per context
        'budget_range' => $budgetRange,
        'message' => $formData['message']
    ]));

    $responseCurl = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo "cURL error: $error_msg";
    } else {
        echo "HTTP Status: $httpStatus\n";
        echo "Response: $responseCurl\n";
    }

    if ($httpStatus !== 200 && $httpStatus !== 201) {
        error_log('Supabase insert failed: ' . $responseCurl);
        $response['message'] = 'Inquiry sent, but failed to save to database.';
        echo json_encode($response, $httpStatus);
        exit;
    }

    curl_close($ch);

    $response['success'] = true;
    $response['message'] = 'Thank you! Your inquiry has been submitted successfully.';

} catch (Exception $e) {
    error_log("Email or Supabase Error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Something went wrong. Please try again later.';
}

echo json_encode($response);
exit;
