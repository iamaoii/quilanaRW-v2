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
        <div class="databank-controls" style="justify-content: space-between;">
            <div class="long-search-bar" style="flex: 1 1 360px; max-width: 520px;">
                <input type="text" placeholder="Search assessments" id="assessment-search-input" class="databank-search">
                <button id="assessment-search-btn"><i class="fas fa-search"></i></button>
            </div>
            <div class="button-group" style="display: flex; align-items: center; gap: 12px;">
                <button id="open-add-assessment" class="add-program-btn"><i class="fas fa-plus"></i> Add Assessment</button>
                <button id="combine-assessments-btn" class="add-program-btn" style="background: #6366f1;"><i class="fas fa-layer-group"></i> Combine Assessments</button>
                <button id="continue-combine-btn" class="add-program-btn" style="background: #10b981; display: none;"><i class="fas fa-arrow-right"></i> Continue</button>
                <button id="cancel-combine-btn" class="add-program-btn" style="background: #ef4444; display: none;"><i class="fas fa-times"></i> Cancel</button>
            </div>
        </div>

        <!-- Header -->
        <h2 class="programs-header">Assessments</h2>

        <!-- Assessment Cards -->
            <div class="program-container" id="assessment-container">
                <?php 
                $qry = $conn->query("SELECT * FROM rw_bank_assessment WHERE created_by = '".$_SESSION['login_id']."' ORDER BY assessment_id DESC"); 
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

    <!-- Combine Assessments Modal -->
    <div id="combine-modal-overlay" class="popup-overlay" style="display: none;">
        <div class="popup-content" style="width: 98vw; height: 95vh; max-width: none; overflow: hidden; display: flex; flex-direction: column; padding: 16px;">
            <button class="popup-close" id="combine-close-btn">&times;</button>
            <h2 class="popup-title" style="margin: 0 0 8px 0;">Select Questions to Combine</h2>
            
            <div style="margin: 8px 0 12px 0;">
                <p style="color: #666; margin: 0 0 8px 0;">Selected Assessments: <span id="selected-assessments-names" style="font-weight: 600;"></span></p>
                
                <div style="margin-bottom: 8px;">
                    <input type="text" id="combine-search-questions" placeholder="Search questions..." 
                           style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="select-all-questions-combine" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="select-all-questions-combine" style="cursor: pointer; font-weight: 500; margin: 0;">Select All Questions</label>
                    <span id="selected-questions-count-combine" style="margin-left: auto; color: #666;"></span>
                </div>
            </div>
            
            <div id="combine-questions-container" style="flex: 1 1 auto; min-height: 0; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 8px;">
                <!-- Questions will be loaded here -->
            </div>
            
            <!-- Compressed details using collapsible section to maximize question space -->
            <details style="margin-top: 10px;" id="combine-details">
                <summary style="cursor: pointer; font-weight: 600; color: #1E1A43; margin-bottom: 8px;">Details</summary>
                <form id="combine-assessment-form" class="popup-form" style="margin-top: 8px;">
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label>New Assessment Name</label>
                        <input type="text" id="combined-assessment-name" required class="popup-input" placeholder="Enter name for combined assessment" />
                    </div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label>Assessment Type</label>
                        <select id="combined-assessment-type" class="popup-input" required>
                            <option value="">Select type</option>
                            <option value="1">Normal</option>
                            <option value="2">Quiz Bee</option>
                            <option value="3">Speed</option>
                        </select>
                    </div>
                    <div class="modal-footer" style="padding: 0; margin-top: 8px;">
                        <button type="submit" class="secondary-button">Create Combined Assessment</button>
                    </div>
                </form>
            </details>
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
    // Simple debounce utility
    function debounce(fn, delay) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(null, args), delay); };
    }

    const debouncedSearch = debounce(searchAssessments, 250);
    searchInput.addEventListener('input', debouncedSearch);
    searchBtn.addEventListener('click', searchAssessments);

    attachAssessmentActionListeners();

    // ======== COMBINE ASSESSMENTS FUNCTIONALITY ========
    let combineMode = false;
    let selectedAssessments = new Set();
    let selectedQuestions = new Set();

    const combineBtn = document.getElementById('combine-assessments-btn');
    const continueBtn = document.getElementById('continue-combine-btn');
    const cancelBtn = document.getElementById('cancel-combine-btn');
    const combineModal = document.getElementById('combine-modal-overlay');
    const combineCloseBtn = document.getElementById('combine-close-btn');
    const combineForm = document.getElementById('combine-assessment-form');

    // Start combine mode
    combineBtn.addEventListener('click', () => {
        combineMode = true;
        selectedAssessments.clear();
        toggleCombineMode();
    });

    // Cancel combine mode
    cancelBtn.addEventListener('click', () => {
        combineMode = false;
        selectedAssessments.clear();
        toggleCombineMode();
    });

    // Continue to question selection
    continueBtn.addEventListener('click', () => {
        if (selectedAssessments.size === 0) {
            Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one assessment' });
            return;
        }
        loadQuestionsFromAssessments();
        combineModal.style.display = 'flex';
    });

    // Toggle combine mode UI
    function toggleCombineMode() {
        const cards = document.querySelectorAll('.program-card');
        
        if (combineMode) {
            combineBtn.style.display = 'none';
            continueBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';
            
            cards.forEach(card => {
                card.style.cursor = 'pointer';
                card.style.border = '2px solid #e5e7eb';
                card.addEventListener('click', handleCardSelection);
            });
        } else {
            combineBtn.style.display = 'inline-block';
            continueBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            
            cards.forEach(card => {
                card.style.cursor = 'default';
                card.style.border = '';
                card.style.background = '';
                card.removeEventListener('click', handleCardSelection);
            });
        }
    }

    // Handle card selection in combine mode
    function handleCardSelection(e) {
        if (!combineMode) return;
        if (e.target.closest('.meatball-menu-btn') || e.target.closest('.view-details-btn')) return;
        
        const card = e.currentTarget;
        const assessmentId = card.dataset.programId;
        
        if (selectedAssessments.has(assessmentId)) {
            selectedAssessments.delete(assessmentId);
            card.style.background = '';
            card.style.border = '2px solid #e5e7eb';
        } else {
            selectedAssessments.add(assessmentId);
            card.style.background = '#dbeafe';
            card.style.border = '2px solid #3b82f6';
        }
        
        continueBtn.innerHTML = `<i class="fas fa-arrow-right"></i> Continue (${selectedAssessments.size})`;
    }

    // Load questions from selected assessments
    function loadQuestionsFromAssessments() {
        const assessmentIds = Array.from(selectedAssessments);
        const assessmentNames = Array.from(selectedAssessments).map(id => {
            const card = document.querySelector(`[data-program-id="${id}"]`);
            return card ? card.querySelector('.program-name').textContent : '';
        }).join(', ');
        
        document.getElementById('selected-assessments-names').textContent = assessmentNames;
        
        fetch('get_assessment_questions_combined.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ assessment_ids: assessmentIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayQuestionsForSelection(data.questions);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to load questions' });
            }
        })
        .catch(err => {
            console.error('Load questions error:', err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load questions' });
        });
    }

    // Display questions for selection
    function displayQuestionsForSelection(questions) {
        const container = document.getElementById('combine-questions-container');
        selectedQuestions.clear();
        
        if (questions.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #666;">No questions found in selected assessments.</p>';
            return;
        }
        
        container.innerHTML = questions.map((q, index) => `
            <div class="question-item" data-question-id="${q.question_id}" style="padding: 15px; border-bottom: 1px solid #e5e7eb; cursor: pointer;">
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <input type="checkbox" class="question-checkbox-combine" value="${q.question_id}" 
                           style="margin-top: 5px; width: 18px; height: 18px; cursor: pointer;">
                    <div style="flex: 1;">
                        <p style="font-weight: 600; margin: 0 0 5px 0;">Q${index + 1}: ${q.question_text}</p>
                        <small style="color: #666;">Type: ${q.type_name} | Difficulty: ${q.difficulty_name} | Points: ${q.total_points}</small>
                        <br><small style="color: #3b82f6;">From: ${q.assessment_title}</small>
                    </div>
                </div>
            </div>
        `).join('');
        
        // Add event listeners
        document.querySelectorAll('.question-checkbox-combine').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedQuestionsCount);
        });
        
        document.querySelectorAll('.question-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.classList.contains('question-checkbox-combine')) return;
                const checkbox = item.querySelector('.question-checkbox-combine');
                checkbox.checked = !checkbox.checked;
                updateSelectedQuestionsCount();
            });
        });
        
        updateSelectedQuestionsCount();
    }

    // Select all questions
    document.getElementById('select-all-questions-combine').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.question-checkbox-combine');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedQuestionsCount();
    });

    // Search questions in combine modal
    document.getElementById('combine-search-questions').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.question-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? 'block' : 'none';
        });
    });

    // Update selected count
    function updateSelectedQuestionsCount() {
        selectedQuestions.clear();
        document.querySelectorAll('.question-checkbox-combine:checked').forEach(cb => {
            selectedQuestions.add(cb.value);
        });
        document.getElementById('selected-questions-count-combine').textContent = 
            `${selectedQuestions.size} question(s) selected`;
    }

    // Close combine modal
    combineCloseBtn.addEventListener('click', () => {
        combineModal.style.display = 'none';
        combineForm.reset();
        selectedQuestions.clear();
    });

    // Submit combined assessment
    combineForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if (selectedQuestions.size === 0) {
            Swal.fire({ icon: 'warning', title: 'No Questions', text: 'Please select at least one question' });
            return;
        }
        
        const formData = new FormData();
        formData.append('assessment_name', document.getElementById('combined-assessment-name').value);
        formData.append('assessment_type', document.getElementById('combined-assessment-type').value);
        formData.append('question_ids', JSON.stringify(Array.from(selectedQuestions)));
        
        fetch('create_combined_assessment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                combineModal.style.display = 'none';
                combineMode = false;
                toggleCombineMode();
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Success!', 
                    text: 'Combined assessment created successfully', 
                    showConfirmButton: false, 
                    timer: 1500 
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to create assessment' });
            }
        })
        .catch(err => {
            console.error('Create combined error:', err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to create combined assessment' });
        });
    });
});
</script>
</body>
</html>