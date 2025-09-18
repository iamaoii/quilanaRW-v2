<?php
include('db_connect.php');

if (isset($_GET['class_id'])) {
    $class_id = $conn->real_escape_string($_GET['class_id']);

    // Fetch the class details
    $qry_class = $conn->query("SELECT class_name, course_name FROM class WHERE class_id = '$class_id'");
    if ($qry_class->num_rows > 0) {
        $class = $qry_class->fetch_assoc();
        echo "<h4><strong>{$class['class_name']} ({$class['course_name']})</strong></h4>";
    } else {
        echo "<p><strong>Class not found.</strong></p>";
    }

    // Fetch the assessments with related details
    $qry_assessments = $conn->query("
        SELECT a.assessment_id, a.assessment_name, aa.date_administered, 
                CASE 
                    WHEN a.assessment_mode IN (1, 2) THEN SUM(q.total_points)
                    WHEN a.assessment_mode = 3 THEN COUNT(q.question_id) * a.max_points
                    ELSE 0
                END AS total_points,
                CASE 
                    WHEN au.upload_id IS NOT NULL THEN 1 
                    ELSE 0 
                END AS is_uploaded
        FROM administer_assessment aa
        JOIN assessment a ON aa.assessment_id = a.assessment_id
        JOIN questions q ON q.assessment_id = a.assessment_id
        LEFT JOIN assessment_uploads au ON a.assessment_id = au.assessment_id AND aa.class_id = au.class_id
        WHERE aa.class_id = '$class_id'
        GROUP BY a.assessment_id, a.assessment_name, aa.date_administered
    ");

    if (!$qry_assessments) {
        die("Error: " . $conn->error);
    }

    // Fetch the students with concatenated name and enrollment status
    $qry_student = $conn->query("
        SELECT s.student_id, s.student_number, CONCAT(s.lastname, ', ', s.firstname) AS student_name, se.status
        FROM student_enrollment se
        JOIN student s ON se.student_id = s.student_id
        WHERE se.class_id = '$class_id' AND se.status = 1
        ORDER BY student_name ASC
    ");

    if (!$qry_student) {
        die("Error: " . $conn->error);
    }
}
?>

<!-- Tab navigation -->
<div class="tabs-container">
    <ul class="tabs">
        <li class="tab-link active" onclick="openTab(event, 'Assessments')">Assessments</li>
        <li class="tab-link" onclick="openTab(event, 'Students')">Students</li>
        <li class="tab-link" id="studentScoresTab" style="display: none;" onclick="openTab(event, 'StudentScores')">Scores</li>
    </ul>
</div>

<!-- Tab content for Assessments -->
<div id="Assessments" class="tabcontent">
    <?php
    if (isset($qry_assessments)) {
        echo '<div class="table-wrapper">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Assessment Name</th>
                            <th>Date Administered</th>
                            <th>Total Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

                    if ($qry_assessments->num_rows > 0) {
                        while ($assessment = $qry_assessments->fetch_assoc()) {
                            echo '<tr>
                                    <td>' . htmlspecialchars($assessment['assessment_name']) . '</td>
                                    <td>' . htmlspecialchars($assessment['date_administered']) . '</td>
                                    <td>' . htmlspecialchars($assessment['total_points']) . '</td>
                                    <td>
                                        <div class="btn-container">
                                            <a href="view_assessment.php?id=' . htmlspecialchars($assessment['assessment_id']) . '&class_id=' . htmlspecialchars($class_id) . '" class="btn btn-primary btn-sm btn-view">View</a>
                                            <button class="btn ' . ($assessment['is_uploaded'] ? 'btn-danger' : 'btn-success') . ' btn-sm upload-btn" 
                                                    onclick="toggleUpload(' . htmlspecialchars($assessment['assessment_id']) . ', ' . htmlspecialchars($class_id) . ', ' . $assessment['is_uploaded'] . ')">
                                                ' . ($assessment['is_uploaded'] ? 'Undo Upload' : 'Upload') . '
                                            </button>
                                        </div>
                                    </td>
                                  </tr>';
                        }
                    } else {
                        echo '<tr>
                                <td colspan="4" class="text-center">No assessments found.</td>
                            </tr>';
                    }
            
                    echo '</tbody></table></div>';
                }
                ?>
</div>

<!-- Tab content for Students -->
<div id="Students" class="tabcontent" style="display: none;">
    <div class="table-wrapper">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>Student Name</th>
                    <th class="status">Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($qry_student) && $qry_student->num_rows > 0) {
                    while ($student = $qry_student->fetch_assoc()) {
                        echo '<tr>
                                <td>' . htmlspecialchars($student['student_number']) . '</td>
                                <td>' . htmlspecialchars($student['student_name']) . '</td>
                                <td class="status">' . (($student['status'] == 0) ? 'Pending' : 'Enrolled') . '</td>
                                <td>';
                        
                        echo '<div class="btn-container">
                                <button class="btn btn-primary btn-sm equal-size" 
                                    onclick="showStudentScores(' . $student['student_id'] . ', \'' . $student['student_name'] . '\')" 
                                    type="button">Scores
                                </button>     
                            </div>';
                        echo '</td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="4" class="text-center">No students found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tab content for Student Scores -->
<div id="StudentScores" class="tabcontent" style="display: none;">
    <div class="table-wrapper">
        <h6 id="studentScoresTitle"></h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Assessment Name</th>
                    <th>Score</th>
                    <th>Total Score</th>
                    <th>Date Taken</th>
                </tr>
            </thead>
            <tbody id="studentScoresBody">
                <!-- Scores will be dynamically populated here -->
            </tbody>
        </table>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    
    // Hide all tab contents
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
        tabcontent[i].classList.remove("active");
    }
    
    // Remove the 'active' class from all tab links
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }

    // Display the selected tab content
    document.getElementById(tabName).style.display = "block";
    document.getElementById(tabName).classList.add("active");
    
    // Add the 'active' class to the clicked tab link
    evt.currentTarget.classList.add("active");

    // Hide the 'Scores' tab if another tab is clicked
    if (tabName !== 'StudentScores') {
        document.getElementById('studentScoresTab').style.display = 'none';
    }
}


