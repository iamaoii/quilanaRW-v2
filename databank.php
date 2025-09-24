<?php 
include 'db_connect.php'; 
include 'auth.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
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
        /* Global */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff;
            color: #1E1A43;
        }

        /* Controls Row */
        .databank-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 35px;
            flex-wrap: wrap;
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

        /* Add Program Button */
        .add-program-btn {
            background: linear-gradient(90deg, #333274 0%, #413E81 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 18px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .add-program-btn:hover {
            background: #333274;
        }
        .add-program-btn i.fas.fa-plus {
            font-size: 12px;
        }

        /* Header */
        .programs-header {
            font-size: 30px;
            margin: 20px 35px;
            color: #1E1A43;
            font-weight: bold;
        }

        /* Program Cards */
        .program-container {
            display: flex;
            flex-wrap: wrap;
            gap: 29px;
            margin: 50px 35px;
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
            text-decoration: none;
            text-align: center;
        }
        .no-programs-yet {
            text-align: center;
            color: #999;
            font-style: italic;
            margin: 20px 0;
            width: 100%;
        }

        /* Meatball Menu */
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

        /* Popup Overlay */
        #program-edit-overlay {
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
        #program-edit-content {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            width: 400px;
            max-width: 90%;
            position: relative;
        }
        .program-popup-close {
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
        .popup-form .form-group input {
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

        /* Responsiveness */
        @media (max-width: 768px) {
            .databank-controls {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .long-search-bar {
                max-width: 100%;
            }
            .add-program-btn {
                width: 100%;
            }
        }
    </style>
</head>

<?php include('nav_bar.php'); ?>

<body>
    <div class="content-wrapper">
        <!-- Search + Button -->
        <div class="databank-controls">
            <div class="long-search-bar">
                <input type="text" placeholder="Search programs" id="program-search-input" class="databank-search">
                <button id="program-search-btn"><i class="fas fa-search"></i></button>
            </div>
            <button id="open-add-program" class="add-program-btn"><i class="fas fa-plus"></i> Add Program</button>
        </div>

        <!-- Header -->
        <h2 class="programs-header">Programs</h2>

        <!-- Program Cards -->
        <div class="program-container" id="program-container">
            <?php 
            $qry = $conn->query("SELECT * FROM rw_bank_program WHERE created_by = '".$_SESSION['login_id']."' ORDER BY program_name ASC"); 
            if ($qry->num_rows > 0) { 
                while ($row = $qry->fetch_assoc()) { 
            ?>
                <div class="program-card" data-program-id="<?php echo htmlspecialchars($row['program_id']); ?>">
                    <p class="program-name"><?php echo htmlspecialchars($row['program_name']); ?></p>
                    
                    <!-- Actions -->
                    <div class="meatball-menu-container">
                        <button class="meatball-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="meatball-menu">
                            <a href="#" class="edit" data-program-id="<?php echo $row['program_id']; ?>" data-program-name="<?php echo htmlspecialchars($row['program_name']); ?>"><i class="fas fa-pen"></i> Edit</a>
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
                        <input type="text" name="program_name" id="edit_program_name" required="required" class="popup-input" />
                        <input type="hidden" name="program_id" id="edit_program_id" />
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
        document.addEventListener('DOMContentLoaded', () => {
            const userId = <?php echo json_encode($_SESSION['login_id']); ?>;

            // ======== SEARCH PROGRAMS ========
            const searchInput = document.getElementById('program-search-input');
            const searchBtn = document.getElementById('program-search-btn');
            const programContainer = document.getElementById('program-container');

            function performSearch() {
                const searchTerm = searchInput.value.trim();

                const formData = new FormData();
                formData.append('search', searchTerm);
                formData.append('user_id', userId);

                fetch('databank_program_search.php', {
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
                        programContainer.innerHTML = data.html;
                        attachMeatballMenuListeners();
                        attachProgramActionListeners();
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'No Results',
                            text: data.message || 'No programs found',
                            confirmButtonText: 'OK',
                            customClass: { confirmButton: 'swal-btn' }
                        });
                        programContainer.innerHTML = '<p class="no-programs-yet">No programs found</p>';
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

            // ======== MEATBALL MENU ========
            function attachMeatballMenuListeners() {
                document.querySelectorAll('.meatball-menu-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
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

            // ======== PROGRAM ACTIONS ========
            function attachProgramActionListeners() {
                document.querySelectorAll('.program-card').forEach(programCard => {
                    // Delete Program
                    programCard.querySelector('.delete')?.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const programId = this.getAttribute('data-program-id');
                        const programName = programCard.querySelector('.program-name').textContent;

                        Swal.fire({
                            title: 'Are you sure?',
                            text: `You are about to delete "${programName}". This action cannot be undone!`,
                            icon: 'warning',
                            showCancelButton: true,
                            cancelButtonColor: '#3085d6',
                            confirmButtonColor: 'rgba(206, 98, 98, 1)',
                            confirmButtonText: 'Yes, delete it!',
                            customClass: { confirmButton: 'swal-btn' }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const formData = new FormData();
                                formData.append('program_id', programId);

                                fetch('databank_delete_program.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Deleted!',
                                            text: data.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        }).then(() => {
                                            performSearch();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: data.message || 'Failed to delete program',
                                            icon: 'error',
                                            confirmButtonText: 'OK',
                                            customClass: { confirmButton: 'swal-btn' }
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Delete error:', error);
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'An unexpected error occurred',
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        customClass: { confirmButton: 'swal-btn' }
                                    });
                                });
                            }
                        });
                    });

                    // Edit Program
                    programCard.querySelector('.edit')?.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const programId = this.getAttribute('data-program-id');
                        const programName = this.getAttribute('data-program-name');

                        const editOverlay = document.getElementById('program-edit-overlay');
                        const editForm = document.getElementById('program-edit-form');

                        if (editForm) {
                            editForm.querySelector('#edit_program_id').value = programId;
                            editForm.querySelector('#edit_program_name').value = programName;
                            editOverlay.style.display = 'flex';
                            editForm.querySelector('#edit_program_name').focus();
                        }
                    });
                });
            }

            // ======== EDIT PROGRAM ========
            const editOverlay = document.getElementById('program-edit-overlay');
            const editForm = document.getElementById('program-edit-form');
            const editCloseBtn = document.getElementById('edit-close-btn');

            if (editCloseBtn) {
                editCloseBtn.addEventListener('click', () => {
                    editOverlay.style.display = 'none';
                    editForm.reset();
                });
            }

            if (editOverlay) {
                editOverlay.addEventListener('click', (e) => {
                    if (e.target === editOverlay) {
                        editOverlay.style.display = 'none';
                        editForm.reset();
                    }
                });
            }

            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const programName = editForm.querySelector('#edit_program_name').value.trim();
                    const programId = editForm.querySelector('#edit_program_id').value;

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
                            editOverlay.style.display = 'none';
                            editForm.reset();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Program updated successfully',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                performSearch();
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
                        console.error('Edit error:', error);
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

            // Initial attachment of listeners
            attachMeatballMenuListeners();
            attachProgramActionListeners();
        });
    </script>
</body>
</html>