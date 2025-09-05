<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Reviewer | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="assets/css/classes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/material-symbols/css/material-symbols.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="content-wrapper">
        <!-- Header Container -->
        <div class="add-course-container">
            <button class="secondary-button" id="add_reviewer_button">Add Reviewer</button>
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="courses-tab">Reviewer</li>
                <li class="tab-link" id="classes-tab-link" style="display: none;" data-tab="classes-tab">Reviewer Details</li>
            </ul>
        </div>

        <div id="courses-tab" class="tab-content active">
            <div class="course-container">
                <?php
                $qry = $conn->query("SELECT * FROM rw_reviewer WHERE student_id = '".$_SESSION['login_id']."' ORDER BY reviewer_name ASC");
                if ($qry->num_rows > 0) {
                    while ($row = $qry->fetch_assoc()) {
                        $reviewer_id =  $row['reviewer_id'];
                    ?>
                        <div class="course-card">
                            <div class="course-card-body">
                                <div class="meatball-menu-container">
                                    <button class="meatball-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="meatball-menu">
                                        <div class="arrow-up"></div>
                                        <a href="#" class="edit_reviewer" data-id="<?php echo $reviewer_id ?>"><span class="material-symbols-outlined">Edit</span>Edit</a>
                                        <a href="#" class="remove_reviewer" data-id="<?php echo $reviewer_id ?>"><span class="material-symbols-outlined">delete</span>Delete</a>
                                        <a href="#" class="share_reviewer" data-id="<?php echo $reviewer_id ?>" data-type="<?php echo $row['reviewer_type']; ?>" ><span class="material-symbols-outlined">key</span>Get Code</a>
                                    </div>
                                </div>
                                <div class="course-card-title"><?php echo $row['reviewer_name'] ?></div>
                                <div class="course-card-text">
                                    Topic: <?php echo $row['topic'] ?><br>
                                    Type: <?php echo $row['reviewer_type'] == 1 ? 'Test Reviewer' : 'Flashcard Reviewer'  ?>
                                </div>
                                <div class="course-actions">
                                    <a class="tertiary-button" id="view_reviewer_details" 
                                    href="manage_reviewer.php?reviewer_id=<?php echo $reviewer_id ?>" type="button"> Manage</a>                                
                                    <button class="main-button take-reviewer" 
                                        id="take_reviewer" 
                                        data-id="<?php echo $row['reviewer_id']; ?>" 
                                        data-type="<?php echo $row['reviewer_type']; ?>" 
                                        type="button">
                                        Take Reviewer
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php }
                } else {
                    echo "<p class='no-assessments'>No reviewers created yet</p>";
                } ?>
                </div>
            </div>

            <div class="popup-overlay" id="add-edit-reviewer-popup">
                <div class="popup-content" id="add-edit-reviewer-content" role="document">
                    <button class="popup-close">&times;</button>
                    <h2 id="add-edit-reviewer-title" class="popup-title">Add New Reviewer</h2>

                    <form id="add-edit-reviewer-form">
                        <div class="modal-body">
                            <div id="msg"></div>
                            <input type="hidden" id="reviewer_id" name="reviewer_id">
                            
                            <div class="form-group">
                                <label for="reviewer_type">Select Reviewer Type</label>
                                <select id="reviewer_type" name="reviewer_type" class="popup-input" required>
                                    <option value="1">Test</option>
                                    <option value="2">Flashcard</option>
                                </select>
                            </div>
                               
                            <div class="form-group">
                                <label for="reviewer_name">Reviewer Name</label>
                                <input type="text" id="reviewer_name" name="reviewer_name" class="popup-input" placeholder="Enter Reviewer Name" required>
                            </div>
                                
                            <div class="form-group">
                                <label for="topic">Topic</label>
                                <input type="text" id="topic" name="topic" class="popup-input" placeholder="Enter Topic" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="secondary-button">Save Reviewer</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Reviewer Modal -->
            <div id="delete-reviewer-popup" class="popup-overlay"> 
                <div id="delete-reviewer-modal-content" class="popup-content" role="document">
                    <button id="modal-close" class="popup-close">&times;</button>
                    <h2 id="delete-reviewer-title" class="popup-title">Delete Reviewer</h2>

                    <!-- Form to delete the program-->
                    <form id='delete-reviewer-form'>
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <p id="delete-message" class="popup-message"> Are you sure you want to delete this reviewer?</p>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="tertiary-button close-popup" type="button">Cancel</button>
                            <button class="secondary-button" id="confirm_delete_btn" type="submit">Delete</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Get Code Modal -->
            <div id="reviewer-code-popup" class="popup-overlay"> 
                <div id="reviewer-code-modal-content" class="popup-content" role="document">
                    <button id="modal-close" class="popup-close">&times;</button>
                    <h2 id="reviewer-code-title" class="popup-title">Reviewer Code</h2>

                    <!-- Get Code -->
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <h3><a id="modal_code"></a></h3>
                            <h1 id="modal_code"></h1>
                        </div>
                    </div>
                </div>
            </div>
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

            // Function to open modal for adding or editing
            function openReviewerModal(mode, reviewerId = null) {
                if (mode === 'add') {
                    $('#add-edit-reviewer-title').text('Add New Reviewer');
                    $('#add-edit-reviewer-form').attr('action', 'save_reviewer.php');
                    $('#add-edit-reviewer-form')[0].reset(); // Clear form
                    $('#reviewer_id').val(''); // Clear the reviewer_id
                } else if (mode === 'edit') {
                    $('#add-edit-reviewer-title').text('Edit Reviewer');
                    $('#add-edit-reviewer-form').attr('action', 'update_reviewer.php');
                        
                    // Fetch reviewer details
                    $.ajax({
                        url: 'get_reviewer.php',
                        type: 'POST',
                        data: { reviewer_id: reviewerId },
                        dataType: 'json',
                        success: function(result) {
                            if (result.success) {
                                $('#reviewer_type').val(result.reviewer.reviewer_type);
                                $('#reviewer_name').val(result.reviewer.reviewer_name);
                                $('#topic').val(result.reviewer.topic);
                                $('#reviewer_id').val(reviewerId);
                            } else {
                                alert('Error fetching reviewer details.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('Error fetching reviewer details.');
                        }
                    });
                }
                showPopup('add-edit-reviewer-popup');
            }

            // Event listener for "Add Reviewer" button
            $('#add_reviewer_button').click(function() {
                openReviewerModal('add');
            });

            // Event listener for "Edit" button in meatball menu
            $(document).on('click', '.edit_reviewer', function() {
                var reviewerId = $(this).data('id');
                openReviewerModal('edit', reviewerId);
            });

            $('.take-reviewer').click(function() {
                var reviewerId = $(this).data('id');
                var reviewerType = $(this).data('type');

                $.ajax({
                    url: 'check_reviewer.php',
                    type: 'POST',
                    data: { 
                        reviewerId: reviewerId,
                        reviewerType: reviewerType
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'take_reviewer.php?reviewer_id=' + reviewerId;
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
                    }, error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error fetching reviewer details.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                                confirmButton: 'secondary-button'
                            }
                        });
                    }
                })
            });

            // Form submission handler
            $('#add-edit-reviewer-form').submit(function(event) {
                event.preventDefault();
                closePopup('add-edit-reviewer-popup');
                var formData = new FormData(this);
                var url = $(this).attr('action');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Reviewer saved successfully!',
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
                                text: 'Unable to save reviewer: ' + response.message,
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
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while saving the reviewer. Please try again.',
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

            $(document).ready(function () {
                // Toggle the meatball menu visibility when the button is clicked
                $(document).on('click', '.meatball-menu-btn', function (e) {
                    e.stopPropagation(); // Prevent click from bubbling up
                    var $menu = $(this).siblings('.meatball-menu');
                    $('.meatball-menu').not($menu).hide(); // Hide other open meatball menus
                    $menu.toggle(); // Toggle the current menu visibility
                });

                // Close the meatball menu if clicking outside of it
                $(document).click(function () {
                    $('.meatball-menu').hide(); // Hide all open menus when clicking outside
                });

                // Prevent the menu from closing when clicking inside the menu
                $(document).on('click', '.meatball-menu', function (e) {
                    e.stopPropagation();
                });
            });

            // Delete button functionality for reviewers
            $(document).on('click', '.remove_reviewer', function() {
            var reviewerId = $(this).data('id');
            $('#confirm_delete_btn').data('id', reviewerId);
            showPopup('delete-reviewer-popup');
            });

            // Confirm delete button click handler
            $('#confirm_delete_btn').click(function() {
                var reviewerId = $(this).data('id');
                closePopup('delete-reviewer-popup');
                
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the reviewer',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'delete_reviewer.php',
                    method: 'POST',
                    data: { reviewer_id: reviewerId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message || 'Reviewer deleted successfully!',
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
                                text: response.message || 'Unable to delete reviewer',
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
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the reviewer: ' + error,
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

            $(document).on('click', '.share_reviewer', function() { 
                var reviewerId = $(this).data('id'); 
                var reviewerType = $(this).data('type');

                // First, check if there are questions for the reviewer
                $.ajax({
                    url: 'check_reviewer.php',
                    type: 'POST',
                    data: { 
                        reviewerId: reviewerId,
                        reviewerType: reviewerType
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // If successful, fetch the reviewer code
                            $('#msg').html(''); // Clear any previous messages
                            $('#reviewer-code-popup #reviewer-code-title').html('Reviewer Code'); // Set modal title to 'Reviewer Code'

                            $.ajax({
                                url: 'reviewer_code.php', 
                                type: 'POST', 
                                data: { reviewer_id: reviewerId }, 
                                success: function(response) {
                                    var result = JSON.parse(response); 
                                    if (result.success) {
                                        $('#modal_code').text(result.code); // Display the generated code
                                    } else {
                                        $('#modal_code').text('Error: ' + result.error); 
                                    }
                                    showPopup('reviewer-code-popup');
                                },
                                error: function(xhr, status, error) {
                                    $('#modal_code').text('Error fetching code. Please try again.'); 
                                    console.error('Error:', error); 
                                }
                            });

                        } else {
                            // If there are no questions, display a message
                            Swal.fire({
                                title: 'No Questions!',
                                text: 'Please add some questions first before sharing the reviewer',
                                icon: 'info',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#modal_code').text('Error checking reviewer. Please try again.'); 
                        console.error('Error:', error); 
                    }
                });
            });


            // Close the popup when close button is clicked
            $('.popup-close').on('click', function() {
                var activePopup = this.parentElement.parentElement.id;
                closePopup(activePopup);
            });

            // For other close button
            $('.close-popup').on('click', function() {
                var activePopup = this.parentElement.parentElement.parentElement.parentElement.id;
                closePopup(activePopup);
            });
        });
    </script>
</body>
</html>