<?php 
include 'db_connect.php'; 
include 'auth.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessments | Quilana</title>
    <link rel="stylesheet" href="assets/css/styles.css"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
         body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff;
            color: #1E1A43;
        }
        .databank-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 35px;
            flex-wrap: wrap;
        }
    </style>    
</head>
<?php include('nav_bar.php'); ?>

<body>
    <div class="content-wrapper">
        <div class="databank-program-wrapper">
        <!-- Search + Button -->
        <div class="databank-controls">
            <div class="long-search-bar">
                <input type="text" placeholder="Search assessments" id="assessment-search-input" class="databank-search">
                <button id="assessment-search-btn"><i class="fas fa-search"></i></button>
            </div>
            <button id="open-add-assessment" class="add-program-btn"><i class="fas fa-plus"></i> Add Assessment</button>
        </div>

        <!-- Header -->
        <h2 class="programs-header">Assessments</h2>

        <!-- Assessment Cards -->
            <div class="program-container" id="assessment-container">
                <?php 
                $qry = $conn->query("SELECT * FROM rw_bank_assessment WHERE created_by = '".$_SESSION['login_id']."' ORDER BY assessment_title ASC"); 
                if ($qry->num_rows > 0) { 
                    while ($row = $qry->fetch_assoc()) { 
                ?>
                    <div class="program-card" data-program-id="<?php echo htmlspecialchars($row['assessment_id']); ?>">
                        <p class="program-name"><?php echo htmlspecialchars($row['assessment_title']); ?></p>
                        
                        <!-- Actions -->
                        <div class="meatball-menu-container">
                            <button class="meatball-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="meatball-menu">
                                <a href="#" class="edit" data-assessment-id="<?php echo $row['assessment_id']; ?>" data-assessment-name="<?php echo htmlspecialchars($row['assessment_title']); ?>"><i class="fas fa-pen"></i> Edit</a>
                                <a href="#" class="delete" data-assessment-id="<?php echo $row['assessment_id']; ?>"><i class="fas fa-trash"></i> Delete</a>
                            </div>
                        </div>
                        <a href="assessment_questions.php?assessment_id=<?php echo $row['assessment_id']; ?>" class="view-details-btn">View Details</a>
                    </div>
                <?php 
                    } 
                } else { 
                    echo '<p class="no-programs-yet">No assessment created.</p>'; 
                } 
                ?>
            </div>
        </div>
    </div>

    <!-- Assessment Edit Popup -->
    <div id="program-edit-overlay" class="popup-overlay" style="display: none;">
        <div class="popup-content" role="document">
            <button class="popup-close" id="edit-close-btn">&times;</button>
            <h2 class="popup-title">Edit Assessment</h2>
            <form id="program-edit-form" class="popup-form">
                <div class="modal-body">
                    <div id="program-edit-msg"></div>
                    <div class="form-group">
                        <label>Assessment Name</label>
                        <input type="text" name="assessment_title" id="edit_assessment_name" required class="popup-input" />
                        <input type="hidden" name="assessment_id" id="edit_assessment_id" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="secondary-button" name="save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

        <!-- Assessment Add Popup -->
    <div id="topic-add-overlay" class="popup-overlay">
        <div class="popup-content">
            <button class="popup-close">&times;</button>
            <h2 class="popup-title">Add Assessment</h2>
            <form id="topic-add-form" class="popup-form">
            <div class="form-group">
                <label>Assessment Type</label>
                <select name="assessment_type" id="assessment_type" class="popup-input" required>
                    <option value="">Select assessment type</option>
                    <option value="1">Normal</option>
                    <option value="2">Quiz Bee</option>
                    <option value="3">Speed</option>
                </select>
            </div>
                <div class="form-group">
                    <label>Assessment Name</label>
                    <input type="text" name="assessment_name" id="add_assessment_name" required class="popup-input" placeholder="Enter assessment name" />
                </div>
                <input type="hidden" name="program_id" value="<?php echo htmlspecialchars($assessment_id); ?>" />
                <div class="modal-footer">
                    <button type="submit" class="secondary-button">Add Assessment</button>
                </div>
            </form>
        </div>
    </div>

   <script>
