<?php
// Use statements for PHPMailer at the top
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// It's a good practice to handle errors and not show them directly in the output
// This prevents breaking the JSON response with PHP warnings or notices.
error_reporting(0);
ini_set('display_errors', 0);

// Set the header to indicate the response is JSON
header('Content-Type: application/json');

// --- Main Response Array ---
// We will build our response in this array and encode it once at the end.
$response = [
    'success' => false,
    'message' => 'An unknown error occurred.'
];

// --- Composer's Autoloader for PHPMailer ---
// Ensure the path is correct relative to your submit.php file
require 'vendor/autoload.php';

// --- STEP 1: reCAPTCHA Verification ---
// IMPORTANT: Replace with your ACTUAL SECRET KEY from the Google reCAPTCHA Admin Console.
// A SECRET KEY typically starts with '6L'. The key you had was likely the SITE KEY.
$recaptchaSecret = '6Lf0jZYrAAAAAOM-zxATvQWRW-UH2n7xSQIzwiID'; 
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

if (empty($recaptchaResponse)) {
    $response['message'] = 'reCAPTCHA token was missing.';
    echo json_encode($response);
    exit; // Stop execution
}

$recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
$recaptchaData = [
    'secret'   => $recaptchaSecret,
    'response' => $recaptchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$recaptchaOptions = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($recaptchaData)
    ]
];

$recaptchaContext = stream_context_create($recaptchaOptions);
$recaptchaResult = file_get_contents($recaptchaUrl, false, $recaptchaContext);
$recaptchaJson = json_decode($recaptchaResult);

// Check if reCAPTCHA verification failed
if (!$recaptchaJson || !$recaptchaJson->success || $recaptchaJson->score < 0.5) {
    $response['message'] = 'reCAPTCHA verification failed. Please try again.';
    // For debugging, you can see why it failed. Do not leave this on a live site.
    // $response['debug'] = $recaptchaJson; 
    echo json_encode($response);
    exit;
}

// --- STEP 2: Validate and Sanitize Form Data ---
// Only proceed if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Sanitize all inputs
$formData = [
    'userType'      => filter_input(INPUT_POST, 'userType', FILTER_SANITIZE_STRING),
    'purpose'       => filter_input(INPUT_POST, 'purpose', FILTER_SANITIZE_STRING),
    'propertyType'  => filter_input(INPUT_POST, 'propertyType', FILTER_SANITIZE_STRING),
    'bedrooms'      => filter_input(INPUT_POST, 'bedrooms', FILTER_SANITIZE_STRING),
    'location'      => filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING),
    'budgetFrom'    => filter_input(INPUT_POST, 'budgetFrom', FILTER_SANITIZE_NUMBER_INT),
    'budgetTo'      => filter_input(INPUT_POST, 'budgetTo', FILTER_SANITIZE_NUMBER_INT),
    'fullName'      => filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING),
    'email'         => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
    'phone'         => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING),
    'contactMethod' => filter_input(INPUT_POST, 'contactMethod', FILTER_SANITIZE_STRING),
    'message'       => filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)
];

// Validate required fields
if (empty($formData['fullName']) || empty($formData['phone']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please fill all required fields correctly.';
    echo json_encode($response);
    exit;
}

// Validate agreement checkbox
if (!isset($_POST['agreement'])) {
    $response['message'] = 'You must agree to be contacted.';
    echo json_encode($response);
    exit;
}


// --- STEP 3: Prepare and Send Email using PHPMailer ---
$emailBody = "
    <h2>New Property Inquiry</h2>
    <hr>
    <h3>Inquiry Details</h3>
    <p><strong>I am a:</strong> " . htmlspecialchars($formData['userType']) . "</p>
    <p><strong>Purpose:</strong> " . htmlspecialchars($formData['purpose']) . "</p>
    <p><strong>Property Type:</strong> " . htmlspecialchars($formData['propertyType']) . "</p>
    <p><strong>Bedrooms:</strong> " . (empty($formData['bedrooms']) ? 'Not specified' : htmlspecialchars($formData['bedrooms'])) . "</p>
    <p><strong>Preferred Location:</strong> " . (empty($formData['location']) ? 'Not specified' : htmlspecialchars($formData['location'])) . "</p>
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
    // Server settings for Gmail SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'support@tntra.io'; // Your Gmail address
    $mail->Password   = 'klat sowk jzqz rvui'; // Your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom('support@tntra.io', 'Property Inquiry');
    $mail->addAddress('madhan.kumar@tntra.io', 'Recipient Name'); // Where the email is going
    $mail->addReplyTo($formData['email'], $formData['fullName']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Property Inquiry from ' . $formData['fullName'];
    $mail->Body    = $emailBody;
    $mail->AltBody = strip_tags($emailBody); // Plain text version for non-HTML mail clients

    $mail->send();

    // If email is sent successfully
    $response['success'] = true;
    $response['message'] = 'Thank you! Your inquiry has been submitted successfully.';

} catch (Exception $e) {
    // If PHPMailer throws an exception
    $response['success'] = false;
    // Provide a user-friendly message. Log the detailed error for yourself.
    $response['message'] = 'Message could not be sent. Please try again later.';
    // For debugging: error_log("Mailer Error: " . $mail->ErrorInfo);
}


// --- FINAL STEP: Send the JSON Response ---
// This is the ONLY place the script should output the final response.
echo json_encode($response);
exit;

