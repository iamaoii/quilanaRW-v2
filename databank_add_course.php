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
                $link_course->execute();
                
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
    <div class="popup-content">
        <button class="popup-close" onclick="closeCoursePopup()">&times;</button>
        <h2 class="popup-title">Add New Course</h2>

        <form id="course-form" class="popup-form">
            <div class="modal-body">
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
// Show add course popup
function showAddCoursePopup() {
    document.getElementById('course-popup-overlay').style.display = 'flex';
}

// Close add course popup
function closeCoursePopup() {
    document.getElementById('course-popup-overlay').style.display = 'none';
}

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
                if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                closeCoursePopup();
                location.reload();
            });
        }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.'
        });
    });
});
</script>

<style>
/* Popup overlay */
#course-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

/* Popup content */
#course-popup-overlay .popup-content {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    position: relative;
}

/* Title */
#course-popup-overlay .popup-title {
    margin-bottom: 20px;
    font-size: 22px;
    font-weight: bold;
    text-align: center;
}

/* Close button */
#course-popup-overlay .popup-close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 22px;
    background: none;
    border: none;
    cursor: pointer;
}
#course-popup-overlay .popup-close:hover {
    color: #555;
    background-color: #f0f0f0;
}

/* Input fields */
#course-popup-overlay .popup-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid rgb(59, 39, 110);
    border-radius: 12px;
    font-size: 15px;
    outline: none;
}
#course-popup-overlay .popup-input:hover {
    border-color: rgb(90, 70, 150);
    outline: 1.5px solid rgba(90, 70, 150, 0.4);
    box-shadow: 0 0 10px rgba(126, 87, 194, 0.6);
}
#course-popup-overlay .popup-input:focus {
    border-color: #7e57c2;
    box-shadow: 0 0 10px rgba(126, 87, 194, 0.6);
}

/* Footer */
#course-popup-overlay .modal-footer {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

/* Button */
#course-popup-overlay .secondary-button {
    background-image: linear-gradient(to right, #8794F2, #6E72C1);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    padding: 10px 0;
    width: 80%;
    max-width: 320px;
    cursor: pointer;
}
#course-popup-overlay .secondary-button:hover {
    background-color: #4A4CA6;
    background-image: none;
}
</style>
