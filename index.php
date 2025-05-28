<?php
    session_start();
    require_once 'config.php';

    $error = '';
    $success = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['login'])) {
            $mobile = trim($_POST['mobile']);
            $password = trim($_POST['password']);
            
            if (empty($mobile) || empty($password)) {
                $error = "All fields are required";
            } else {
                $stmt = $conn->prepare("SELECT id, name, password, type FROM users WHERE mobile = ?");
                $stmt->execute([$mobile]);
                
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch();
                    if (password_verify($password, $user['password'])) {
                        // Generate OTP
                        $otp = rand(1000, 9999);
                        $_SESSION['temp_user_id'] = $user['id'];
                        $_SESSION['temp_user_type'] = $user['type'];
                        $_SESSION['otp'] = $otp;
                        $_SESSION['otp_time'] = time();
                        
                        // Send OTP via TextIt
                        $site_textit_username = '9420233035';
                        $site_textit_password = '1725';
                        
                        $text = urlencode("Your login OTP is: $otp. Valid for 5 minutes.");
                        $baseurl = "https://www.textit.biz/sendmsg";
                        $url = "$baseurl/?id=$site_textit_username&pw=$site_textit_password&to=$mobile&text=$text";
                        $ret = file($url);
                        
                        $success = "OTP has been sent to your mobile number";
                        
                        // Show OTP input modal
                        echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
                                otpModal.show();
                            });
                        </script>";
                    } else {
                        $error = "Invalid mobile number or password";
                    }
                } else {
                    $error = "Invalid mobile number or password";
                }
            }
        } elseif (isset($_POST['verify_otp'])) {
            $entered_otp = trim($_POST['otp']);
            
            if ($entered_otp == $_SESSION['otp'] && (time() - $_SESSION['otp_time']) <= 300) { // 300 seconds = 5 minutes
                // OTP is valid
                $_SESSION['user_id'] = $_SESSION['temp_user_id'];
                $_SESSION['user_type'] = $_SESSION['temp_user_type'];
                
                // Clear temporary session variables
                unset($_SESSION['temp_user_id']);
                unset($_SESSION['temp_user_type']);
                unset($_SESSION['otp']);
                unset($_SESSION['otp_time']);
                
                $success = "Login successful! Redirecting...";
                
                // Redirect based on user type with delay
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '" . ($_SESSION['user_type'] == 'Admin' ? 'admin.php' : 'user.php') . "';
                    }, 2000);
                </script>";
            } else {
                $error = "Invalid OTP or OTP expired";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Modern UI</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card shadow-lg border-0 rounded-lg">

                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h1 class="fs-2 fw-bold text-primary">Welcome Back!</h1>
                            <p class="text-muted">Please login to your account</p>
                        </div>
                        
                        <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-floating mb-3">
                                <input type="tel" class="form-control" id="mobile" name="mobile" 
                                    placeholder="10 digit mobile number" 
                                    pattern="[0-9]{10}" 
                                    maxlength="10"
                                    title="Please enter exactly 10 digits">
                                <label for="mobile"><i class="fas fa-phone me-2"></i>Mobile Number</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary text-decoration-none">Register</a></p>
                            </div>
                        </form>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Enter OTP</h5>
                </div>
                <div class="modal-body text-center px-5">
                    <p class="text-muted mb-4">Please enter the 4-digit code sent to your mobile number</p>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="otpForm">
                        <div class="d-flex gap-2 justify-content-center mb-4">
                            <input type="text" class="form-control otp-input text-center fw-bold" 
                                maxlength="1" pattern="[0-9]" inputmode="numeric"
                                style="width: 50px; height: 50px; font-size: 24px;"
                                required>
                            <input type="text" class="form-control otp-input text-center fw-bold" 
                                maxlength="1" pattern="[0-9]" inputmode="numeric"
                                style="width: 50px; height: 50px; font-size: 24px;"
                                required>
                            <input type="text" class="form-control otp-input text-center fw-bold" 
                                maxlength="1" pattern="[0-9]" inputmode="numeric"
                                style="width: 50px; height: 50px; font-size: 24px;"
                                required>
                            <input type="text" class="form-control otp-input text-center fw-bold" 
                                maxlength="1" pattern="[0-9]" inputmode="numeric"
                                style="width: 50px; height: 50px; font-size: 24px;"
                                required>
                        </div>
                        <input type="hidden" name="otp" id="otpValue">
                        <button type="submit" name="verify_otp" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-check-circle me-2"></i>Verify OTP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpForm = document.getElementById('otpForm');
        const otpValue = document.getElementById('otpValue');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value) {
                    if (index > 0) {
                        otpInputs[index - 1].focus();
                    }
                }
            });

            input.addEventListener('keypress', function(e) {
                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                }
            });

            // Prevent paste except numbers
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = e.clipboardData.getData('text');
                if (/^\d+$/.test(pastedText)) {
                    const digits = pastedText.split('');
                    otpInputs.forEach((input, i) => {
                        if (digits[i]) {
                            input.value = digits[i];
                            if (i < otpInputs.length - 1) {
                                otpInputs[i + 1].focus();
                            }
                        }
                    });
                }
            });
        });

        // Combine OTP values before form submission
        otpForm.addEventListener('submit', function(e) {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpValue.value = otp;
        });
    });
    </script>
</body>
</html> 