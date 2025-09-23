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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();

    if (isset($_POST['course_name']) && isset($_POST['program_id'])) {
        $course_name = trim($_POST['course_name']);
        $program_id = $_POST['program_id'];
        $created_by = $_SESSION['login_id'];

        if (empty($course_name)) {
            $response['success'] = false;
            $response['message'] = 'Course name is required';
        } else {
            $conn->begin_transaction();
            try {
                // Check if course exists
                $check_query = $conn->prepare("SELECT course_id FROM rw_bank_course WHERE course_name = ? AND created_by = ?");
                $check_query->bind_param("si", $course_name, $created_by);
                $check_query->execute();
                $result = $check_query->get_result();

                if ($result->num_rows > 0) {
                    $course = $result->fetch_assoc();
                    $course_id = $course['course_id'];

                    // Check if course already linked to this program
                    $program_check = $conn->prepare("SELECT * FROM rw_bank_program_course WHERE program_id = ? AND course_id = ?");
                    $program_check->bind_param("ii", $program_id, $course_id);
                    $program_check->execute();

                    if ($program_check->get_result()->num_rows > 0) {
                        throw new Exception('This course is already added to this program');
                    }
                } else {
                    // Insert new course
                    $insert_course = $conn->prepare("INSERT INTO rw_bank_course (course_name, created_by, no_of_topics) VALUES (?, ?, 0)");
                    $insert_course->bind_param("si", $course_name, $created_by);
                    if (!$insert_course->execute()) {
                        throw new Exception('Error creating course: ' . $conn->error);
                    }
                    $course_id = $conn->insert_id;
                }

                // Link course to program
                $link_course = $conn->prepare("INSERT INTO rw_bank_program_course (program_id, course_id) VALUES (?, ?)");
                $link_course->bind_param("ii", $program_id, $course_id);
                if (!$link_course->execute()) {
                    throw new Exception('Error linking course to program: ' . $conn->error);
                }

                $conn->commit();

                $response['success'] = true;
                $response['message'] = 'Course added successfully';
                $response['course_id'] = $course_id;
            } catch (Exception $e) {
                $conn->rollback();
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            }
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Missing required parameters';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!-- Add Course Popup -->
<div id="course-popup-overlay" class="popup-overlay">
    <div class="popup-content" role="document">
        <button class="popup-close course-popup-close">&times;</button>
        <h2 class="popup-title">Add New Course</h2>

        <form id="course-form" class="popup-form">
            <div class="modal-body">
                <div id="course-msg"></div>
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" required class="popup-input" placeholder="Enter course name" />
                </div>
                <input type="hidden" name="program_id" value="<?php echo isset($program_id) ? htmlspecialchars($program_id) : ''; ?>" />
            </div>
            <div class="modal-footer">
                <button type="submit" class="secondary-button">Add Course</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const coursePopup = document.getElementById('course-popup-overlay');
    const courseForm = document.getElementById('course-form');
    const addCourseBtn = document.querySelector('.add-course-btn');

    // Open modal on add button click
    if (addCourseBtn && coursePopup) {
        addCourseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            coursePopup.style.display = 'flex';
            courseForm.querySelector('[name="course_name"]').focus();
        });
    }

    // Close on X button click
    const courseCloseBtn = coursePopup ? coursePopup.querySelector('.course-popup-close') : null;
    if (courseCloseBtn && courseForm && coursePopup) {
        courseCloseBtn.addEventListener('click', () => {
            coursePopup.style.display = 'none';
            courseForm.reset();
        });
    }

    // Close on overlay click
    if (coursePopup) {
        coursePopup.addEventListener('click', (e) => {
            if (e.target === coursePopup) {
                coursePopup.style.display = 'none';
                courseForm.reset();
            }
        });
    }

    // Handle form submission
    if (courseForm) {
        courseForm.addEventListener('submit', function(e) {
            e.preventDefault();

            let courseName = this.querySelector('[name="course_name"]').value.trim();

            if (courseName === "") {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter a course name',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
                return;
            }

            const formData = new FormData(this);

            fetch('databank_add_course.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close popup
                    coursePopup.style.display = 'none';
                    courseForm.reset();

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
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
    }
});
</script>