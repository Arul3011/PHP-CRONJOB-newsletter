<?php
require_once 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

// Handle form 1: send OTP for unsubscription
if (isset($_POST["form1"])) {
    $emailId = trim($_POST["unsubscribe_email"]);
    if ($emailId === "") {
        $message = "Please enter a valid email.";
    } else {
        $code = generateVerificationCode();

        if (!isset($_SESSION['unsubscribe_codes']) || !is_array($_SESSION['unsubscribe_codes'])) {
            $_SESSION['unsubscribe_codes'] = [];
        }

        if (sendVerificationEmail($emailId, $code)) {
            $_SESSION['unsubscribe_codes'][$emailId] = $code;
            $message = "OTP sent to <strong>$emailId</strong> at " . date("Y-m-d H:i:s");
        } else {
            $message = "Failed to send OTP to <strong>$emailId</strong>";
        }
    }
}

// Handle form 2: verify OTP and unsubscribe
if (isset($_POST["form2"])) {
    $emailId = trim($_POST["unsubscribe_email"]);
    $userOtp = trim($_POST["verification_code"]);

    if ($userOtp === "") {
        $message = "OTP is required.";
    } else {
        if (isset($_SESSION['unsubscribe_codes'][$emailId]) && $_SESSION['unsubscribe_codes'][$emailId] === $userOtp) {
            if (unsubscribeEmail($emailId)) {
                $message = "<strong>$emailId</strong> has been unsubscribed successfully.";
            } else {
                $message = "<strong>$emailId</strong> was not found or already unsubscribed.";
            }
            unset($_SESSION['unsubscribe_codes'][$emailId]);
        } else {
            $message = "Invalid OTP for <strong>$emailId</strong>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe from XKCD Emails</title>
    <link rel="stylesheet" href="style.css">
    <style>

        .fixed-top-left-btn {
            position: fixed;
            top: 40px;
            left: 40px;
            z-index: 1000;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="container">
            <div class="header">
                                <a href="/" class="btn btn-primary fixed-top-left-btn">HOME</a>

                <h1 class="title">Unsubscribe from comicser</h1>
                <p class="subtitle">
                    We're sorry to see you go. Please enter your email to confirm your unsubscription.
                </p>
            </div>

            <?php if (!empty($message)): ?>
            <div id="statusMessage" class="alert">
                <div class="alert-content"><?php echo $message; ?></div>
            </div>
            <?php endif; ?>

            <div class="main-grid">
                
                <div class="card main-card">
                    <div class="card-header">
                        <div class="card-title">Unsubscribe</div>
                        <p class="card-description" id="formDescription">
                            Enter your email to receive a verification code to complete the unsubscription process.
                        </p>
                    </div>
                    <div class="card-content">
                        
                        <form method="post" class="form-step">
                            <div class="form-group">
                                <label for="unsubscribe-email-step1" class="label">Email Address</label>
                                <input 
                                    type="email" 
                                    id="unsubscribe-email-step1" 
                                    name="unsubscribe_email" 
                                    placeholder="Enter your email to unsubscribe" 
                                    class="input" 
                                    required
                                >
                            </div>
                            <button type="submit" name="form1" class="btn btn-primary">
                                Send Verification Code
                            </button>

                        </form>

                        <div class="form-step">
                            <form method="post">
                                <div class="form-group">
                                    <label for="unsubscribe-email-step2" class="label">Email Address</label>
                                    <input 
                                        type="email" 
                                        id="unsubscribe-email-step2" 
                                        name="unsubscribe_email" 
                                        placeholder="Re-enter your email" 
                                        class="input" 
                                        required
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="otp" class="label">6-Digit Verification Code</label>
                                    <input 
                                        type="text" 
                                        id="otp" 
                                        name="verification_code" 
                                        placeholder="Enter 6-digit code" 
                                        class="input otp-input" 
                                        maxlength="6"
                                        required
                                    >
                                </div>
                                <button type="submit" name="form2" class="btn btn-primary">
                                    Verify & Unsubscribe
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
<!-- 
                <div class="sidebar">
                    <div class="card stats-card">
                        <div class="card-header-small">
                            <div class="card-title-small">Total Subscribers</div>
                        </div>
                        <div class="card-content">
                            <div class="stats-number" id="subscriberCount">
                                <?php  // echo count(file('registered_emails.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)); ?>
                            </div>
                            <p class="stats-description">Current active subscriptions</p>
                        </div>
                    </div> -->

                    <div class="card security-card">
                        <div class="card-header-small">
                            <div class="card-title-small">Secure Process</div>
                        </div>
                        <div class="card-content">
                            <div class="security-badges">
                                <div class="badge success">✓ OTP Verification</div>
                            </div>
                            <p class="security-description">
                                Your unsubscription is verified to prevent unauthorized changes.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>