function showStudentScores(studentId, studentName) {
    fetch(`get_student_scores.php?student_id=${studentId}&class_id=<?php echo $class_id; ?>`)
        .then(response => response.json())
        .then(data => {
            const scoresContainer = document.getElementById('StudentScores');
            const scoresTitle = document.getElementById('studentScoresTitle');
            const scoresBody = document.getElementById('studentScoresBody');
            
            scoresTitle.textContent = ` ${studentName}`;
            scoresBody.innerHTML = ''; 
            
            // Check if data has scores
            if (data.length > 0) {
                data.forEach(score => {
                    scoresBody.innerHTML += `
                        <tr>
                            <td>${score.assessment_name}</td>
                            <td>${score.score}</td>
                            <td>${score.total_score}</td>
                            <td>${score.date_updated}</td>
                        </tr>
                    `;
                });
            } else {
                scoresBody.innerHTML = '<tr><td colspan="4" class="text-center">No scores found for this student.</td></tr>';
            }
            
            document.getElementById('studentScoresTab').style.display = 'block'; 
            openTab({currentTarget: document.getElementById('studentScoresTab')}, 'StudentScores');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching student scores.');
        });
}

document.addEventListener('DOMContentLoaded', function() {
    var defaultTab = document.querySelector('.tab-link.active');
    if (defaultTab) {
        var defaultTabName = defaultTab.getAttribute('onclick').match(/'(.*?)'/)[1];
        document.getElementById(defaultTabName).style.display = "block";
        document.getElementById(defaultTabName).classList.add("active");
    }
});

function toggleUpload(assessmentId, classId, isUploaded) {
    const action = isUploaded ? 'remove' : 'upload';
    const confirmMessage = isUploaded 
        ? "Are you sure you want to remove this assessment upload?" 
        : "Are you sure you want to upload this assessment?";

    if (confirm(confirmMessage)) {
        fetch('assessment_upload.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `assessment_id=${assessmentId}&class_id=${classId}&action=${action}`
        })
        .then(response => response.json())
        .then data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Reload the page to reflect the changes
            } else {
                alert("Failed to " + action + " assessment: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while " + action + "ing the assessment");
        });
    }
}

</script>

<?php $conn->close(); ?>
