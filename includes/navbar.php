<style> body { margin: 0 !important; padding-top: 0 !important; } .navbar { margin-top: 0 !important; } </style>
<nav class="navbar navbar-expand-lg sticky-top px-3 px-md-5 py-3 shadow-sm">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            
            <button class="btn btn-light d-md-none me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" style="border-radius: 12px;">
                <i class="bi bi-list" style="font-size: 24px;"></i>
            </button>

            <img src="../assets/images/maracoporation.png" height="42" class="me-4 d-none d-md-block">
            <img src="../assets/images/kemajuan.png" height="42" class="me-4 d-none d-md-block">
            <img src="../assets/images/maralogo.png" height="42" class="me-4 d-none d-md-block">
            
            <img src="../assets/images/logo2.png" height="42">
        </div>

        <div class="d-flex align-items-center">

            <?php
            // NOTIFICATION LOGIC (UNTOUCHED)
            $count_sql = "SELECT COUNT(*) AS total FROM notifications WHERE user_id=? AND is_read=0";
            $count_stmt = mysqli_prepare($conn, $count_sql);
            mysqli_stmt_bind_param($count_stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            $notification_count = mysqli_fetch_assoc($count_result)['total'];
            ?>

            <div class="dropdown me-3 me-md-4">
                <a href="#" class="position-relative text-dark" data-bs-toggle="dropdown">
                    <i class="bi bi-bell" style="font-size:24px;"></i>
                    <?php if($notification_count > 0) { ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $notification_count ?>
                        </span>
                    <?php } ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow" style="width:340px;">
                    <li class="dropdown-header fw-bold">Notifications</li>
                    <li><hr class="dropdown-divider"></li>
                    <?php
                    $sql = "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    while($notification = mysqli_fetch_assoc($result)) {
                    ?>
                        <li>
                            <a href="../<?= $_SESSION['role'] ?>/read_notification.php?id=<?= $notification['id'] ?>" class="dropdown-item">
                                <div class="fw-semibold"><?= $notification['title'] ?></div>
                                <small class="text-muted"><?= $notification['message'] ?></small>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="me-3 text-end d-none d-sm-block">
                <div class="fw-semibold navbar-user-name" style="font-size:18px;">
                    <?php echo $_SESSION['fullname']; ?>
                </div>
                <small class="navbar-user-role">
                    <?php echo ucfirst($_SESSION['role']); ?>
                </small>
            </div>

            <?php
            if(empty($_SESSION['profile_picture'])) {
                $picture = "../uploads/default.png";
            } else {
                $picture = "../uploads/" . $_SESSION['profile_picture'];
            }
            ?>

            <div class="dropdown me-3">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="text-decoration:none;">
                    <img src="<?= $picture ?>" class="navbar-avatar" style="width:45px; height:45px; border-radius:50%; object-fit:cover; cursor:pointer;">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item" href="../<?= $_SESSION['role'] ?>/profile.php">
                            <i class="bi bi-person-circle me-2"></i> My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="../<?= $_SESSION['role'] ?>/change_password.php">
                            <i class="bi bi-key me-2"></i> Change Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <button id="theme-toggle" class="btn btn-sm" style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; padding:0;" title="Toggle Dark Mode" onclick="toggleTheme()">
                <i class="bi bi-moon-fill" id="theme-icon" style="font-size:18px;"></i>
            </button>

            <script>
            function toggleTheme(){
                document.body.classList.toggle('dark-mode');
                var isDark = document.body.classList.contains('dark-mode');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateThemeIcon();
            }

            function updateThemeIcon(){
                var icon = document.getElementById('theme-icon');
                if(document.body.classList.contains('dark-mode')){
                    icon.className = 'bi bi-sun-fill';
                } else {
                    icon.className = 'bi bi-moon-fill';
                }
            }
            updateThemeIcon();
            </script>
        </div>
    </div>
</nav>