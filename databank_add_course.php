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
            // Start transaction
            $conn->begin_transaction();
            try {
                // First check if course exists
                $check_query = $conn->prepare("SELECT course_id FROM rw_bank_course WHERE course_name = ? AND created_by = ?");
                $check_query->bind_param("si", $course_name, $created_by);
                $check_query->execute();
                $result = $check_query->get_result();
                
                if ($result->num_rows > 0) {
                    // Course already exists
                    $course = $result->fetch_assoc();
                    $course_id = $course['course_id'];
                    
                    // Check if course is already in this program
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
                
                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'Course added successfully';
                $response['course_id'] = $course_id;
                
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

<!-- Course Popup -->
<div id="course-popup-overlay">
    <div id="course-popup-content" role="document">
        <button class="course-popup-close">&times;</button>
        <h2 id="course-popup-title">Add Course</h2>

        <form id="course-form" class="popup-form">
            <div class="modal-body">
                <div id="course-msg"></div>
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" required="required" class="popup-input" />
                </div>
                <input type="hidden" name="program_id" value="<?php echo isset($program_id) ? htmlspecialchars($program_id) : ''; ?>" />
            </div>
            <div class="modal-footer">
                <button id="course-save-btn" type="submit" class="secondary-button" name="save">Save</button>
            </div>
        </form>
    </div>
</div>
    </div>
</div>

<style>
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

    #course-popup-content {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        position: relative;
    }

    #course-popup-title {
        margin-bottom: 20px;
        font-size: 22px;
        font-weight: bold;
        text-align: center;
    }

    .course-popup-close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 22px;
        background: none;
        border: none;
        cursor: pointer;
    }

    .course-popup-close:hover {
        color: #555;
        background-color: #f0f0f0;
    }

    #course-popup-content .modal-body {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .popup-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgb(59, 39, 110);
        border-radius: 12px;
        font-size: 15px;
        outline: none;
    }

    .popup-input:hover {
        border-color: rgb(90, 70, 150);
        outline: 1.5px solid rgba(90, 70, 150, 0.4);
        box-shadow: 0 0 10px rgba(126, 87, 194, 0.6);
    }

    .popup-input:focus {
        border-color: #7e57c2;
        box-shadow: 0 0 10px rgba(126, 87, 194, 0.6);
    }
        
    .modal-footer {
        border-top: none !important;
        box-shadow: none !important;
        margin-top: 20px;
        padding: 0;
        background: transparent;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .modal-footer .secondary-button {
        width: 80%;
        max-width: 320px;
        padding: 10px 0;
        font-size: 16px;
        border-radius: 10px;
        text-align: center;
        margin: 0 auto;
        display: block;
    }

    .secondary-button {
        background-image: linear-gradient(to right, #8794F2, #6E72C1);
        background-color: #4A4CA6;
        color: white;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        z-index: 2;
    }

    .secondary-button:hover {
        background-color: #4A4CA6;
        background-image: none;
        cursor: pointer;
    }
</style>