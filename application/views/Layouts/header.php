<?php
header("script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
$where = ['company_id' => 1, 'product_id' => 1];
$user_roles = $this->db->select('UR.user_role_user_id, UR.user_role_type_id, MRT.role_type_labels, MRT.role_type_name, MRT.role_type_heading')
    ->where('UR.user_role_user_id', $_SESSION['isUserSession']['user_id'])
    ->where(['UR.user_role_active' => 1, 'UR.user_role_deleted' => 0])
    ->from('user_roles UR')
    ->join('master_role_type MRT', 'MRT.role_type_id = UR.user_role_type_id')
    ->get()
    ->result();

$userDetails = $this->db->select('users.*')
    ->where('users.user_id', $_SESSION['isUserSession']['user_id'])
    ->from('users')
    ->join('user_roles UR', 'UR.user_role_user_id = users.user_id')
    ->where(['UR.user_role_active' => 1, 'UR.user_role_deleted' => 0])
    ->get()
    ->row();

if (!empty($userDetails->user_token) && session_id() != $userDetails->user_token) {
    $this->session->set_flashdata('login_err', "Multiple Login Detected, Please login again.");
    return redirect(base_url("logout"));
}
?>

<!DOCTYPE html>
<html>

<head>
<title><?= TITLE;?> LMS</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="<?= FAVICON_16; ?>" type="image/*" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" />
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/font-awesome.css" type="text/css">
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/bootstrap.css?v=1.2" type="text/css">
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/layout.css?v=1.2" type="text/css">
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/components.css?v=1.2" type="text/css">
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/plugins.css?v=1.2" type="text/css">
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/datepicker.min.css?v=1.2" rel="stylesheet">
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/style.css?v=1.4" />
<link rel="stylesheet" href="<?= PUBLIC_URL; ?>css/accordion.css">
</head>
<body id="parent_wrapper">
<div class="toggle-icon" id="menuToggle">☰</div>
<section class="left-side" id="leftSideMenu">
    <div class="nav-row">
        <div class="logo">
            <div class="logo-box">
                <a href="<?= LMS_URL; ?>"><img src="<?= LOGO; ?>" class="logo-img"></a>
            </div>
        </div>
        <ul>
            <li>
                <select id="role_permission" onchange="defaultLoginRole(<?= $_SESSION['isUserSession']['user_id'] ?>, this);checkRoleChange(this.value);">
                    <?php 
                    foreach ($user_roles as $role) 
                    { 
                        $role = (array)$role;
                        extract($role);
                        $selected = $user_role_type_id == $_SESSION['isUserSession']['role_id']?"selected":"";
                        ?>
                        <option value="<?= $user_role_type_id ?>" <?= $selected;?>><?= $role_type_name ?></option>
                    <?php 
                    } ?>
                </select>
            </li>
            <li><a href="<?= base_url('dashboard') ?>" title="Dashboard"><i class="fa-solid fa-house"></i>Home </a></li>
            <?php if (agent != "OL" && $_SESSION['isUserSession']['user_id'] != 265) { ?>
                <li> <a href="<?= base_url('search') ?>" title="Search"><i class="fa-solid fa-magnifying-glass"></i>Search </a></li>
            <?php } ?>
            <?php if (agent == 'CA') { ?>
                <!--<li><a href="<?= base_url('adminViewUser'); ?>" title="Setting"><i class="fa fa-gear"></i></a></li>-->
                <li><a href="<?= base_url('ums'); ?>" title="Setting"><i class="fa-solid fa-gear"></i>Settings </a></li>
            <?php } ?>
            <li><a href="<?= base_url('myProfile') ?>" title="<?= $userDetails->user_id ?>"><i class="fa-solid fa-user"></i>Profile</a></li>
            <li><a href="<?= base_url('logout'); ?>" title="Logout"><i class="fa-solid fa-power-off"></i>Logout </a></li>
            <?php //}
            ?>
        </ul>
    </div>
</section>