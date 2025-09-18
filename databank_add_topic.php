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
    
    if (isset($_POST['topic_name']) && isset($_POST['course_id'])) {
        $topic_name = trim($_POST['topic_name']);
        $course_id = $_POST['course_id'];
        $created_by = $_SESSION['login_id'];
        
        if (empty($topic_name)) {
            $response['success'] = false;
            $response['message'] = 'Topic name is required';
        } else {
            // Start transaction
            $conn->begin_transaction();
            try {
                // First, get the program_course_id for this course
                $program_course_query = $conn->prepare("SELECT program_course_id FROM rw_bank_program_course WHERE course_id = ?");
                $program_course_query->bind_param("i", $course_id);
                $program_course_query->execute();
                $program_course_result = $program_course_query->get_result();
                
                if ($program_course_result->num_rows == 0) {
                    throw new Exception('Course not found in any program');
                }
                
                $program_course_row = $program_course_result->fetch_assoc();
                $program_course_id = $program_course_row['program_course_id'];
                
                // Check if topic already exists for this program-course
                $check_query = $conn->prepare("SELECT topic_id FROM rw_bank_topic WHERE topic_name = ? AND program_course_id = ?");
                $check_query->bind_param("si", $topic_name, $program_course_id);
                $check_query->execute();
                
                if ($check_query->get_result()->num_rows > 0) {
                    throw new Exception('This topic already exists in this course');
                }
                
                // Insert new topic
                $insert_topic = $conn->prepare("INSERT INTO rw_bank_topic (topic_name, program_course_id, no_of_questions) VALUES (?, ?, 0)");
                $insert_topic->bind_param("si", $topic_name, $program_course_id);
                $insert_topic->execute();
                
                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'Topic added successfully';
                $response['topic_id'] = $conn->insert_id;
                
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            }
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Missing required parameters';
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!-- Add Topic Popup -->
<div id="topic-popup-overlay" class="popup-overlay">
    <div class="popup-content">
        <button class="popup-close" onclick="closeTopicPopup()">&times;</button>
        <h2 class="popup-title">Add New Topic</h2>

        <form id="topic-form" class="popup-form">
            <div class="modal-body">
                <div class="form-group">
                    <label>Course</label>
                    <select name="course_id" required class="popup-input">
                        <option value="">Select course</option>
                        <?php
                        // Fetch courses for dropdown
                        $courses_query = "SELECT c.* FROM rw_bank_course c 
                                        JOIN rw_bank_program_course pc ON c.course_id = pc.course_id 
                                        WHERE pc.program_id = ? AND c.created_by = ?
                                        ORDER BY c.course_name";
                        $stmt = $conn->prepare($courses_query);
                        $stmt->bind_param("ii", $program_id, $created_by);
                        $stmt->execute();
                        $courses = $stmt->get_result();

                        while ($course = $courses->fetch_assoc()) {
                            echo '<option value="' . $course['course_id'] . '">' . htmlspecialchars($course['course_name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Topic Name</label>
                    <input type="text" name="topic_name" required class="popup-input" placeholder="Enter topic name" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="secondary-button">Add Topic</button>
            </div>
        </form>
    </div>
</div>

<script>
// Show add topic popup
function showAddTopicPopup() {
    document.getElementById('topic-popup-overlay').style.display = 'flex';
}

// Close add topic popup
function closeTopicPopup() {
    document.getElementById('topic-popup-overlay').style.display = 'none';
}

// Handle topic form submission
document.getElementById('topic-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('databank_add_topic.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
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
            text: 'An unexpected error occurred'
        });
    });
});
</script>