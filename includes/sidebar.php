<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
$link_style = "display:flex; align-items:center; gap:14px; padding:14px 18px; margin-bottom:10px; border-radius:18px; text-decoration:none; font-weight:500; font-size:17px; transition:.2s;"; 

// We capture all the links into a variable so we don't have to type them twice!
ob_start(); 
if($_SESSION['role']=="student") { ?>
    <a href="../student/dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-house"></i><span>Dashboard</span></a>
    <a href="../student/apply_outing.php" class="sidebar-link <?= $current_page == 'apply_outing.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-pencil-square"></i><span>Apply Outing</span></a>
    <a href="../student/my_request.php" class="sidebar-link <?= $current_page == 'my_request.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-card-list"></i><span>My Requests</span></a>
    <a href="../student/qr_pass.php" class="sidebar-link <?= $current_page == 'qr_pass.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-qr-code"></i><span>QR Pass</span></a>
    <a href="../student/compound.php" class="sidebar-link <?= $current_page == 'compound.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-receipt"></i><span>Compound</span></a>
    <a href="../student/sos.php" class="sidebar-link <?= $current_page == 'sos.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-exclamation-triangle"></i><span>SOS Emergency</span></a>
<?php } elseif($_SESSION['role']=="warden") { ?>
    <a href="../warden/dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-house"></i><span>Dashboard</span></a>
    <a href="../warden/pending_request.php" class="sidebar-link <?= $current_page == 'pending_request.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-file-earmark-text"></i><span>Pending Requests</span></a>
    <a href="../warden/active_students.php" class="sidebar-link <?= $current_page == 'active_students.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-people"></i><span>Active Students</span></a>
    <a href="../warden/history.php" class="sidebar-link <?= $current_page == 'history.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-clock-history"></i><span>History</span></a>
    <a href="../warden/sos_alert.php" class="sidebar-link <?= $current_page == 'sos_alert.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-bell"></i><span>SOS Alerts</span></a>
<?php } elseif($_SESSION['role']=="guard") { ?>
    <a href="../guard/dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-house"></i><span>Dashboard</span></a>
    <a href="../guard/scanner.php" class="sidebar-link <?= $current_page == 'scanner.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-upc-scan"></i><span>QR Scanner</span></a>
<?php } elseif($_SESSION['role']=="admin") { ?>
    <a href="../admin/dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-house"></i><span>Dashboard</span></a>
    <a href="../admin/student_monitoring.php" class="sidebar-link <?= $current_page == 'student_monitoring.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-person-lines-fill"></i><span>Student Monitoring</span></a>
    <a href="../admin/user_management.php" class="sidebar-link <?= $current_page == 'user_management.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-people-fill"></i><span>User Management</span></a>
    <a href="../admin/analytics.php" class="sidebar-link <?= $current_page == 'analytics.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-bar-chart"></i><span>Analytics</span></a>
    <a href="../admin/upload_student.php" class="sidebar-link <?= $current_page == 'upload_student.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-upload"></i><span>Upload Student Data</span></a>
    <a href="../admin/compound.php" class="sidebar-link <?= $current_page == 'compound.php' ? 'active' : '' ?>" style="<?= $link_style ?>"><i class="bi bi-file-earmark-text"></i><span>Compound Management</span></a>
<?php } ?>
<hr>
<?php 
// Save the links and clean the output buffer
$menu_links = ob_get_clean(); 
?>

<!-- 1. MOBILE OFFCANVAS (Hidden on Laptops) -->
<div class="offcanvas offcanvas-start p-4 d-md-none" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header border-bottom mb-3 pb-3">
        <h5 class="offcanvas-title fw-bold">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <?= $menu_links ?>
    </div>
</div>

<!-- 2. MAIN LAYOUT CONTAINER -->
<div class="container-fluid">
    <div class="row">
        
        <!-- DESKTOP SIDEBAR (Hidden on Phones) -->
        <div class="col-md-2 p-4 d-none d-md-block" style="min-height:100vh; border-right:1px solid #eee;">
            <h5 class="fw-bold mb-4">Navigation</h5>
            <?= $menu_links ?>
        </div>

        <!-- MAIN CONTENT AREA -->
        <div class="col-md-10 p-3 p-md-4">