<?php
require_once 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

// Handle form 1: send OTP
if (isset($_POST["form1"])) {
    $emailId = trim($_POST["email"]);
    if ($emailId === "") {
        $message = "Please enter a valid email.";
    } else {
        $code = generateVerificationCode();

        if (!isset($_SESSION['codes']) || !is_array($_SESSION['codes'])) {
            $_SESSION['codes'] = [];
        }

        if (sendVerificationEmail($emailId, $code)) {
            $_SESSION['codes'][$emailId] = $code;
            $message = "OTP sent to <strong>$emailId</strong> at " . date("Y-m-d H:i:s");
        } else {
            $message = "Failed to send OTP to <strong>$emailId</strong>";
        }
    }
}

// Handle form 2: verify OTP
if (isset($_POST["form2"])) {
    $emailId = trim($_POST["email"]);
    $userOtp = trim($_POST["verification_code"]);

    if ($userOtp === "") {
        $message = "OTP is required.";
    } else {
        if (isset($_SESSION['codes'][$emailId]) && $_SESSION['codes'][$emailId] === $userOtp) {
            if (registerEmail($emailId)) {
                $message = "OTP verified and <strong>$emailId</strong> subscribed!";
            } else {
                $message = "Email already subscribed or an error occurred.";
            }
            unset($_SESSION['codes'][$emailId]);
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
    <title>XKCD Email Subscription Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1 class="title">comicser Comic Email Subscription</h1>
                <p class="subtitle">
                    Subscribe to receive the latest comicser comics directly in your inbox. 
                    Secure verification ensures your subscription is protected.
                </p>
            </div>

            <!-- Status Message -->
            <?php if (!empty($message)): ?>
            <div id="statusMessage" class="alert">
                <div class="alert-content"><?php echo $message; ?></div>
            </div>
            <?php endif; ?>

            <!-- Main Grid -->
            <div class="main-grid">
                
                <!-- Email Subscription Form -->
                <div class="card main-card">
                    <div class="card-header">
                        <div class="card-title">Email Subscription</div>
                        <p class="card-description" id="formDescription">
                            Enter your email address to receive an OTP verification code
                        </p>
                    </div>
                    <div class="card-content">
                        
                        <!-- Step 1: Email Form -->
                        <form method="post" class="form-step">
                            <div class="form-group">
                                <label for="email" class="label">Email Address</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    placeholder="Enter your email address" 
                                    class="input" 
                                    required
                                >
                            </div>
                            <button type="submit" name="form1" class="btn btn-primary">
                                Send Verification Code
                            </button>
                        </form>

                        <!-- Step 2: OTP Form -->
                        <div class="form-step">
                            <form method="post">
                                <div class="form-group">
                                    <label for="otp-email" class="label">Email Address</label>
                                    <input 
                                        type="email" 
                                        id="otp-email" 
                                        name="email" 
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
                                    Verify & Subscribe
                                </button>
                                <a href="/unsubscribe.php" class="btn btn-primary">unsubscribe</a>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <div class="card stats-card">
                        <div class="card-header-small">
                            <div class="card-title-small">Total Subscribers</div>
                        </div>
                        <div class="card-content">
                            <div class="stats-number" id="subscriberCount">
                                <?php echo count(file('registered_emails.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)); ?>
                            </div>
                            <p class="stats-description">Active email subscriptions</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header-small">
                            <div class="card-title-small">Recent Subscribers</div>
                        </div>
                        <div class="card-content">
                            <div class="subscriber-list">
                                <?php
                                $subscribers = array_reverse(file('registered_emails.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                                if (empty($subscribers)) {
                                    echo "<p class='empty-state'>No subscribers yet</p>";
                                } else {
                                    foreach (array_slice($subscribers, 0, 5) as $sub) {
                                        echo "<p>$sub</p>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="card security-card">
                        <div class="card-header-small">
                            <div class="card-title-small">Security</div>
                        </div>
                        <div class="card-content">
                            <div class="security-badges">
                                <div class="badge success">✓ OTP Verification</div>
                                <div class="badge success">✓ Secure Storage</div>
                            </div>
                            <p class="security-description">
                                Your email is protected with industry-standard security measures.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
