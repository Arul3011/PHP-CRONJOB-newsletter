<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (adjust path if not using Composer)
require_once __DIR__ . '/vendor/autoload.php';


/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yarul8406@gmail.com';            // replace with your Gmail
        $mail->Password   = 'ztiqcokckojydovy';         // use app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('yarul8406@gmail.com', 'XKCD Bot');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = '<p>Your verification code is: <strong>' . htmlspecialchars($code) . '</strong></p>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send verification email to $email: {$mail->ErrorInfo}");
        echo $e->getMessage();
        return false;

    }

    /*
    // Old native mail() version
    $subject = 'Your Verification Code';
    $message = '<p>Your verification code is: <strong>' . htmlspecialchars($code) . '</strong></p>';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";
    return mail($email, $subject, $message, $headers);
    */
}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    if (!in_array($email, $emails)) {
        return file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
    }
    return false;
}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;
    $emails = file($file, FILE_IGNORE_NEW_LINES);
    $updated = array_filter($emails, fn($e) => trim($e) !== trim($email));
    return file_put_contents($file, implode(PHP_EOL, $updated) . PHP_EOL, LOCK_EX) !== false;
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    $random = rand(1, 3105);
    $comic = json_decode(file_get_contents("https://xkcd.com/$random/info.0.json"), true);

    $img = htmlspecialchars($comic['img']);
    $title = htmlspecialchars($comic['title']);
    $alt = htmlspecialchars($comic['alt']);
    $unsubscribeLink = "http://localhost:8000/unsubscribe.php";

    $html = "
        <h2>XKCD Comic: $title</h2>
        <img src='$img' alt='$alt'>
        <p><i>$alt</i></p>
        <p><a href='$unsubscribeLink'>Unsubscribe</a></p>
    ";

    return $html;
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$emails) {
        error_log("No emails found or file could not be read.");
        return;
    }

    $content = fetchAndFormatXKCDData();
    $subject = "Your XKCD Comic";

    foreach ($emails as $email) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'yarul8406@gmail.com';            // replace with your Gmail
        $mail->Password   = 'ztiqcokckojydovy'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('yarul8406@gmail.com', 'XKCD Bot');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $content;

            $mail->send();
        } catch (Exception $e) {
            error_log("Failed to send comic to $email: {$mail->ErrorInfo}");
        }

        /*
        // Old native mail() version
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: no-reply@example.com\r\n";
        $success = mail($email, $subject, $content, $headers);
        if (!$success) {
            error_log("Failed to send comic to $email");
        }
        */
    }
}
