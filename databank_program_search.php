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

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

if (!$user_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

// Verify user
if ($user_id !== $_SESSION['login_id']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
    exit();
}

// Build query for programs
$where = '';
$types = 'i';
$params = [$user_id];
if ($search) {
    $where = ' AND program_name LIKE ?';
    $search_term = "%$search%";
    $params[] = $search_term;
    $types .= 's';
}

$query = "SELECT * FROM rw_bank_program WHERE created_by = ? $where ORDER BY program_name ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$programs = $stmt->get_result();

ob_start();

if ($programs->num_rows > 0) {
    while ($row = $programs->fetch_assoc()) {
?>
<div class="program-card" data-program-id="<?php echo htmlspecialchars($row['program_id']); ?>">
    <p class="program-name"><?php echo htmlspecialchars($row['program_name']); ?></p>
    <div class="meatball-menu-container">
        <button class="meatball-menu-btn">
            <i class="fas fa-ellipsis-v"></i>
        </button>
        <div class="meatball-menu">
            <a href="#" class="edit" data-program-id="<?php echo $row['program_id']; ?>" data-program-name="<?php echo htmlspecialchars($row['program_name']); ?>">
                <i class="fas fa-pen"></i> Edit
            </a>
            <a href="#" class="delete" data-program-id="<?php echo $row['program_id']; ?>">
                <i class="fas fa-trash"></i> Delete
            </a>
        </div>
    </div>
    <a href="databank_course.php?id=<?php echo $row['program_id']; ?>" class="view-details-btn">View Details</a>
</div>
<?php
    }
} else {
    echo '<p class="no-programs-yet">No programs found</p>';
}

$html = ob_get_clean();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'html' => $html]);
?>