<?php
header('Content-Type: application/json');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Helper function to sanitize input
function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// Helper function for UTF-8 encoding in subject/from
function adopt($text) {
    return '=?UTF-8?B?'.base64_encode($text).'?=';
}

// Your admin email (where messages will be sent)
$admin_email = "support@mixdesign.dev";

// Required hidden fields
$project_name = isset($_POST['project_name']) ? clean($_POST['project_name']) : "Website Contact Form";
$form_subject = isset($_POST['form_subject']) ? clean($_POST['form_subject']) : "New Contact Message";

// Build message table
$message = "<table style='width:100%; border-collapse: collapse;'>";

$alternate = true;
foreach ($_POST as $key => $value) {
    if (in_array($key, ['project_name', 'admin_email', 'form_subject'])) continue;
    if (!empty($value)) {
        $bg = $alternate ? '' : ' style="background-color:#f8f8f8;"';
        $message .= "
            <tr$bg>
                <td style='padding:10px; border: #e9e9e9 1px solid;'><b>".clean($key)."</b></td>
                <td style='padding:10px; border: #e9e9e9 1px solid;'>".clean($value)."</td>
            </tr>
        ";
        $alternate = !$alternate;
    }
}
$message .= "</table>";

// Email headers
$headers = "MIME-Version: 1.0" . PHP_EOL .
    "Content-Type: text/html; charset=utf-8" . PHP_EOL .
    'From: '.adopt($project_name).' <'.$admin_email.'>' . PHP_EOL .
    'Reply-To: '.$admin_email.'' . PHP_EOL;

// Send email
$mail_sent = mail($admin_email, adopt($form_subject), $message, $headers);

// Response
if ($mail_sent) {
    echo json_encode(['status' => 'success', 'message' => 'Your message has been sent successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
}
