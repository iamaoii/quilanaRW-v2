<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Programs | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="assets/css/faculty-dashboard.css">
    <link rel="stylesheet" href="assets/css/classes.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<body>
    <?php include('nav_bar.php') ?>

<div class="content-wrapper">
        <!-- Header Container -->
        <div class="add-course-container">
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="courses-tab">Programs</li>
                <li class="tab-link" id="classes-tab-link" style="display: none;" data-tab="classes-tab">Classes</li>
            </ul>
        </div>

        <div id="courses-tab" class="tab-content scrollable-content active">
            <div class="course-container">
                <?php
                $qry = $conn->query("SELECT * FROM course WHERE faculty_id = '".$_SESSION['login_id']."' ORDER BY course_name ASC");
                if ($qry->num_rows > 0) {
                    while ($row = $qry->fetch_assoc()) {
                        $course_id =  $row['course_id'];
                        $result = $conn->query("SELECT COUNT(*) as classCount FROM class WHERE course_id = '$course_id'");
                        $classCountRow = $result->fetch_assoc();
                        $classCount = $classCountRow['classCount'];
                ?>
                <div class="course-card">
                    <div class="course-card-body">
                        <div class="course-card-title"><?php echo $row['course_name'] ?></div>
                        <div class="course-card-text"><?php echo $classCount ?> Class(es)</div>
                        <div class="course-actions">
                            <button id="viewClasses" class="tertiary-button viewClasses" data-id="<?php echo $row['course_id'] ?>" data-name="<?php echo $row['course_name'] ?>" type="button">Classes</button>
                            <button id="viewCourseDetails" class="main-button" data-id="<?php echo $row['course_id'] ?>" type="button">View Details</button>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo '<div class="no-records" style="grid-column: 1/-1;"> No programs has been added </div>';
                }
                ?>
            </div>
        </div>

        <div id="classes-tab" class="tab-content scrollable-content">
            <div class="course-container" id="class-container">
                <!-- Classes will be dynamically loaded here -->
            </div>
        </div>

        <!-- Course Details Modal -->
        <div id="program-details-popup" class="popup-overlay"> 
            <div id="program-details-modal-content" class="popup-content details-popup" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="program-details-title" class="popup-title">Program Details</h2>

                <div class="modal-body" id="courseDetailsBody">
                    <!-- Course details will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <button class="tertiary-button close-popup">Close</button>
                </div>
            </div>
        </div>

        <!-- Class Details Modal -->
        <div id="class-details-popup" class="popup-overlay"> 
            <div id="program-details-modal-content" class="popup-content details-popup" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="program-details-title" class="popup-title">Class Details</h2>

                <div class="modal-body" id="classDetailsBody">
                    <!-- Class details will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <div id="back-button-container"></div> <!-- If View Class from the View Course Details is clicked -->
                    <button class="tertiary-button close-popup back_vcd_false">Close</button>
                </div>
            </div>
        </div>

        <!-- Get Code Modal -->
        <div id="class-code-popup" class="popup-overlay"> 
            <div id="class-code-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="class-code-title" class="popup-title">Class Code</h2>

                <!-- Get Code -->
                <div class="modal-body">
                    <div id="msg"></div>
                    <div class="form-group">
                        <h3 style="font-weight: bold;"><a id="modal_class_name"></a> (<a id="modal_subject"></a>)</h3>
                        <h1 id="modal_code"></h1>
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

            function getClasses(course_id) {
                $.ajax({
                    url: 'get_classes.php',
                    method: 'POST',
                    data: { course_id: course_id },
                    success: function(response) {
                        $('#class-container').html(response);
                        updateMeatballMenu();
                    }
                });
            } 

            const urlParams = new URLSearchParams(window.location.search);

            // Close the popup when close button is clicked
            $('.popup-close').on('click', function() {
                var activePopup = this.parentElement.parentElement.id;
                closePopup(activePopup);

                if (activePopup == 'class-details-popup') {
                    urlParams.delete('show_modal');
                    urlParams.delete('class_id');

                    const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                    history.replaceState(null, '', newUrl); // This will update the URL
                }
            });
            
            // For other close button
            $('.close-popup').on('click', function() {
                var activePopup = this.parentElement.parentElement.parentElement.id;
                closePopup(activePopup);
                
                if (activePopup == 'class-details-popup') {
                    urlParams.delete('show_modal');
                    urlParams.delete('class_id');

                    const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                    history.replaceState(null, '', newUrl); // This will update the URL
                }
            });

            // Initialize meatball menu
            initializeMeatballMenu();

            // Show the appropriate button based on the active tab
            function updateButtons() {
                var activeTab = $('.tab-link.active').data('tab');
                // Hide both buttons initially
                $('#addCourse').hide();
                $('#addClass').hide();

                if (activeTab === 'courses-tab') {
                    $('#addCourse').show();
                } else if (activeTab === 'classes-tab') {
                    $('#addClass').show();
                }
            }

            // Hide Classes tab link initially
            $('#classes-tab-link').hide();

            // Handle tab click for courses tab
            $('.tab-link').click(function() {
                var tabId = $(this).data('tab');

                if (tabId === 'courses-tab') {
                    $('#classes-tab-link').hide(); // Hide the Classes tab when Courses tab is clicked
                }

                $('.tab-link').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $('#' + tabId).addClass('active');

                updateButtons();

                // For Meatball Menu to load
                updateMeatballMenu();
            });

            $(document).on('click', '.get_code', function() { 
                var classId = $(this).data('class-id');
                var className = $(this).data('class-name');
                var subject = $(this).data('subject');

                $('#msg').html('');
                $('#class-code-popup #modal_class_name').text(className);
                $('#class-code-popup #modal_subject').text(subject);

                // Fetch the code dynamically using AJAX
                $.ajax({
                    url: 'generated_code.php',
                    type: 'POST',
                    data: { class_id: classId }, 
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            $('#class-code-popup #modal_code').text(result.code);
                        } else {
                            $('#class-code-popup #modal_code').text('Error: ' + result.error);
                        }
                        showPopup('class-code-popup');
                    },
                    error: function(xhr, status, error) {
                        $('#modal_code').text('Error fetching code. Please try again.');
                        console.error('Error:', error);
                    }
                });
            });

            // View course details button
            $(document).on('click', '#viewCourseDetails', function() {
                var course_id = $(this).attr('data-id');
                $.ajax({
                    url: 'get_course_details.php',
                    method: 'GET',
                    data: { course_id: course_id },
                    success: function(response) {
                        $('#program-details-popup #courseDetailsBody').html(response);
                        // $('#course_details').modal('show');
                        showPopup('program-details-popup');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while fetching course details.');
                    }
                });
            });

            // View class details button
            $(document).on('click', '#viewClassDetails', function() {
                var class_id = $(this).attr('data-id');
                $.ajax({
                    url: 'get_class_details.php',
                    method: 'GET',
                    data: { class_id: class_id },
                    success: function(response) {
                        $('#class-details-popup #classDetailsBody').html(response);
                        // $('#class_details').modal('show');
                        showPopup('class-details-popup');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while fetching class details.');
                    }
                });
            });
            
            let isButtonClicked = false;
            
            // View Course Details Action Button: View Class
            $(document).on('click', '.action_vcd', function() {
                isButtonClicked = true;

                $('#program-details-popup').modal('hide');

                $('#program-details-popup').one('hidden.bs.modal', function() {
                    $('#class-details-popup').modal('show');
                });
            });

            // View Class Details: Back Button
            $(document).on('click', '.back_vcd', function() {
                isButtonClicked = false;
                
                $('#program-details-popup').modal('hide');

                $('#program-details-popup').one('hidden.bs.modal', function() {
                    $('#program-details-popup').modal('show');
                });
            });

            // To make sure that the isButtonClicked false after exiting the Class Details
            $(document).on('click', '.back_vcd_false', function() {
                isButtonClicked = false;
            });
            
            // When the next modal is shown, check the boolean value
            $('#class-details-popup').on('shown.bs.modal', function() {
                if (isButtonClicked) {
                    $('#back-button-container').html('<button class="btn btn-secondary back_vcd" data-dismiss="modal">Back</button>');
                } else {
                    $('#back-button-container').html('');
                }
            });

            // Handle Classes button click
            $('.viewClasses').click(function() {
                var course_id = $(this).attr('data-id');
                var course_name = $(this).attr('data-name');

                // Show the Classes tab and set the course name
                $('#classes-tab-link').show().click();
                $('#classes-tab-link').text(course_name);

                // Fetch and display classes associated with the course
                getClasses(course_id);

                // Set the hidden course_id field in the add class form
                $('#add-class-popup input[name="course_id"]').val(course_id);
            });

            function initializeMeatballMenu() {
                // Ensure the click event is bound to dynamically loaded elements
                $(document).on('click', '.meatball-menu-btn', function(event) {
                    event.stopPropagation();
                    $('.meatball-menu-container').not($(this).parent()).removeClass('show');
                    $(this).parent().toggleClass('show');
                });

                // Close the menu if clicked outside
                $(document).on('click', function(event) {
                    if (!$(event.target).closest('.meatball-menu-container').length) {
                        $('.meatball-menu-container').removeClass('show');
                    }
                });
            }

            function updateMeatballMenu() {
                // Remove any existing open menus
                $('.meatball-menu-container').removeClass('show');
            }

            // Check if the URL contains `show_modal=true`
            const showModal = urlParams.get('show_modal');
            const classId = urlParams.get('class_id');

            // If `show_modal` is true, open the class details modal
            if (showModal === 'true' && classId) {
                // Show the modal
                showPopup('class-details-popup');

                // Fetch class details dynamically
                fetchClassDetails(classId);
            }

            function fetchClassDetails(classId) {
                $.ajax({
                    url: 'get_class_details.php',
                    type: 'GET',
                    data: { class_id: classId },
                    success: function (response) {
                        // Load the response into the modal body
                        $('#class-details-popup #classDetailsBody').html(response);
                    },
                    error: function () {
                        $('#class-details-popup #classDetailsBody').html('<p>Error loading class details.</p>');
                    }
                });
            }

            // Ensure meatball menu is initialized after any dynamic content changes
            $(document).ajaxComplete(function() {
                updateMeatballMenu();
            });
        });
        </script>
    </body>
</html>