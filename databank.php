<?php 
include 'db_connect.php'; 
include 'auth.php'; 

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type' ])) {
    header("Location: login.php");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Databank | Quilana</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* BODY */
        body {
            overflow: hidden;
            margin: 0;
            height: 100vh;
            position: relative;
        }

        /* BUTTONS */
        .main-button {
            background-image: linear-gradient(to right, #6E72C1, #4A4CA6);
            background-color: #4A4CA6;
            color: white;
            border: none;
            font-weight: bold;
            box-shadow: none;
            z-index: 2;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .main-button:hover {
            background-color: #4A4CA6;
            background-image: none;
            cursor: pointer;
            box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.25);
        }
        .main-button:active {
            outline: none;
            transform: scale(1.02);
            transition: transform 0.1s ease;
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

        .tertiary-button {
            background-color: transparent;
            color: #4A4CA6;
            border: 2px solid #4A4CA6;
            border-radius: 5px;
            font-weight: bold;
            z-index: 2;
            transition: color 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }
        .tertiary-button:hover {
            background-color: #4A4CA6;
            border: 2px solid #4A4CA6;
            cursor: pointer;
            color: #FFFFFF;
            box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.25);
            text-decoration: none;
        }
        .tertiary-button:active {
            outline: none;
            transform: scale(1.02);
            transition: transform 0.1s ease;
        }

        .course-actions .main-button,
        .course-actions .tertiary-button,
        .assessment-actions .main-button,
        .assessment-actions .tertiary-button {
            width: 100%;
            flex-grow: 1;
        }

        /* HEADER STYLE */
        .long-search-bar {
            display: flex;
            align-items: center;
            border: 1px solid #3B276E;
            border-radius: 10px;
            color: rgba(115, 119, 145, 0.75);
            padding: 0 15px;
            width: 600px;
            min-height: 40px;
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

        /* CONTAINERS AND CARDS STYLES */
        .program-container {
            display: flex;
            flex-wrap: wrap;
            gap: 29px;
            margin: 30px 35px 20px;
        }
        .program-card {
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
        .program-name {
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
        }

        /* CONTENT WRAPPER */
        .content-wrapper {
            height: 100vh;
            position: relative;
        }

        /* CUSTOM ADJUSTMENTS */
        .databank-controls {
            display: flex;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 20px;
            justify-content: space-between;
            width: 100%;
        }
        .programs-header {
            font-size: 30px;
            color: #1E1A43;
            margin: 30px 35px 20px;
            font-weight: bolder;
        }
        .add-program-btn {
            background: linear-gradient(90deg, #333274 0%, #413E81 100%);
            color: #fff;
            border: none;
            border-radius: 15px;
            padding: 6px 18px;
            cursor: pointer;
            font-size: 18px;
            font-weight: normal;
        }
        .no-programs-yet {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            font-size: 24px;
            color: #1E1A43;
            width: 100%;
            margin: 0;
        }
        
        #program-popup-overlay, #program-edit-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            display: none;  /* hidden by default */
            align-items: center; 
            justify-content: center; 
            z-index: 1000;
        }

        #program-popup-content, #program-edit-content {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
        }

        #program-popup-title {
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        .program-popup-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 22px;
            background: none;
            border: none;
            cursor: pointer;
        }
        .program-popup-close:hover {
            color: #555;
            background-color: #f0f0f0;
        }
        #program-popup-content .modal-body {
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

        /* Meatball Menu Styles */
        .meatball-menu-container {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .meatball-menu-btn {
            background: none;
            border: none;
            font-size: 18px;
            color: #666;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .meatball-menu-btn:hover {
            background-color: #f0f0f0;
            color: #333;
        }

        .meatball-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 120px;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .meatball-menu-container.show .meatball-menu {
            display: block;
        }

        .meatball-menu a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .meatball-menu a:hover {
            background-color: #f8f9fa;
        }

        .meatball-menu a.edit {
            color: #007bff;
        }

        .meatball-menu a.delete {
            color: #dc3545;
        }

        .meatball-menu a i {
            margin-right: 8px;
            width: 14px;
        }
    </style>
</head>

<?php include('nav_bar.php'); ?>

<body>
    <div class="content-wrapper">
        <!-- Search + Button -->
        <div class="databank-controls">
            <div class="long-search-bar">
                <input type="text" placeholder="Search" class="databank-search">
                <button><i class="fas fa-search"></i></button>
            </div>
            <button id="open-add-program" class="add-program-btn">+ Add Program</button>
        </div>

        <!-- Header -->
        <h2 class="programs-header">Programs</h2>

        <!-- Program Cards -->
        <div class="program-container">
            <?php 
            $qry = $conn->query("SELECT * FROM rw_bank_program WHERE created_by = '".$_SESSION['login_id']."' ORDER BY program_name ASC"); 
            if ($qry->num_rows > 0) { 
                while ($row = $qry->fetch_assoc()) { 
            ?>
                <div class="program-card">
                    <p class="program-name"><?php echo htmlspecialchars($row['program_name']); ?></p>
                    
                    <!-- Actions -->
                    <div class="meatball-menu-container">
                        <button class="meatball-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="meatball-menu">
                            <a href="#" class="edit" data-program-id="<?php echo $row['program_id']; ?>"><i class="fas fa-pen"></i> Edit</a>
                            <a href="#" class="delete" data-program-id="<?php echo $row['program_id']; ?>"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                    </div>

                    <a href="databank_course.php?id=<?php echo $row['program_id']; ?>" class="view-details-btn">View Details</a>
                </div>
            <?php 
                } 
            } else { 
                echo '<p class="no-programs-yet">No programs yet</p>'; 
            } 
            ?>
        </div>
    </div>

    <!-- Program Edit Popup -->
    <div id="program-edit-overlay" style="display: none;">
        <div id="program-edit-content" role="document">
            <button class="program-popup-close" id="edit-close-btn">&times;</button>
            <h2 id="program-popup-title">Edit Program</h2>

            <form id="program-edit-form" class="popup-form">
                <div class="modal-body">
                    <div id="program-edit-msg"></div>
                    <div class="form-group">
                        <label>Program Name</label>
                        <input type="text" name="program_name" required="required" class="popup-input" />
                        <input type="hidden" name="program_id" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="secondary-button" name="save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <?php include('databank_add.php'); ?>

    <script>
        // Meatball menu toggle
        document.querySelectorAll('.meatball-menu-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.querySelectorAll('.meatball-menu-container').forEach(c => {
                    if (c !== btn.parentElement) c.classList.remove('show');
                });
                btn.parentElement.classList.toggle('show');
            });
        });

        // Close all menus when clicking elsewhere
        document.addEventListener('click', () => {
            document.querySelectorAll('.meatball-menu-container').forEach(c => c.classList.remove('show'));
        });

        // Handle delete button clicks
        document.querySelectorAll('.delete').forEach(deleteBtn => {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const programId = this.getAttribute('data-program-id');
                const programCard = this.closest('.program-card');
                const programName = programCard.querySelector('.program-name').textContent;
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${programName}". This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('program_id', programId);
                        
                        fetch('databank_delete_program.php', {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: formData
                        })
                        .then(response => {
                            console.log('Delete response status:', response.status);
                            return response.text(); // Get raw response first
                        })
                        .then(text => {
                            console.log('Delete response text:', text);
                            try {
                                const data = JSON.parse(text);
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message,
                                        icon: 'success'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Invalid response from server: ' + text,
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An unexpected error occurred: ' + error.message,
                                icon: 'error'
                            });
                        });
                    }
                });
            });
        });

        // Edit Program Functionality
        const editOverlay = document.getElementById('program-edit-overlay');
        const editForm = document.getElementById('program-edit-form');
        const editCloseBtn = document.getElementById('edit-close-btn');

        // Handle edit button clicks
        document.querySelectorAll('.edit').forEach(editBtn => {
            editBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const programId = this.getAttribute('data-program-id');
                const programCard = this.closest('.program-card');
                const programName = programCard.querySelector('.program-name').textContent;

                console.log('Edit clicked - Program ID:', programId, 'Program Name:', programName);
                console.log('Edit overlay element:', editOverlay);
                console.log('Edit form element:', editForm);

                // Populate the edit form
                if (editForm) {
                    editForm.querySelector('[name="program_id"]').value = programId;
                    editForm.querySelector('[name="program_name"]').value = programName;
                    console.log('Form populated successfully');
                } else {
                    console.error('Edit form not found!');
                }

                // Show the edit overlay
                if (editOverlay) {
                    editOverlay.style.display = 'flex';
                    console.log('Edit overlay should be visible now');
                } else {
                    console.error('Edit overlay not found!');
                }
            });
        });

        // Close edit overlay
        if (editCloseBtn) {
            editCloseBtn.addEventListener('click', () => {
                editOverlay.style.display = 'none';
            });
        }

        if (editOverlay) {
            editOverlay.addEventListener('click', (e) => {
                if (e.target === editOverlay) {
                    editOverlay.style.display = 'none';
                }
            });
        }

        // Handle edit form submission
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            let programName = editForm.querySelector('[name="program_name"]').value.trim();
            let programId = editForm.querySelector('[name="program_id"]').value;

            console.log('Edit form submitted - Program ID:', programId, 'Program Name:', programName);

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
                credentials: 'same-origin',
                body: formData
            })
            .then(response => {
                console.log('Edit response status:', response.status);
                return response.text(); // Get raw response first
            })
            .then(text => {
                console.log('Edit response text:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        // Close edit overlay
                        editOverlay.style.display = 'none';

                        // Update program name in the UI
                        const programCard = document.querySelector(`[data-program-id="${programId}"]`).closest('.program-card');
                        programCard.querySelector('.program-name').textContent = programName;

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Program updated successfully',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'swal-btn'
                            }
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
                } catch (e) {
                    console.error('JSON parse error:', e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Invalid response from server: ' + text,
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'swal-btn' }
                    });
                }
            })
            .catch(error => {
                console.error('Edit error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred: ' + error.message,
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'swal-btn' }
                });
            });
        });
        }
    </script>
</body>
</html>