document.addEventListener('DOMContentLoaded', () => {
    const userId = <?php echo json_encode($_SESSION['login_id']); ?>;

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
    attachMeatballMenuListeners();


    // ======== ADD ASSESSMENT POPUP ========
    const assessmentAddOverlay = document.getElementById('topic-add-overlay');
    const assessmentAddForm = document.getElementById('topic-add-form');
    const assessmentAddCloseBtn = assessmentAddOverlay.querySelector('.popup-close');
    const openAddAssessmentBtn = document.getElementById('open-add-assessment');

    openAddAssessmentBtn.addEventListener('click', () => {
        assessmentAddOverlay.style.display = 'flex';
    });

    assessmentAddCloseBtn.addEventListener('click', () => {
        assessmentAddOverlay.style.display = 'none';
        assessmentAddForm.reset();
    });

    assessmentAddOverlay.addEventListener('click', (e) => {
        if (e.target === assessmentAddOverlay) {
            assessmentAddOverlay.style.display = 'none';
            assessmentAddForm.reset();
        }
    });

    // Handle Add form submit
    assessmentAddForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const assessmentType = assessmentAddForm.querySelector('#assessment_type').value;
        const assessmentName = assessmentAddForm.querySelector('#add_assessment_name').value.trim();

        if (!assessmentType || !assessmentName) {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please fill in all fields' });
            return;
        }

        const formData = new FormData();
        formData.append('assessment_type', assessmentType);
        formData.append('assessment_name', assessmentName);

        fetch('assessment_add.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                assessmentAddOverlay.style.display = 'none';
                assessmentAddForm.reset();
                Swal.fire({ icon: 'success', title: 'Success!', text: data.message, showConfirmButton: false, timer: 1500 })
                .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to add assessment' });
            }
        })
        .catch(err => {
            console.error("Add error:", err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected error occurred' });
        });
    });


    // ======== EDIT/DELETE ========
    function attachAssessmentActionListeners() {
        document.querySelectorAll('.program-card').forEach(card => {
            const assessmentId = card.getAttribute('data-program-id');
            const assessmentName = card.querySelector('.program-name').textContent;

            // ===== Delete =====
            card.querySelector('.delete')?.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Delete "${assessmentName}"? This cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('assessment_id', assessmentId);

                        fetch('assessment_delete.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, showConfirmButton: false, timer: 1500 })
                                .then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Delete failed' });
                            }
                        })
                        .catch(err => {
                            console.error("Delete error:", err);
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected error occurred' });
                        });
                    }
                });
            });

            // ===== Edit =====
            card.querySelector('.edit')?.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const editOverlay = document.getElementById('program-edit-overlay');
                const editForm = document.getElementById('program-edit-form');

                editForm.querySelector('#edit_assessment_id').value = assessmentId;
                editForm.querySelector('#edit_assessment_name').value = assessmentName;

                editOverlay.style.display = 'flex';
                editForm.querySelector('#edit_assessment_name').focus();
            });
        });
    }


    // ======== EDIT FORM HANDLER ========
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

            const assessmentId = editForm.querySelector('#edit_assessment_id').value;
            const assessmentName = editForm.querySelector('#edit_assessment_name').value.trim();

            if (!assessmentName) {
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please enter a name' });
                return;
            }

            const formData = new FormData();
            formData.append('assessment_id', assessmentId);
            formData.append('assessment_title', assessmentName);

            fetch('assessment_edit.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    editOverlay.style.display = 'none';
                    editForm.reset();
                    Swal.fire({ icon: 'success', title: 'Updated!', text: data.message, showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Update failed' });
                }
            })
            .catch(err => {
                console.error("Edit error:", err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected error occurred' });
            });
        });
    }

        // ======== SEARCH FUNCTION ========
    const searchInput = document.getElementById('assessment-search-input');
    const searchBtn = document.getElementById('assessment-search-btn');
    const assessmentContainer = document.getElementById('assessment-container');

    function searchAssessments() {
        const query = searchInput.value.trim();

        fetch(`assessment_search.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                assessmentContainer.innerHTML = "";

                if (data.success && data.data.length > 0) {
                    data.data.forEach(row => {
                        const card = document.createElement("div");
                        card.className = "program-card";
                        card.setAttribute("data-program-id", row.assessment_id);
                        card.innerHTML = `
                            <p class="program-name">${row.assessment_title}</p>
                            <div class="meatball-menu-container">
                                <button class="meatball-menu-btn"><i class="fas fa-ellipsis-v"></i></button>
                                <div class="meatball-menu">
                                    <a href="#" class="edit" data-assessment-id="${row.assessment_id}" data-assessment-name="${row.assessment_title}"><i class="fas fa-pen"></i> Edit</a>
                                    <a href="#" class="delete" data-assessment-id="${row.assessment_id}"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </div>
                            <a href="assessment_questions.php?assessment_id=${row.assessment_id}" class="view-details-btn">View Details</a>
                        `;
                        assessmentContainer.appendChild(card);
                    });

                    attachMeatballMenuListeners();
                    attachAssessmentActionListeners();
                } else {
                    assessmentContainer.innerHTML = `<p class="no-programs-yet">No matching assessments.</p>`;
                }
            })
            .catch(err => {
                console.error("Search error:", err);
                assessmentContainer.innerHTML = `<p class="no-programs-yet">Error loading results</p>`;
            });
    }
    searchInput.addEventListener('input', searchAssessments);
    searchBtn.addEventListener('click', searchAssessments);

    attachAssessmentActionListeners();
});
</script>
</body>
</html>