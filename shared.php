<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <?php include('auth.php'); ?>
    <?php include('db_connect.php'); ?>
    <title>Shared | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="assets/css/classes.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include('nav_bar.php'); ?>
    <div class="content-wrapper">
        <div class="join-class-container">
            <button class="secondary-button" id="enterCode">Enter Code</button>
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="reviewer-tab">Shared Reviewers</li>
            </ul>
        </div>

        <div class="scrollable-content">
            <div id="reviewer-tab" class="tab-content active">
                <div class="course-container" id="reviewersList">
                    <?php
                    $qry = $conn->query("SELECT * FROM user_reviewers WHERE student_id = '".$_SESSION['login_id']."' ORDER BY topic ASC");
                    if ($qry->num_rows > 0) {
                        while ($row = $qry->fetch_assoc()) {
                            $reviewer_id = $row['reviewer_id'];
                    ?>
                        <div class="course-card" data-id="<?php echo $reviewer_id; ?>">
                            <div class="course-card-body">
                                <div class="meatball-menu-container">
                                    <button class="meatball-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="meatball-menu">
                                        <div class="arrow-up"></div>                                
                                        <a href="#" class="remove_reviewer" data-id="<?php echo $reviewer_id ?>"><span class="material-symbols-outlined">delete</span>Delete</a>
                                    </div>
                                </div>
                                <div class="course-card-title"><?php echo $row['reviewer_name'] ?></div>
                                <div class="course-card-text">
                                    Topic: <?php echo $row['topic'] ?><br>
                                    Type: <?php echo $row['reviewer_type'] == 1 ? 'Test Reviewer' : 'Flashcard Reviewer'  ?>
                                </div>
                                <div class="course-actions">                
                                    <button class="main-button" 
                                        id="take_reviewer" 
                                        data-id="<?php echo $row['reviewer_id']; ?>" 
                                        data-type="<?php echo $row['reviewer_type']; ?>" 
                                        type="button" 
                                        onclick="window.location.href='take_shared_reviewer.php?reviewer_id=<?php echo $row['reviewer_id']; ?>&reviewer_type=<?php echo $row['reviewer_type']; ?>'">
                                        Take Reviewer
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php
                        }
                    } else {
                        echo "<p class='no-assessments'>No available shared reviewers</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div id="shared-code-popup" class="popup-overlay">
            <div id="shared-code-content" class="popup-content">
                <button id="shared-code-close" class="popup-close">&times;</button>
                <h2 id="shared-code-title" class="popup-title">Enter Shared Code</h2>

                <form id="code-frm" action="" method="POST">
                    <div class="modal-body">
                        <div class="shared-code">
                            <input type="text" name="get_code" required class="code" placeholder="Reviewer Code" />
                        </div>
                    </div>
                    <div class="join-button">
                        <button id="join" type="submit" class="secondary-button" name="join_by_code">Enter</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="delete-shared-popup" class="popup-overlay"> 
            <div id="delete-shared-modal-content" class="popup-content" role="document">
                <button class="popup-close close-popup">&times;</button>
                <h2 id="delete-shared-title" class="popup-title">Delete Shared Reviewer</h2>

                <!-- Form to delete the program-->
                <form id='delete-shared-form'>
                    <div class="modal-body">
                        <div class="form-group">
                            <p id="delete-message" class="popup-message"> Are you sure you want to delete this reviewer?</p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="tertiary-button close-popup" id="shared-reviewer-close" type="button">Cancel</button>
                        <button class="secondary-button" id="confirm_delete_btn" type="button">Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // Handles Popups
                function showPopup(popupId) {
                    $('#' + popupId).css('display', 'flex');
                }

                function closePopup(popupId) {
                    $('#' + popupId).css('display', 'none');
                }

                $('#enterCode').click(function() {
                    $('#shared-code-popup #code-frm')[0].reset();
                    showPopup('shared-code-popup');
                });

                $('#shared-code-close').click(function() {
                    closePopup('shared-code-popup');
                });

                $('.close-popup').click(function() {
                    closePopup('delete-shared-popup');
                });

                $('#code-frm').submit(function(event) {
                    event.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_reviewer.php',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            $('#shared-code-popup').hide(); 

                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    customClass: {
                                        popup: 'popup-content',
                                        confirmButton: 'secondary-button'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    customClass: {
                                        popup: 'popup-content',
                                        confirmButton: 'secondary-button'
                                    }
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while accessing the shared reviewer. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            });
                        }
                    });
                });

                // Initialize Meatball Menu
                initializeMeatballMenu();

                function initializeMeatballMenu() {
                    $(document).on('click', '.meatball-menu-btn', function(event) {
                        event.stopPropagation();
                        $('.meatball-menu-container').not($(this).parent()).removeClass('show');
                        $(this).parent().toggleClass('show');
                    });

                    $(document).on('click', function(event) {
                        if (!$(event.target).closest('.meatball-menu-container').length) {
                            $('.meatball-menu-container').removeClass('show');
                        }
                    });
                }

                $(document).on('click', '.remove_reviewer', function(event) {
                    event.preventDefault();
                    var sharedId = $(this).data('id');
                    $('#confirm_delete_btn').data('id', sharedId);
                    showPopup('delete-shared-popup');
                });

                $('#confirm_delete_btn').click(function() {
                    var sharedId = $(this).data('id');
                    closePopup('delete-shared-popup');

                    $.ajax({
                        type: 'POST',
                        url: 'remove_shared_reviewer.php',
                        data: { shared_id: sharedId },
                        dataType: 'json',
                        success: function(response) {
                        console.log(response);
                            if (response.status === 'success') {
                                $(`.course-card[data-id="${sharedId}"]`).remove();
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'popup-content',
                                        confirmButton: 'secondary-button',
                                        cancelButton: 'secondary-button'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }     
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'popup-content',
                                        confirmButton: 'secondary-button',
                                        cancelButton: 'secondary-button'
                                    }
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while deleting the shared reviewer. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button',
                                    cancelButton: 'secondary-button'
                                }
                            });
                        }
                    });
                });
            });
        </script>
    </div>
</body>
</html>