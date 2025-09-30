<?php 
include 'db_connect.php'; 
include 'auth.php'; 

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

// Handle program update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    // Debug: Log the received data
    error_log("Edit Program - Received POST data: " . print_r($_POST, true));
    
    if (isset($_POST['program_id']) && isset($_POST['program_name'])) {
        $program_id = $_POST['program_id'];
        $program_name = trim($_POST['program_name']);
        $created_by = $_SESSION['login_id'];
        
        if (empty($program_name)) {
            $response['success'] = false;
            $response['message'] = 'Program name is required';
        } else {
            // Check if the program exists and belongs to the user
            $check_query = $conn->prepare("SELECT COUNT(*) as count FROM rw_bank_program WHERE program_id = ? AND created_by = ?");
            $check_query->bind_param("ii", $program_id, $created_by);
            $check_query->execute();
            $result = $check_query->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                // Check if the new name already exists for other programs
                $name_check = $conn->prepare("SELECT COUNT(*) as count FROM rw_bank_program WHERE program_name = ? AND created_by = ? AND program_id != ?");
                $name_check->bind_param("sii", $program_name, $created_by, $program_id);
                $name_check->execute();
                $name_result = $name_check->get_result();
                $name_row = $name_result->fetch_assoc();

                if ($name_row['count'] > 0) {
                    $response['success'] = false;
                    $response['message'] = 'A program with this name already exists';
                } else {
                    // Update the program
                    $update_query = $conn->prepare("UPDATE rw_bank_program SET program_name = ? WHERE program_id = ? AND created_by = ?");
                    $update_query->bind_param("sii", $program_name, $program_id, $created_by);
                    
                    if ($update_query->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Program updated successfully';
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Failed to update program';
                    }
                    $update_query->close();
                }
                $name_check->close();
            } else {
                $response['success'] = false;
                $response['message'] = 'Program not found or access denied';
            }
            $check_query->close();
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Missing required parameters';
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    error_log("Edit Program - Sending response: " . json_encode($response));
    echo json_encode($response);
    exit();
}

// Get program details if ID is provided
$program_name = '';
$program_id = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $created_by = $_SESSION['login_id'];
    
    $stmt = $conn->prepare("SELECT * FROM rw_bank_program WHERE program_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $id, $created_by);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $program_name = $row['program_name'];
        $program_id = $row['program_id'];
    }
    $stmt->close();
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Program | Quilana</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        #program-edit-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center; 
            justify-content: center; 
            z-index: 1000;
        }

        #program-edit-content {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
        }

        #program-edit-title {
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        .program-edit-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 22px;
            background: none;
            border: none;
            cursor: pointer;
        }
        .program-edit-close:hover {
            color: #555;
            background-color: #f0f0f0;
        }
        #program-edit-content .modal-body {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .modal-footer {
            border-top: none !important;
            box-shadow: none !important;
            margin-top: 20px;
            padding: 0;
            background: transparent;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            gap: 10px;
        }
        .modal-footer .secondary-button {
            flex: 1;
            padding: 10px 0;
            font-size: 16px;
            border-radius: 10px;
            text-align: center;
            margin: 0;
        }

        .secondary-button {
            background-image: linear-gradient(to right, #8794F2, #6E72C1);
            background-color: #4A4CA6;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            z-index: 2;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .secondary-button:hover {
            background-color: #4A4CA6;
            background-image: none;
            cursor: pointer;
            box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.25);
        }
        .secondary-button:active {
            outline: none;
            transform: scale(1.02);
            transition: transform 0.1s ease;
        }

        .cancel-button {
            background: transparent;
            color: #4A4CA6;
            border: 2px solid #4A4CA6;
        }
        .cancel-button:hover {
            background: #4A4CA6;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Program Edit Form -->
    <div id="program-edit-overlay">
        <div id="program-edit-content" role="document">
            <button class="program-edit-close" onclick="window.location.href='databank.php'">&times;</button>
            <h2 id="program-edit-title">Edit Program</h2>

            <form id="program-edit-form" class="popup-form">
                <div class="modal-body">
                    <div id="program-msg"></div>
                    <div class="form-group">
                        <label>Program Name</label>
                        <input type="text" name="program_name" required="required" class="popup-input" value="<?php echo htmlspecialchars($program_name); ?>" />
                        <input type="hidden" name="program_id" value="<?php echo htmlspecialchars($program_id); ?>" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="secondary-button cancel-button" onclick="window.location.href='databank.php'">Cancel</button>
                    <button type="submit" class="secondary-button" name="save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Program edit form submit
        const programEditForm = document.getElementById('program-edit-form');
        programEditForm.addEventListener('submit', function(e) {
            e.preventDefault();

            let programName = programEditForm.querySelector('[name="program_name"]').value.trim();
            let programId = programEditForm.querySelector('[name="program_id"]').value;

            if (programName === "") {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a program name',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
                return;
            }

            // Send AJAX request to update program
            const formData = new FormData();
            formData.append('program_name', programName);
            formData.append('program_id', programId);

            fetch('databank_edit_program.php', {
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
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal-btn'
                        }
                    }).then(() => {
                        window.location.href = 'databank.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update program',
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
    </script>
</body>
</html>
