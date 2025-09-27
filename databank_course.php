<?php
include 'db_connect.php';
include 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

// Get program details
$program_id = isset($_GET['id']) ? intval($_GET['id']) : null;
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

$programName = htmlspecialchars($program['program_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($programName); ?> | Quilana</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Global */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff;
            color: #1E1A43;
        }

        /* Controls Row */
        .course-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 35px;
            flex-wrap: wrap;
        }
        
        .databank-course-wrapper {
            height: calc(100vh - 60px);
            overflow-y: auto;
            padding:0px;
            padding-right: 0;
        }

        /* Back Button */
        .back-btn {
            background-color: #4A4CA6;
            border: none;
            color: #fff;
            border-radius: 6px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
        }
        .back-btn:hover {
            background-color: #3b3d85;
        }

        /* Search Bar */
        .long-search-bar {
            display: flex;
            align-items: center;
            border: 1px solid #3B276E;
            border-radius: 10px;
            padding: 0 10px;
            width: 100%;
            max-width: 750px;
            min-height: 40px;
        }
        .long-search-bar input[type="text"] {
            border: none;
            outline: none;
            flex: 1;
            padding: 8px 4px;
            font-size: 14px;
        }
        .long-search-bar button {
            background: none;
            border: none;
            cursor: pointer;
            color: #737791;
        }
        .long-search-bar button:hover {
            color: #4A4CA6;
        }

        /* Add Dropdown */
        .add-dropdown {
            position: relative;
            margin-left: auto;
        }
        .add-course-btn {
            background: #413E81;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .add-course-btn:hover {
            background: #333274;
        }
        .add-course-btn i.fas.fa-plus {
            font-size: 12px; /* Smaller plus icon */
        }
        .add-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(5px);
            background: rgb(247, 247, 247);
            border-radius: 6px;
            width: 100%; /* Match the width of the add-course-btn */
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 999;
            text-align: center;
            padding: 10px;
        }
        .add-dropdown-menu.show {
            display: block;
        }
        .add-dropdown-menu button {
            display: block;
            width: 100%;
            padding: 10px;
            color: #1E1A43;
            background: none;
            border: none;
            font-size: 14px; 
            font-weight: 600; 
            cursor: pointer;
            text-align: center;
        }
        .add-dropdown-menu button:hover {
            background: rgba(74, 76, 166, 0.1);
            border-radius: 6px;
        }
        .add-course-btn-option i.fas.fa-plus,
        .add-topic-btn-option i.fas.fa-plus {
            font-size: 12px;
        }

        /* Header */
        .courses-header {
            font-size: 30px;
            margin: 30px 35px 20px;
            color: #1E1A43;
            font-weight: bolder;
        }

        /* COURSE LIST */
        .course-list {
            margin: 50px 35px;
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
            flex-grow: 1;
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
            max-height: 2000px;
        }
        .course-details {
            padding: 20px;
            padding-right: 0;
            position: relative;
        }

        /* TOPICS SECTION */
        .topics-section h4 {
            margin: 20px 0 20px 30px;
            color: #1E1A43;
            font-size: 18px;
            font-weight: bold;
        }

        .topic-container {
            max-height: none;
            overflow-y: visible;
            display: flex;
            flex-wrap: wrap;
            gap: 29px;
            margin-bottom: 20px;
            margin-right: 0;
            padding-right: 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .topic-container::-webkit-scrollbar {
            display: none;
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
            white-space: nowrap; 
            display: block;
            background: linear-gradient(90deg, #6E72C1 0%, #4A4CA6 100%);
            color: #fff;
            border: 1px #f7f7f7ff;
            border-radius: 10px;
            padding: 3px 0px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin: 0 3px 3px 3px;
            text-decoration: none;
            text-align: center;
            width: calc(100% - 2px);
            box-sizing: border-box;
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
        .meatball-menu-container {
            position: absolute;
            top: 12px;
            right: 12px;
        }
        .meatball-menu-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
            transition: color 0.2s ease;
        }
        .meatball-menu-btn:hover {
            color: #000;
        }
        .meatball-menu {
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
        .meatball-menu.show {
            display: block;
        }
        .meatball-menu a {
            display: block;
            padding: 8px 14px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        .meatball-menu a:hover {
            background: #f6f6f6;
        }

        /* Popup Overlay (Course and Topic) */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-content {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            width: 400px;
            max-width: 90%;
            position: relative;
        }
        .popup-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            background: none;
            border: none;
            color: #666;
        }
        .popup-form .form-group {
            margin-bottom: 15px;
        }
        .popup-form .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #1E1A43;
        }
        .popup-form .form-group input,
        .popup-form .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .popup-form .modal-footer {
            text-align: right;
        }
        .popup-form .modal-footer button {
            background: #413E81;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 14px;
        }
        .popup-form .modal-footer button:hover {
            background: #333274;
        }

        /* RESPONSIVENESS */
        @media (max-width: 768px) {
            .topic-container {
                max-height: 300px;  
                overflow-y: auto;
                -ms-overflow-style: none; 
                scrollbar-width: thin;
            }  
            .course-controls {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .long-search-bar {
                max-width: 100%;
            }
            .add-course-btn {
                width: 100%;
            }
        }

    </style>
</head>

<?php include('nav_bar.php'); ?>

<body>
    <div class="content-wrapper">
        <div class="databank-course-wrapper">
        <!-- Controls Row -->
        <div class="course-controls">
            <!-- Back Button -->
            <button class="back-btn" onclick="window.location.href='databank.php'">
                <i class="fas fa-arrow-left"></i>
            </button>

            <!-- Search Bar -->
            <div class="long-search-bar">
                <input type="text" placeholder="Search courses or topics" id="course-search-input">
                <button id="course-search-btn"><i class="fas fa-search"></i></button>
            </div>

            <!-- Add Dropdown -->
            <div class="add-dropdown">
                <button class="add-course-btn">
                    <i class="fas fa-plus"></i> Add Course/Topic
                </button>
                <div class="add-dropdown-menu">
                    <button class="add-course-btn-option"> <i class="fas fa-plus"></i> Add Course</button>
                    <button class="add-topic-btn-option"> <i class="fas fa-plus"></i> Add Topic</button>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <h2 class="courses-header"><?php echo htmlspecialchars($programName); ?></h2>

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
            <div class="course-item" data-course-id="<?php echo htmlspecialchars($course['course_id']); ?>">
                <div class="course-header">
                    <h3 class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                    <button class="course-toggle">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="course-content">
                    <div class="course-details">
                        <!-- Course Meatball Menu -->
                        <div class="meatball-menu-container">
                            <button class="meatball-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="meatball-menu">
                                <a href="#" class="edit-course-btn" data-course-id="<?php echo $course['course_id']; ?>" 
                                   data-course-name="<?php echo htmlspecialchars($course['course_name']); ?>">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="#" class="delete-course-btn" data-course-id="<?php echo $course['course_id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                        <div class="topics-section">
                            <h4>Topics</h4>
                            <div class="topic-container">
                                <?php
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
                                    <a href="#" class="view-details-btn" 
                                        data-topic-id="<?php echo $topic['topic_id']; ?>"
                                        data-course-id="<?php echo $course['course_id']; ?>"
                                        data-program-id="<?php echo $program_id; ?>">View Questions</a>
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
    </div>

    <!-- Course Add Popup -->
    <div id="course-add-overlay" class="popup-overlay">
        <div class="popup-content">
            <button class="popup-close">&times;</button>
            <h2 class="popup-title">Add Course</h2>
            <form id="course-add-form" class="popup-form">
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" id="add_course_name" required class="popup-input" placeholder="Enter course name" />
                </div>
                <input type="hidden" name="program_id" value="<?php echo htmlspecialchars($program_id); ?>" />
                <div class="modal-footer">
                    <button type="submit" class="secondary-button">Add Course</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Topic Add Popup -->
    <div id="topic-add-overlay" class="popup-overlay">
        <div class="popup-content">
            <button class="popup-close">&times;</button>
            <h2 class="popup-title">Add Topic</h2>
            <form id="topic-add-form" class="popup-form">
                <div class="form-group">
                    <label>Course</label>
                    <select name="course_id" id="add_topic_course_id" required>
                        <option value="">Select a course</option>
                        <?php
                        $course_query = $conn->prepare("
                            SELECT c.course_id, c.course_name 
                            FROM rw_bank_course c 
                            INNER JOIN rw_bank_program_course pc 
                            ON c.course_id = pc.course_id 
                            WHERE pc.program_id = ? 
                            ORDER BY c.course_name ASC
                        ");
                        $course_query->bind_param("i", $program_id);
                        $course_query->execute();
                        $courses = $course_query->get_result();
                        while ($course = $courses->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($course['course_id']) . '">' . htmlspecialchars($course['course_name']) . '</option>';
                        }
                        $course_query->close();
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Topic Name</label>
                    <input type="text" name="topic_name" id="add_topic_name" required class="popup-input" placeholder="Enter topic name" />
                </div>
                <input type="hidden" name="program_id" value="<?php echo htmlspecialchars($program_id); ?>" />
                <div class="modal-footer">
                    <button type="submit" class="secondary-button">Add Topic</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Course Edit Popup -->
    <div id="course-edit-overlay" class="popup-overlay">
        <div class="popup-content">
            <button class="popup-close">&times;</button>
            <h2 class="popup-title">Edit Course</h2>
            <form id="course-edit-form" class="popup-form">
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" id="edit_course_name" required class="popup-input" placeholder="Enter course name" />
                </div>
                <input type="hidden" name="course_id" id="edit_course_id" />
                <input type="hidden" name="program_id" value="<?php echo htmlspecialchars($program_id); ?>" />
                <div class="modal-footer">
                    <button type="submit" class="secondary-button">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Topic Edit Popup -->
    <div id="topic-edit-overlay" class="popup-overlay">
        <div class="popup-content">
            <button class="popup-close">&times;</button>
            <h2 class="popup-title">Edit Topic</h2>
            <form id="topic-edit-form" class="popup-form">
                <div class="form-group">
                    <label>Topic Name</label>
                    <input type="text" name="topic_name" id="edit_topic_name" required class="popup-input" placeholder="Enter topic name" />
                </div>
                <input type="hidden" name="topic_id" id="edit_topic_id" />
                <div class="modal-footer">
                    <button type="submit" class="secondary-button">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

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
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    courseList.innerHTML = data.html;
                    attachCourseToggleListeners();
                    attachMeatballMenuListeners();
                    attachTopicActionListeners();
                    attachCourseActionListeners();
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Results',
                        text: data.message || 'No courses or topics found',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'swal-btn' }
                    });
                    courseList.innerHTML = '<p class="no-courses">No courses or topics found</p>';
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
            searchTimeout = setTimeout(performSearch, 300);
        });

        // Search on button click
        searchBtn.addEventListener('click', performSearch);

        // ======== TOGGLE COURSE CONTENT ========
        function attachCourseToggleListeners() {
            document.querySelectorAll('.course-header').forEach(header => {
                header.addEventListener('click', function (e) {
                    if (e.target.closest('.meatball-menu-container')) return;
                    const content = this.nextElementSibling;
                    const toggleIcon = this.querySelector('.course-toggle i');
                    content.classList.toggle('active');
                    toggleIcon.classList.toggle('fa-chevron-down');
                    toggleIcon.classList.toggle('fa-chevron-up');
                });
            });
        }

        // ======== MEATBALL MENU ========
        function attachMeatballMenuListeners() {
            document.querySelectorAll('.meatball-menu-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    document.querySelectorAll('.meatball-menu-container').forEach(c => {
                        if (c !== btn.parentElement) c.classList.remove('show');
                    });
                    btn.parentElement.classList.toggle('show');
                });
            });

            document.addEventListener('click', () => {
                document.querySelectorAll('.meatball-menu-container').forEach(c => c.classList.remove('show'));
            });
        }

        // ======== ADD DROPDOWN ========
        const dropdown = document.querySelector('.add-dropdown');
        const addBtn = dropdown.querySelector('.add-course-btn');
        const dropdownMenu = dropdown.querySelector('.add-dropdown-menu');

        addBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        // Prevent dropdown from closing when clicking inside
        dropdownMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // ======== ADD COURSE POPUP ========
        const courseAddOverlay = document.getElementById('course-add-overlay');
        const courseAddForm = document.getElementById('course-add-form');
        const courseAddCloseBtn = courseAddOverlay.querySelector('.popup-close');

        function showAddCoursePopup() {
            courseAddOverlay.style.display = 'flex';
            courseAddForm.querySelector('#add_course_name').focus();
            dropdownMenu.classList.remove('show');
        }

        document.querySelector('.add-course-btn-option').addEventListener('click', showAddCoursePopup);

        courseAddCloseBtn.addEventListener('click', () => {
            courseAddOverlay.style.display = 'none';
            courseAddForm.reset();
        });

        courseAddOverlay.addEventListener('click', (e) => {
            if (e.target === courseAddOverlay) {
                courseAddOverlay.style.display = 'none';
                courseAddForm.reset();
            }
        });

        courseAddForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const courseName = courseAddForm.querySelector('#add_course_name').value.trim();
            const programId = courseAddForm.querySelector('[name="program_id"]').value;

            if (!courseName) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a course name',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
                return;
            }

            fetch('databank_add_course.php', {
                method: 'POST',
                body: new URLSearchParams({
                    course_name: courseName,
                    program_id: programId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    courseAddOverlay.style.display = 'none';
                    courseAddForm.reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Course added successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        performSearch();
                    });
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

        // ======== ADD TOPIC POPUP ========
        const topicAddOverlay = document.getElementById('topic-add-overlay');
        const topicAddForm = document.getElementById('topic-add-form');
        const topicAddCloseBtn = topicAddOverlay.querySelector('.popup-close');

        function showAddTopicPopup() {
            topicAddOverlay.style.display = 'flex';
            topicAddForm.querySelector('#add_topic_course_id').focus();
            dropdownMenu.classList.remove('show');
        }

        document.querySelector('.add-topic-btn-option').addEventListener('click', showAddTopicPopup);

        topicAddCloseBtn.addEventListener('click', () => {
            topicAddOverlay.style.display = 'none';
            topicAddForm.reset();
        });

        topicAddOverlay.addEventListener('click', (e) => {
            if (e.target === topicAddOverlay) {
                topicAddOverlay.style.display = 'none';
                topicAddForm.reset();
            }
        });

        topicAddForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const courseId = topicAddForm.querySelector('#add_topic_course_id').value;
            const topicName = topicAddForm.querySelector('#add_topic_name').value.trim();
            const programId = topicAddForm.querySelector('[name="program_id"]').value;

            if (!courseId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please select a course',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
                return;
            }

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

            fetch('databank_add_topic.php', {
                method: 'POST',
                body: new URLSearchParams({
                    course_id: courseId,
                    topic_name: topicName,
                    program_id: programId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    topicAddOverlay.style.display = 'none';
                    topicAddForm.reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Topic added successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        performSearch();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to add topic',
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

        // ======== TOPIC ACTIONS ========
        function attachTopicActionListeners() {
            document.querySelectorAll('.topic-card').forEach(topicCard => {
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

                topicCard.querySelector('.delete')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const topicId = e.target.closest('.delete').getAttribute('data-topic-id');
                    const topicName = topicCard.querySelector('.topic-name').textContent;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to delete "${topicName}". This action cannot be undone!`,
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        customClass: { confirmButton: 'swal-btn', 
                                        cancelButton: 'swal-btn' 
                                    }
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
                                        performSearch();
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
                                    title: 'Error',
                                    text: 'Unexpected error occurred.',
                                    confirmButtonText: 'OK',
                                    customClass: { confirmButton: 'swal-btn' }
                                });
                            });
                        }
                    });
                });

                topicCard.querySelector('.view-details-btn')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const topicId = e.target.getAttribute('data-topic-id'); 
                    const courseId = e.target.getAttribute('data-course-id'); 
                    const programId = e.target.getAttribute('data-program-id');
                    console.log('Attributes:', { topicId, courseId, programId });
                    window.location.href = `databank_manage_question.php?program_id=${programId}&course_id=${courseId}&topic_id=${topicId}`;
                });

                topicCard.querySelector('.edit-topic-btn')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const topicId = e.target.closest('.edit-topic-btn').getAttribute('data-topic-id');
                    const topicName = e.target.closest('.edit-topic-btn').getAttribute('data-topic-name');

                    const topicEditOverlay = document.getElementById('topic-edit-overlay');
                    const topicEditForm = document.getElementById('topic-edit-form');

                    if (topicEditForm) {
                        topicEditForm.querySelector('#edit_topic_id').value = topicId;
                        topicEditForm.querySelector('#edit_topic_name').value = topicName;
                        topicEditOverlay.style.display = 'flex';
                        topicEditForm.querySelector('#edit_topic_name').focus();
                    }
                });
            });
        }

        // ======== COURSE ACTIONS ========
        function attachCourseActionListeners() {
            document.querySelectorAll('.course-item').forEach(courseItem => {
                courseItem.querySelector('.edit-course-btn')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const courseId = e.target.closest('.edit-course-btn').getAttribute('data-course-id');
                    const courseName = e.target.closest('.edit-course-btn').getAttribute('data-course-name');

                    const courseEditOverlay = document.getElementById('course-edit-overlay');
                    const courseEditForm = document.getElementById('course-edit-form');

                    if (courseEditForm) {
                        courseEditForm.querySelector('#edit_course_id').value = courseId;
                        courseEditForm.querySelector('#edit_course_name').value = courseName;
                        courseEditOverlay.style.display = 'flex';
                        courseEditForm.querySelector('#edit_course_name').focus();
                    }
                });

                courseItem.querySelector('.delete-course-btn')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const courseId = e.target.closest('.delete-course-btn').getAttribute('data-course-id');
                    const courseName = courseItem.querySelector('.course-name').textContent;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to delete "${courseName}" and all its topics. This action cannot be undone!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        customClass: { confirmButton: 'swal-btn',
                                        cancelButton: 'swal-btn'
                         }
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch('databank_delete_course.php', {
                                method: 'POST',
                                body: new URLSearchParams({ course_id: courseId, program_id: programId })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Course has been deleted.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        performSearch();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: data.message || 'Failed to delete course.',
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
                                    text: 'Unexpected error occurred.',
                                    confirmButtonText: 'OK',
                                    customClass: { confirmButton: 'swal-btn' }
                                });
                            });
                        }
                    });
                });
            });
        }

        // ======== EDIT COURSE ========
        const courseEditOverlay = document.getElementById('course-edit-overlay');
        const courseEditForm = document.getElementById('course-edit-form');
        const courseEditCloseBtn = courseEditOverlay.querySelector('.popup-close');

        courseEditCloseBtn.addEventListener('click', () => {
            courseEditOverlay.style.display = 'none';
            courseEditForm.reset();
        });

        courseEditOverlay.addEventListener('click', (e) => {
            if (e.target === courseEditOverlay) {
                courseEditOverlay.style.display = 'none';
                courseEditForm.reset();
            }
        });

        courseEditForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const courseName = courseEditForm.querySelector('#edit_course_name').value.trim();
            const courseId = courseEditForm.querySelector('#edit_course_id').value;
            const programId = courseEditForm.querySelector('[name="program_id"]').value;

            if (!courseName) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a course name',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
                return;
            }

            fetch('databank_edit_course.php', {
                method: 'POST',
                body: new URLSearchParams({
                    course_id: courseId,
                    course_name: courseName,
                    program_id: programId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    courseEditOverlay.style.display = 'none';
                    courseEditForm.reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Course updated successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        performSearch();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update course',
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

        // ======== EDIT TOPIC ========
        const topicEditOverlay = document.getElementById('topic-edit-overlay');
        const topicEditForm = document.getElementById('topic-edit-form');
        const topicEditCloseBtn = topicEditOverlay.querySelector('.popup-close');

        topicEditCloseBtn.addEventListener('click', () => {
            topicEditOverlay.style.display = 'none';
            topicEditForm.reset();
        });

        topicEditOverlay.addEventListener('click', (e) => {
            if (e.target === topicEditOverlay) {
                topicEditOverlay.style.display = 'none';
                topicEditForm.reset();
            }
        });

        topicEditForm.addEventListener('submit', (e) => {
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
                    topicEditForm.reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Topic updated successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        performSearch();
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
        attachCourseActionListeners();
    });
    </script>
</body>
</html>