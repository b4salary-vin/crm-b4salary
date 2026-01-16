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
    <?= form_open('dashboard', ['id' => 'formData', 'autocomplete' => 'off']); ?>
        <img src="<?= LOGO; ?>" alt="logo">
        <h3>LMS LOGIN</h3>
        <hr>
        <p>Kindly use your Email and Password</p>
        <?php 
        if ($this->session->flashdata('err') != '') { ?>
            <p class="alert alert-danger alert-dismissible custom_center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= $this->session->flashdata('err'); ?></strong>
            </p>
            <?php

        }?>
        
        <div class="form-group row-field">
            <input type="hidden" name="latitude" id="latitude" value="12345">
            <input type="hidden" name="longitude" id="longitude" value="12345">
            <input type="text" name="email" class="form-control" placeholder="Username" value="" required>
        </div>

        <div class="form-group row-field">
            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            <i class="fa-solid fa-eye-slash toggle-eye" id="togglePassword"></i>
        </div>
        <div class="form-group row-field">
            <a href="<?= LMS_URL.'/forgetPassword'; ?>">← Forgot Password? →</a>
        </div>
        <button class="btn btn-primary btn-block" id="userSignin" type="submit">SIGN IN</button>
        <p id="location-status" class="text-danger"></p>
        <!-- Map preview -->
        <div id="map"></div>
    </form>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Google Maps JS API -->
<!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCX-MAFMmVCSW6KdCNWTXTPR0Y6k6js8rIPG"></script> -->
<script>
    const isLocalhost = window.location.hostname === "lms.suryaloan.co.in";
    let locationAllowed = false;
    let map, marker;

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

    /* ✅ Initialize Google Map */
    // function initMap(lat, lng) {
    //     const position = { lat: lat, lng: lng };
    //     document.getElementById("map").style.display = "block";
    //     map = new google.maps.Map(document.getElementById("map"), {
    //         center: position,
    //         zoom: 15
    //     });
    //     marker = new google.maps.Marker({
    //         position: position,
    //         map: map,
    //         title: "Your Current Location"
    //     });
    // }

    // ✅ Geolocation
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    document.getElementById("latitude").value = lat;
                    document.getElementById("longitude").value = lng;
                    locationAllowed = true;
                    document.getElementById("location-status").innerText = "";
                    initMap(lat, lng);
                },
                function (err) {
                    let txt;
                    switch (err.code) {
                        case err.PERMISSION_DENIED:
                            txt = "❌ Location permission denied.";
                            break;
                        case err.POSITION_UNAVAILABLE:
                            txt = "⚠️ Location unavailable.";
                            break;
                        case err.TIMEOUT:
                            txt = "⏳ Location request timed out.";
                            break;
                        default:
                            txt = "⚠️ Unknown location error.";
                    }
                    document.getElementById("location-status").innerText = txt;
                    locationAllowed = false;
                }
            );
        } else {
            document.getElementById("location-status").innerText = "⚠️ Browser does not support geolocation.";
        }
    }

    // window.onload = getLocation;

    // /* ✅ Prevent submission if on live and no location */
    // document.getElementById("formData").addEventListener("submit", function (e) {
    //     if (!isLocalhost && !locationAllowed) {
    //         e.preventDefault();
    //         document.getElementById("location-status").innerText =
    //             "🚫 Please allow location access to continue.";
    //     }
    // });
</script>

</body>
</html>
