<?php
include 'db_connect.php';
include 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_user_type'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

if (!$program_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid program ID']);
    exit();
}

// Verify program ownership
$created_by = $_SESSION['login_id'];
$stmt = $conn->prepare("SELECT * FROM rw_bank_program WHERE program_id = ? AND created_by = ?");
$stmt->bind_param("ii", $program_id, $created_by);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Program not found or unauthorized']);
    exit();
}

// Build query for courses (match course name or topic name)
$where = '';
$types = 'i';
$params = [$program_id];
if ($search) {
    $where = ' AND (c.course_name LIKE ? OR t.topic_name LIKE ?)';
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$query = "
SELECT DISTINCT c.course_id, c.course_name 
FROM rw_bank_course c 
INNER JOIN rw_bank_program_course pc ON c.course_id = pc.course_id 
LEFT JOIN rw_bank_topic t ON t.program_course_id = pc.program_course_id
WHERE pc.program_id = ? $where
ORDER BY c.course_name ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$courses = $stmt->get_result();

ob_start();

if ($courses->num_rows > 0) {
    while ($course = $courses->fetch_assoc()) {
        $course_id = $course['course_id'];
?>
<div class="course-item" data-course-id="<?php echo htmlspecialchars($course_id); ?>">
    <div class="course-header">
        <h3 class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></h3>
        <button class="course-toggle">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    <div class="course-content">
        <div class="course-details">
            <div class="meatball-menu-container">
                <button class="meatball-menu-btn">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="meatball-menu">
                    <a href="#" class="edit-course-btn" data-course-id="<?php echo $course_id; ?>" 
                       data-course-name="<?php echo htmlspecialchars($course['course_name']); ?>">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <a href="#" class="delete-course-btn" data-course-id="<?php echo $course_id; ?>">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
            <div class="topics-section">
                <h4>Topics</h4>
                <div class="topic-container">
                    <?php
                    $topics_query = "
                        SELECT t.*, pc.program_course_id 
                        FROM rw_bank_topic t 
                        INNER JOIN rw_bank_program_course pc ON t.program_course_id = pc.program_course_id 
                        WHERE pc.course_id = ? 
                        ORDER BY t.topic_name ASC
                    ";
                    $stmt_topics = $conn->prepare($topics_query);
                    $stmt_topics->bind_param("i", $course_id);
                    $stmt_topics->execute();
                    $topics = $stmt_topics->get_result();

                    if ($topics->num_rows > 0) {
                        while ($topic = $topics->fetch_assoc()) {
                    ?>
                    <div class="topic-card">
                        <p class="topic-name"><?php echo htmlspecialchars($topic['topic_name']); ?></p>
                        <div class="meatball-menu-container">
                            <button class="meatball-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="meatball-menu">
                                <a href="#" class="view" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="#" class="edit-topic-btn" data-topic-id="<?php echo $topic['topic_id']; ?>" 
                                   data-topic-name="<?php echo htmlspecialchars($topic['topic_name']); ?>">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="#" class="delete" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                        <a href="#" class="view-details-btn" data-topic-id="<?php echo $topic['topic_id']; ?>">View Details</a>
                    </div>
                    <?php
                        }
                    } else {
                        echo '<p class="no-topics">No topics added yet</p>';
                    }
                    $stmt_topics->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }
} else {
    echo '<p class="no-courses">No courses or topics found matching your search</p>';
}

$html = ob_get_clean();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'html' => $html]);
?>