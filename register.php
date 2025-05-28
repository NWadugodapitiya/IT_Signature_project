<?php
    require_once 'config.php';

    $error = '';
    $success = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username']);
        $mobile = trim($_POST['mobile']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        // Validate input
        if (empty($username) || empty($mobile) || empty($password) || empty($confirm_password)) {
            $error = "All fields are required";
        } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
            $error = "Please enter a valid 10-digit mobile number";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } else {

            $stmt = $conn->prepare("SELECT id FROM users WHERE mobile = ?");
            $stmt->execute([$mobile]);
            
            if ($stmt->rowCount() > 0) {
                $error = "This mobile number is already registered";
            } else {
                
                try {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (name, mobile, password, type) VALUES (?, ?, ?, 'User')");
                    $stmt->execute([$username, $mobile, $hashed_password]);
                    $success = "Registration successful! Please login.";
                    
                    // Redirect to login page after 2 seconds
                    header("refresh:2;url=index.php");
                } catch(PDOException $e) {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Modern UI</title>
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

            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h1 class="fs-2 fw-bold text-primary">Create Account</h1>
                            <p class="text-muted">Fill in your details to get started</p>
                        </div>
                        
                        <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form id="registerForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username">
                                <label for="username"><i class="fas fa-user me-2"></i>Full Name</label>
                            </div>
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
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
                                <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </button>
                            <div class="text-center">
                                <p class="mb-0">Already have an account? <a href="index.php" class="text-primary text-decoration-none">Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html> 