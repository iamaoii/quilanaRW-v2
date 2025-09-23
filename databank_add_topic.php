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
            $conn->begin_transaction();
            try {
                $program_course_query = $conn->prepare("SELECT program_course_id FROM rw_bank_program_course WHERE course_id = ?");
                $program_course_query->bind_param("i", $course_id);
                $program_course_query->execute();
                $program_course_result = $program_course_query->get_result();
                
                if ($program_course_result->num_rows == 0) {
                    throw new Exception('Course not found in any program');
                }
                
                $program_course_row = $program_course_result->fetch_assoc();
                $program_course_id = $program_course_row['program_course_id'];
                
                $check_query = $conn->prepare("SELECT topic_id FROM rw_bank_topic WHERE topic_name = ? AND program_course_id = ?");
                $check_query->bind_param("si", $topic_name, $program_course_id);
                $check_query->execute();
                
                if ($check_query->get_result()->num_rows > 0) {
                    throw new Exception('This topic already exists in this course');
                }
                
                $insert_topic = $conn->prepare("INSERT INTO rw_bank_topic (topic_name, program_course_id, no_of_questions) VALUES (?, ?, 0)");
                $insert_topic->bind_param("si", $topic_name, $program_course_id);
                $insert_topic->execute();
                
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'Topic added successfully';
                $response['topic_id'] = $conn->insert_id;
                
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

<style>
/* Popup overlay */
.popup-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

/* Popup box */
.popup-content {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    width: 420px;
    max-width: 95%;
    position: relative;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    gap: 18px;
}

/* Close button */
.popup-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    font-size: 24px;
    color: #555;
    cursor: pointer;
}
.popup-close:hover {
    color: #000;
}

/* Title */
.popup-title {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
    color: #1E1A43;
}

/* Form inputs */
.popup-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.popup-input {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
}

/* Submit button */
.secondary-button {
    background: #413E81;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    cursor: pointer;
    font-size: 14px;
}
.secondary-button:hover {
    background: #333274;
}
</style>

<!-- Add Topic Popup -->
<div id="topic-popup-overlay" class="popup-overlay">
    <div class="popup-content">
        <button class="popup-close" onclick="closeTopicPopup()">&times;</button>
        <h2 class="popup-title">Add New Topic</h2>

        <form id="topic-form" class="popup-form">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" required class="popup-input">
                    <option value="">Select course</option>
                    <?php
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
            closeTopicPopup(); 
            this.reset(); 

            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Add new topic dynamically or reload
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
