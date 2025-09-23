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
            <button id="open-add-program" class="add-program-btn" ><i class="fas fa-plus"></i> Add Program</button>
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
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: 'rgba(206, 98, 98, 1)',
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
