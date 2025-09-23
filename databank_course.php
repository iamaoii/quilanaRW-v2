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
        /* Remove black lines for pre-selected items */
        button:focus,
        input:focus,
        a:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        /* BODY */
        body {
            overflow-y: auto; 
            margin: 0;
            min-height: 100vh;
            position: relative;
            padding-bottom: 60px;
        }

        /* HEADER CONTROLS */
        .program-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0px 35px;
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
            background: #fff;
            overflow-y: auto; 
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
        .long-search-bar button:hover {
            color: #4A4CA6;
        }

        /* BUTTONS */
        .button-group {
            display: flex;
            gap: 15px;
        }
        .add-course-btn,
        .add-topic-btn {
            background: #413E81;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .add-course-btn:hover,
        .add-topic-btn:hover {
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
            background: #FFFFFF;
            border-top: 1px solid #F0EFEF;
            transition: max-height 0.3s ease;
        }
        .course-content.active {
            max-height: 500px;
        }
        .course-details {
            padding: 20px;
        }

        /* TOPICS SECTION */
        .topics-section h4 {
            margin: 20px 0 20px 30px;
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
            margin: 0 0 0 15px;
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

        /* MEATBALL MENU */
        .meatball-menu {
            position: absolute;
            top: 12px;
            right: 12px;
        }
        .meatball-button {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
            transition: color 0.2s ease;
        }
        .meatball-button:hover {
            color: #000;
        }
        .meatball-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 28px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            z-index: 1000;
        }
        .meatball-dropdown a {
            display: block;
            padding: 8px 14px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        .meatball-dropdown a:hover {
            background: #f6f6f6;
        }

        /* BACK BUTTON */
        .search-back-btn {
            background: #4A4CA6;
            color: #fff;
            border-radius: 10px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: background 0.2s ease;
        }
        .search-back-btn:hover {
            background: #131179ff;
            color: #fff;
        }

        .search-wrapper {
            display: flex;
            align-items: center;
            gap: 8px; 
        }
    </style>
</head>

<?php include('nav_bar.php'); ?>

<body>
<!-- Program Header -->
<div class="program-header">
    <a href="databank.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Programs
    </a>
    <h1 class="program-title"><?php echo htmlspecialchars($program['program_name']); ?></h1>
    <div style="flex:1;"></div>
</div>

<div class="content-wrapper">
<div class="controls-section">
    <!-- Grouped back button + search bar -->
    <div class="search-wrapper">
        <a href="databank.php" class="search-back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="long-search-bar">
            <input type="text" placeholder="Search courses..." id="course-search-input">
            <button id="course-search-btn"><i class="fas fa-search"></i></button>
        </div>
    </div>
        <div class="button-group">
            <button class="primary-button add-course-btn">
                <i class="fas fa-plus"></i> Add Course
            </button>
            <button class="primary-button add-topic-btn" onclick="showAddTopicPopup()">
                <i class="fas fa-plus"></i> Add Topic
            </button>
        </div>
    </div>

    <!-- Course List -->
    <div class="course-list" id="course-list">
        <?php
        $course_query = $conn->prepare("
            SELECT c.* 
            FROM rw_bank_course c 
            INNER JOIN rw_bank_program_course pc 
            ON c.course_id = pc.course_id 
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
                                INNER JOIN rw_bank_program_course pc 
                                ON t.program_course_id = pc.program_course_id 
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
                                        <a href="#" class="view" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="#" class="edit-topic-btn" data-topic-id="<?php echo $topic['topic_id']; ?>" 
                                           data-topic-name="<?php echo htmlspecialchars($topic['topic_name']); ?>">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <a href="#" class="delete" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
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
include('databank_edit_topic.php');
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const programId = <?php echo json_encode($program_id); ?>;

    // ======== SEARCH COURSES ========
    const searchInput = document.getElementById('course-search-input');
    const searchBtn = document.getElementById('course-search-btn');
    const courseList = document.getElementById('course-list');

    function performSearch() {
        const searchTerm = searchInput.value.trim();

        const formData = new FormData();
        formData.append('search', searchTerm);
        formData.append('program_id', programId);

        fetch('databank_course_search.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                courseList.innerHTML = data.html;
                attachCourseToggleListeners();
                attachMeatballMenuListeners();
                attachTopicActionListeners();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to perform search',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
            }
        })
        .catch(err => {
            console.error('Search error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred during search',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'swal-btn' }
            });
        });
    }

    // Debounced search on input
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300); // 300ms debounce
    });

    // Search on button click
    searchBtn.addEventListener('click', performSearch);

    // ======== TOGGLE COURSE CONTENT ========
    function attachCourseToggleListeners() {
        document.querySelectorAll('.course-header').forEach(header => {
            header.addEventListener('click', function () {
                const content = this.nextElementSibling;
                const toggleIcon = this.querySelector('.course-toggle i');

                // Close others
                document.querySelectorAll('.course-content.active').forEach(c => {
                    if (c !== content) {
                        c.classList.remove('active');
                        c.previousElementSibling.querySelector('.course-toggle i')
                            .classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });

                // Toggle current
                content.classList.toggle('active');
                toggleIcon.classList.toggle('fa-chevron-down');
                toggleIcon.classList.toggle('fa-chevron-up');
            });
        });
    }

    // ======== TOPIC MEATBALL MENU ========
    function attachMeatballMenuListeners() {
        document.querySelectorAll('.topic-card .meatball-menu-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                document.querySelectorAll('.topic-card .meatball-menu-container').forEach(c => {
                    if (c !== btn.parentElement) c.classList.remove('show');
                });
                btn.parentElement.classList.toggle('show');
            });
        });

        // Close meatball menu when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.topic-card .meatball-menu-container').forEach(c => c.classList.remove('show'));
        });
    }

    // ======== TOPIC ACTIONS ========
    function attachTopicActionListeners() {
        document.querySelectorAll('.topic-card').forEach(topicCard => {
            // View topic
            topicCard.querySelector('.view')?.addEventListener('click', (e) => {
                e.preventDefault();
                const topicName = topicCard.querySelector('.topic-name').textContent;
                Swal.fire({
                    title: 'View Topic',
                    text: `Viewing topic: ${topicName}`,
                    icon: 'info',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
            });

            // Delete topic
            topicCard.querySelector('.delete')?.addEventListener('click', (e) => {
                e.preventDefault();
                const topicId = e.target.closest('.delete').getAttribute('data-topic-id');
                const topicName = topicCard.querySelector('.topic-name').textContent;

                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${topicName}". This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'rgba(255, 108, 108, 1)',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    customClass: { confirmButton: 'swal-btn' }
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('databank_delete_topic.php', {
                            method: 'POST',
                            body: new URLSearchParams({ topic_id: topicId })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Topic has been deleted.',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    performSearch(); // Refresh the list
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message || 'Failed to delete topic.',
                                    confirmButtonText: 'OK',
                                    customClass: { confirmButton: 'swal-btn' }
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Unexpected error occurred.',
                                confirmButtonText: 'OK',
                                customClass: { confirmButton: 'swal-btn' }
                            });
                        });
                    }
                });
            });

            // View details
            topicCard.querySelector('.view-details-btn')?.addEventListener('click', (e) => {
                e.preventDefault();
                const topicName = topicCard.querySelector('.topic-name').textContent;
                Swal.fire({
                    title: 'Topic Details',
                    text: `Viewing details for: ${topicName}`,
                    icon: 'info',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
            });

            // Edit topic
            topicCard.querySelector('.edit-topic-btn')?.addEventListener('click', (e) => {
                e.preventDefault();
                const topicId = e.target.closest('.edit-topic-btn').getAttribute('data-topic-id');
                const topicName = e.target.closest('.edit-topic-btn').getAttribute('data-topic-name');

                const topicEditOverlay = document.getElementById('topic-edit-overlay');
                const topicEditForm = document.getElementById('edit-topic-form');

                if (topicEditForm) {
                    topicEditForm.querySelector('#edit_topic_id').value = topicId;
                    topicEditForm.querySelector('#edit_topic_name').value = topicName;
                    topicEditOverlay.style.display = 'flex';
                }
            });
        });
    }

    // ======== EDIT TOPIC ========
    const topicEditOverlay = document.getElementById('topic-edit-overlay');
    const topicEditForm = document.getElementById('edit-topic-form');
    const topicEditCloseBtn = topicEditOverlay?.querySelector('.topic-popup-close');

    topicEditCloseBtn?.addEventListener('click', () => {
        topicEditOverlay.style.display = 'none';
    });

    topicEditOverlay?.addEventListener('click', (e) => {
        if (e.target === topicEditOverlay) {
            topicEditOverlay.style.display = 'none';
        }
    });

    topicEditForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const topicName = topicEditForm.querySelector('#edit_topic_name').value.trim();
        const topicId = topicEditForm.querySelector('#edit_topic_id').value;

        if (!topicName) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please enter a topic name',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'swal-btn' }
            });
            return;
        }

        fetch('databank_edit_topic.php', {
            method: 'POST',
            body: new URLSearchParams({
                topic_id: topicId,
                topic_name: topicName
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                topicEditOverlay.style.display = 'none';
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Topic updated successfully',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    performSearch(); // Refresh the list
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update topic',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unexpected error occurred',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'swal-btn' }
            });
        });
    });

    // Initial attachment of listeners
    attachCourseToggleListeners();
    attachMeatballMenuListeners();
    attachTopicActionListeners();
});
</script>

</body>
</html>