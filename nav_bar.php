<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-left">
                <button id="sidebarCollapse">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <div class="navbar-title">
                <h3><strong>QUILANA</strong></h3>
            </div>
            <div class="navbar-right">
                <a href="logout.php" style="color:white">
                    <?php echo $firstname ?> <i class="fa fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <div id="sidebar">
        <?php if($_SESSION['login_user_type'] != 3): ?>
            <a href="faculty_dashboard.php" class="sidebar-item">
                <i class="fa fa-home sidebar-icon"></i> Dashboard
            </a>
            
            <a href="classes.php" class="sidebar-item">
                <i class="fa fa-list-alt sidebar-icon"></i> Classes
            </a>
            
                <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            $is_databank = ($current_page == 'databank.php' || strpos($current_page, 'databank_') === 0);
            ?>

            <a href="databank.php" class="sidebar-item <?php echo $is_databank ? 'active' : ''; ?>">
                <i class="fa fa-database sidebar-icon"></i> Data Bank
            </a>

        <?php else: ?>
            <a href="student_dashboard.php" class="sidebar-item">
                <i class="fa fa-home sidebar-icon"></i> Dashboard
            </a>
            <a href="class_enrolled.php" class="sidebar-item">
                <i class="fa fa-book sidebar-icon"></i> Classes
            </a>
            <a href="reviewer.php" class="sidebar-item">
                <i class="fas fa-folder sidebar-icon"></i> Reviewer
            </a>
			<a href="shared.php" class="sidebar-item">
                <i class="fas fa-share-alt sidebar-icon"></i> Shared
            </a>
        <?php endif; ?>
    </div>

	<script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function(e) {
                e.stopPropagation(); // Prevent event from bubbling up
                $('#sidebar, .content-wrapper').toggleClass('active');
            });

            // Close sidebar when clicking outside of it on mobile
            $(document).on('click', function(event) {
                var windowWidth = $(window).width();
                if (windowWidth <= 768 && !$(event.target).closest('#sidebar, #sidebarCollapse').length) {
                    $('#sidebar, .content-wrapper').removeClass('active');
                }
            });

            // Prevent sidebar from closing when clicking on menu items
            $('#sidebar').on('click', function(e) {
                e.stopPropagation(); // Prevent event from bubbling up
            });

            // Highlight active menu item
            var loc = window.location.href;
            $('#sidebar a').each(function() {
                if ($(this).attr('href') == loc.substr(loc.lastIndexOf("/") + 1)) {
                    $(this).addClass('active');
                }
            });

            // Add delay before redirecting
            $('#sidebar a').on('click', function(e) {
                e.preventDefault(); // Prevent default link behavior

                // Remove 'active' class from all sidebar items and add to the clicked one
                $('#sidebar a').removeClass('active');
                $(this).addClass('active');

                var href = $(this).attr('href');

                setTimeout(function() {
                    $('#sidebar, .content-wrapper').removeClass('active');
                }, 300);

                setTimeout(function() {
                    window.location.href = href; 
                }, 500)   
            });
        });
    </script>
</body>
</html>