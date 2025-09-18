<?php 
include 'db_connect.php'; 
include 'auth.php'; 

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get program details
$program_id = isset($_GET['id']) ? $_GET['id'] : null;
$created_by = $_SESSION['login_id'];

if (!$program_id) {
    header("Location: databank.php");
    exit();
}

// Fetch program details
$stmt = $conn->prepare("SELECT * FROM rw_bank_program WHERE program_id = ? AND created_by = ?");
$stmt->bind_param("ii", $program_id, $created_by);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();

if (!$program) {
    header("Location: databank.php");
    exit();
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($program['program_name']); ?> | Quilana</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* BODY */
        body {
            overflow: hidden;
            margin: 0;
            height: 100vh;
            position: relative;
        }

        /* HEADER CONTROLS */
        .program-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 35px;
            background: #FFFFFF;
            border-bottom: 1px solid #F0EFEF;
        }

        .program-title {
            font-size: 24px;
            color: #1E1A43;
            font-weight: bold;
            margin: 0;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4A4CA6;
            text-decoration: none;
            font-weight: 500;
        }

        .back-button i {
            font-size: 20px;
        }

        /* CONTENT WRAPPER */
        .content-wrapper {
            height: calc(100vh - 80px);
            padding: 20px 35px;
            background: #F8F9FD;
        }

        /* SEARCH AND ADD CONTROLS */
        .controls-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .long-search-bar {
            display: flex;
            align-items: center;
            border: 1px solid #3B276E;
            border-radius: 10px;
            color: rgba(115, 119, 145, 0.75);
            padding: 0 15px;
            width: 600px;
            min-height: 40px;
            background: #FFFFFF;
        }
        .long-search-bar:hover {
            box-shadow: 0 0 8px rgba(74, 76, 166, 0.5);
        }
        .long-search-bar input[type="text"] {
            padding: 5px;
            font-size: 14px;
            background: none;
            border: none;
            margin: 4px;
            flex-grow: 1;
            outline: none;
        }
        .long-search-bar input:focus {
            outline: none;
        }
        .long-search-bar button {
            background: none;
            color: rgba(115, 119, 145, 0.75);
            width: 30px;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .long-search-bar button:active,
        .long-search-bar button:hover {
            background: none;
            color: #4A4CA6;
            border: none;
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 15px;
        }

        .add-course-btn, .add-topic-btn {
            background: #413E81;
            color: #fff;
            border: none;
            border-radius: 15px;
            padding: 8px 20px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-course-btn:hover, .add-topic-btn:hover {
            background: #333274;
        }

        /* COURSE LIST */
        .course-list {
            margin-top: 20px;
            background: #FFFFFF;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .course-item {
            border-bottom: 1px solid #F0EFEF;
        }

        .course-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            background: #FFFFFF;
            cursor: pointer;
        }

        .course-header:hover {
            background: #F8F9FD;
        }

        .course-name {
            font-size: 16px;
            font-weight: bold;
            color: #1E1A43;
            margin: 0;
        }

        .course-toggle {
            background: none;
            border: none;
            color: #4A4CA6;
            padding: 5px 10px;
            cursor: pointer;
        }
        
        .course-toggle:hover {
            color: #333274;
        }

        .course-content {
            max-height: 0;
            overflow: hidden;
            background: #F8F9FD;
            border-top: 1px solid #F0EFEF;
        }

        .course-content.active {
            max-height: 500px;
        }

        .course-details {
            padding: 20px;
        }

        /* Topics Section */
        .topics-section h4 {
            margin: 0 0 20px 0;
            color: #1E1A43;
            font-size: 18px;
            font-weight: bold;
        }

        .topic-container {
            display: flex;
            flex-wrap: wrap;
            gap: 29px;
            margin-bottom: 20px;
        }

        .topic-card {
            background: #FFFFFF;
            border: 1px solid #F0EFEF;
            border-radius: 20px;
            width: 341px;
            height: 162px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            position: relative;
        }

        .topic-name {
            font-size: 18px;
            font-weight: bold;
            color: #1E1A43;
            margin: 0;
        }

        .view-details-btn {
            background: linear-gradient(90deg, #6E72C1 0%, #4A4CA6 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 113px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            align-self: flex-start;
            margin-top: auto;
            text-decoration: none;
            text-align: center;
        }

        .no-topics {
            text-align: center;
            color: #999;
            font-style: italic;
            margin: 20px 0;
            width: 100%;
        }

        .no-courses {
            text-align: center;
            color: #666;
            margin-top: 50px;
            font-size: 18px;
            background: #FFFFFF;
            padding: 30px;
            border-radius: 10px;
        }


    </style>
</head>

<?php include('nav_bar.php'); ?>

<body>
    <!-- Program Header -->
    <div class="program-header">
        <a href="databank.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Programs
        </a>
        <h1 class="program-title"><?php echo htmlspecialchars($program['program_name']); ?></h1>
        <div style="width: 100px;"><!-- Spacer for flex alignment --></div>
    </div>

    <div class="content-wrapper">
        <!-- Controls Section -->
        <div class="controls-section">
            <div class="long-search-bar">
                <input type="text" placeholder="Search courses..." class="course-search">
                <button><i class="fas fa-search"></i></button>
            </div>

                        <div class="button-group">
                <button class="add-course-btn">
                    <i class="fas fa-plus"></i>
                    Add Course
                </button>
                <button class="add-topic-btn" onclick="showAddTopicPopup()">
                    <i class="fas fa-plus"></i>
                    Add Topic
                </button>
            </div>
        </div>

        <!-- Course List -->
        <div class="course-list">
            <?php
            // Fetch courses for this program using the junction table
            $course_query = $conn->prepare("
                SELECT c.* 
                FROM rw_bank_course c
                INNER JOIN rw_bank_program_course pc ON c.course_id = pc.course_id
                WHERE pc.program_id = ? 
                ORDER BY c.course_name ASC
            ");
            $course_query->bind_param("i", $program_id);
            $course_query->execute();
            $courses = $course_query->get_result();

            if ($courses->num_rows > 0) {
                while ($course = $courses->fetch_assoc()) {
            ?>
                <div class="course-item">
                    <div class="course-header">
                        <h3 class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        <button class="course-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="course-content">
                        <div class="course-details">
                            <div class="topics-section">
                                <h4>Topics</h4>
                                <div class="topic-container">
                                    <?php
                                    // Fetch topics for this course
                                    $topics_query = $conn->prepare("
                                        SELECT t.*, pc.program_course_id 
                                        FROM rw_bank_topic t
                                        INNER JOIN rw_bank_program_course pc ON t.program_course_id = pc.program_course_id
                                        WHERE pc.course_id = ?
                                        ORDER BY t.topic_name ASC
                                    ");
                                    $topics_query->bind_param("i", $course['course_id']);
                                    $topics_query->execute();
                                    $topics_result = $topics_query->get_result();
                                    
                                    if ($topics_result->num_rows > 0) {
                                        while ($topic = $topics_result->fetch_assoc()) {
                                    ?>
                                        <div class="topic-card">
                                            <p class="topic-name"><?php echo htmlspecialchars($topic['topic_name']); ?></p>
                                            
                                            <!-- Actions -->
                                            <div class="meatball-menu-container">
                                                <button class="meatball-menu-btn">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="meatball-menu">
                                                    <a href="#" class="view" data-topic-id="<?php echo $topic['topic_id']; ?>"><i class="fas fa-eye"></i> View</a>
                                                    <a href="#" class="edit" data-topic-id="<?php echo $topic['topic_id']; ?>"><i class="fas fa-pen"></i> Edit</a>
                                                    <a href="#" class="delete" data-topic-id="<?php echo $topic['topic_id']; ?>"><i class="fas fa-trash"></i> Delete</a>
                                                </div>
                                            </div>

                                            <a href="#" class="view-details-btn" data-topic-id="<?php echo $topic['topic_id']; ?>">View Details</a>
                                        </div>
                                    <?php
                                        }
                                    } else {
                                        echo '<p class="no-topics">No topics added yet</p>';
                                    }
                                    $topics_query->close();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<p class="no-courses">No courses added yet</p>';
            }
            $course_query->close();
            ?>
        </div>
    </div>

    <?php 
    include('databank_add_course.php');
    include('databank_add_topic.php');
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const coursePopup = document.getElementById('course-popup-overlay');
            const courseCloseBtn = coursePopup.querySelector('.course-popup-close');
            const courseForm = document.getElementById('course-form');

            // Open popup when Add Course button is clicked
            document.querySelector('.add-course-btn').addEventListener('click', () => {
                coursePopup.style.display = 'flex';
            });

            // Close on X button click
            courseCloseBtn.addEventListener('click', () => {
                coursePopup.style.display = 'none';
            });

            // Close on outside click
            coursePopup.addEventListener('click', (e) => {
                if (e.target === coursePopup) {
                    coursePopup.style.display = 'none';
                }
            });

            // Handle form submission
            courseForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let courseName = courseForm.querySelector('[name="course_name"]').value.trim();
                
                if (courseName === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please enter a course name',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'swal-btn' }
                    });
                    return;
                }

                const formData = new FormData(this);
                
                fetch('databank_add_course.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close popup overlay
                        coursePopup.style.display = 'none';
                        
                        // Success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Course was successfully added',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'swal-btn'
                            }
                        }).then(() => {
                            window.location.reload();
                        });

                        // Reset form
                        courseForm.reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to add course',
                            confirmButtonText: 'OK',
                            customClass: { confirmButton: 'swal-btn' }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'swal-btn' }
                    });
                });
            });
        });
        // Show add course popup
        function showAddCoursePopup() {
            document.getElementById('course-popup-overlay').style.display = 'flex';
            document.getElementById('course_name').focus();
        }

        // Close add course popup
        function closeAddCoursePopup() {
            document.getElementById('course-popup-overlay').style.display = 'none';
            document.getElementById('course-form').reset();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Close on X button click
            document.querySelector('.popup-close').addEventListener('click', closeAddCoursePopup);

            // Close on overlay click
            document.querySelector('.popup-overlay').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddCoursePopup();
                }
            });

            // Handle course form submission
            document.getElementById('course-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('databank_add_course.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred');
                })
                .finally(() => {
                    closeAddCoursePopup();
                });
            });
        });

        // Search functionality
        const searchInput = document.querySelector('.course-search');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const courseItems = document.querySelectorAll('.course-item');
                
                courseItems.forEach(item => {
                    const courseName = item.querySelector('.course-name').textContent.toLowerCase();
                    
                    if (courseName.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        // Course expansion toggle
        document.querySelectorAll('.course-header').forEach(header => {
            header.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const toggle = this.querySelector('.course-toggle i');
                
                // Close all other open items
                document.querySelectorAll('.course-content.active').forEach(item => {
                    if (item !== content) {
                        item.classList.remove('active');
                        item.previousElementSibling.querySelector('.course-toggle i').classList.remove('fa-chevron-up');
                        item.previousElementSibling.querySelector('.course-toggle i').classList.add('fa-chevron-down');
                    }
                });

                // Toggle current item
                content.classList.toggle('active');
                if (content.classList.contains('active')) {
                    toggle.classList.remove('fa-chevron-down');
                    toggle.classList.add('fa-chevron-up');
                } else {
                    toggle.classList.remove('fa-chevron-up');
                    toggle.classList.add('fa-chevron-down');
                }
            });
        });

        // Topic meatball menu toggle
        document.querySelectorAll('.topic-card .meatball-menu-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.querySelectorAll('.topic-card .meatball-menu-container').forEach(c => {
                    if (c !== btn.parentElement) c.classList.remove('show');
                });
                btn.parentElement.classList.toggle('show');
            });
        });

        // Handle topic action buttons
        document.addEventListener('click', function(e) {
            // View topic button
            if (e.target.closest('.topic-card .view')) {
                e.preventDefault();
                e.stopPropagation();
                const link = e.target.closest('.view');
                const topicId = link.getAttribute('data-topic-id');
                const topicName = link.closest('.topic-card').querySelector('.topic-name').textContent;
                
                Swal.fire({
                    title: 'View Topic',
                    text: `Viewing topic: ${topicName}`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            }
            
            // Edit topic button
            if (e.target.closest('.topic-card .edit')) {
                e.preventDefault();
                e.stopPropagation();
                const link = e.target.closest('.edit');
                const topicId = link.getAttribute('data-topic-id');
                const topicName = link.closest('.topic-card').querySelector('.topic-name').textContent;
                
                Swal.fire({
                    title: 'Edit Topic',
                    text: `Edit topic: ${topicName}`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            }
            
            // Delete topic button
            if (e.target.closest('.topic-card .delete')) {
                e.preventDefault();
                e.stopPropagation();
                const link = e.target.closest('.delete');
                const topicId = link.getAttribute('data-topic-id');
                const topicName = link.closest('.topic-card').querySelector('.topic-name').textContent;
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${topicName}". This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Here you would make an AJAX call to delete the topic
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Topic has been deleted.',
                            icon: 'success'
                        }).then(() => {
                            // Reload the page to refresh the topics list
                            window.location.reload();
                        });
                    }
                });
            }

            // View Details button
            if (e.target.closest('.topic-card .view-details-btn')) {
                e.preventDefault();
                const link = e.target.closest('.view-details-btn');
                const topicId = link.getAttribute('data-topic-id');
                const topicName = link.closest('.topic-card').querySelector('.topic-name').textContent;
                
                Swal.fire({
                    title: 'Topic Details',
                    text: `Viewing details for: ${topicName}`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
</body>
</html>