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

// Handle AJAX requests for adding programs only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    if (isset($_POST['program_name'])) {
        $program_name = trim($_POST['program_name']);
        $created_by = $_SESSION['login_id'];

        if (empty($program_name)) {
            $response['success'] = false;
            $response['message'] = 'Program name is required';
        } else {
            try {
                // Check if program name already exists for this user
                $check_query = $conn->prepare("SELECT COUNT(*) as count FROM rw_bank_program WHERE program_name = ? AND created_by = ?");
                $check_query->bind_param("si", $program_name, $created_by);
                $check_query->execute();
                $result = $check_query->get_result();
                $row = $result->fetch_assoc();

                if ($row['count'] > 0) {
                    $response['success'] = false;
                    $response['message'] = 'A program with this name already exists';
                } else {
                    // Insert new program
                    $stmt = $conn->prepare("INSERT INTO rw_bank_program (program_name, created_by) VALUES (?, ?)");
                    $stmt->bind_param("si", $program_name, $created_by);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Program added successfully';
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Failed to save program';
                    }
                    $stmt->close();
                }
                $check_query->close();
            } catch (Exception $e) {
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            }
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Program name is required';
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

?>

<!-- Program Add Popup -->
<div id="program-popup-overlay">
    <div id="program-popup-content" role="document">
        <button class="program-popup-close">&times;</button>
        <h2 id="program-popup-title">Add New Program</h2>

        <form id="program-form" class="popup-form">
            <div class="modal-body">
                <div id="program-msg"></div>
                <div class="form-group">
                    <label>Program Name</label>
                    <input type="text" name="program_name" required="required" class="popup-input" />
                </div>
            </div>
            <div class="modal-footer">
                <button id="program-save-btn" type="submit" class="secondary-button">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const programPopup = document.getElementById('program-popup-overlay');
        const programForm = document.getElementById('program-form');
        const addProgramBtn = document.getElementById('open-add-program');

        // Open modal on add button click
        if (addProgramBtn && programPopup) {
            addProgramBtn.addEventListener('click', (e) => {
                e.preventDefault();
                programPopup.style.display = 'flex';
            });
        }

        // Close on X button click
        const programCloseBtn = programPopup ? programPopup.querySelector('.program-popup-close') : null;
        if (programCloseBtn && programForm && programPopup) {
            programCloseBtn.addEventListener('click', () => {
                programPopup.style.display = 'none';
                programForm.reset();
            });
        }

        // Close on overlay click
        if (programPopup) {
            programPopup.addEventListener('click', (e) => {
                if (e.target === programPopup) {
                    programPopup.style.display = 'none';
                    programForm.reset();
                }
            });
        }

        // Handle form submission
        if (programForm) {
            programForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let programName = this.querySelector('[name="program_name"]').value.trim();
                
                if (programName === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter a program name'
                    });
                    return;
                }

                const formData = new FormData(this);
                
                fetch('databank_add.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close popup
                        programPopup.style.display = 'none';
                        
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

                        // Reset form
                        this.reset();
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
        }
    });
</script>