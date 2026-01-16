<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= TITLE; ?> ADMIN LOGIN</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= PUBLIC_URL.'css/style.css'; ?>">
    <meta http-equiv="Permissions-Policy" content="geolocation=(self)">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 50px;
            background: #f7f7f7;
        }
        .login-box {
            max-width: 450px;
            margin: 0 auto;
            background: white;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .login-box img {
            width: 250px;
            margin-bottom: 20px;
        }
        .row-field {
            position: relative;
        }
        .row-field .toggle-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 16px;
        }
        #location-status {
            font-size: 13px;
            margin-top: 15px;
        }
        #map {
            width: 100%;
            height: 250px;
            border-radius: 6px;
            margin-top: 20px;
            display: none;
        }
    </style>
</head>

<body>

<div class="login-box">
    <?= form_open('password-expired', ['id' => 'formData', 'autocomplete' => 'off']); ?>
        <img src="<?= LOGO; ?>" alt="logo">
        <h3>Reset Password</h3>
        <hr>
        <p>Kindly Update Your Password</p>
        <?php 
        if ($this->session->flashdata('err') != '') { ?>
            <p class="alert alert-danger alert-dismissible custom_center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= $this->session->flashdata('err'); ?></strong>
            </p>
            <?php

        }?>
        
        <div class="form-group row-field">
            <input type="password" name="password" class="form-control" placeholder="New Password" title="New Password" required autocomplete="off" id="password">
            <i class="fa-solid fa-eye-slash toggle-eye" id="togglePassword"></i>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" title="Confirm Password" required autocomplete="off" id="password">
        </div>
        <div class="form-group">
            <button class="btn btn-primary btn-block" id="userSignin" type="submit">Change Password</button>
        </div>
    </form>
</div>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    // ✅ Password Toggle
    document.getElementById("togglePassword").addEventListener("click", function() {
        let passwordField = document.getElementById("password");
        if (passwordField.type === "password") {
            passwordField.type = "text";
            this.classList.remove("fa-eye-slash");
            this.classList.add("fa-eye");
        } else {
            passwordField.type = "password";
            this.classList.remove("fa-eye");
            this.classList.add("fa-eye-slash");
        }
    });
</script>
</body>
</html>
