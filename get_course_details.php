<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="assets/css/custom-tables.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Course Details</title>
</head>
<body>
    <?php
    include('db_connect.php');

    if (isset($_GET['program_id'])) {
        $program_id = $conn->real_escape_string($_GET['program_id']);

        // Fetch the program details
        $qry_program = $conn->query("SELECT program_name FROM program WHERE program_id = '$program_id'");
        if ($qry_program->num_rows > 0) {
            $program = $qry_program->fetch_assoc();
            echo "<h4><strong>{$program['program_name']}</strong></h4>";
        } else {
            echo "<p><strong>Program not found.</strong></p>";
        }

        // Fetch the class details associated with the program
        $qry_class = $conn->query("SELECT * FROM class WHERE program_id = '$program_id' ORDER BY class_name");

        // Display the table
        echo '<div class="table-wrapper">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Course Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

        if ($qry_class->num_rows > 0) {
            while ($class = $qry_class->fetch_assoc()) {
                echo '<tr>
                        <td>' . $class['class_name'] . '</td>
                        <td>' . $class['course_name'] . '</td>
                        <td>
                            <div class="btn-container">
                                <button id="viewClassDetails" class="btn btn-primary btn-sm view_class_details action_vcd" data-id="' . $class['class_id'] . '" type="button">View Class</button>
                            </div>
                        </td>
                      </tr>';
            }
        } else {
            // Show an empty row if no class data is available
            echo '<tr>
                    <td colspan="5" class="text-center">No classes found.</td>
                </tr>';
        }

        echo '</tbody></table></div>';
    } else {
        echo "<p>No program ID provided.</p>";
    }

    $conn->close();
    ?>
</body>
</html>