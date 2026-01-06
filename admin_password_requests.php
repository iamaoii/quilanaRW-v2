<?php
include 'db_connect.php';
include 'auth.php';

// Check access: only allow a specific faculty account (e.g., main admin) to access this page
// Adjust the username or login_id below to match your admin account
if (
    !isset($_SESSION['login_user_type']) ||
    $_SESSION['login_user_type'] != 2 ||              // must be faculty
    !isset($_SESSION['login_username']) ||
    $_SESSION['login_username'] !== 'admin'           // only the 'admin' faculty user
) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Requests | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #1E1A43;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .table {
            margin-bottom: 0;
        }
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        .modal-header {
            background-color: #1E1A43;
            color: white;
        }
        .btn-close-white {
            filter: invert(1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-key"></i> Password Reset Requests</h4>
            </div>
            <div class="card-body">
                <div id="message-alert" style="display: none;"></div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Type</th>
                                <th>Username</th>
                                <th>Webmail</th>
                                <th>Date Requested</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requests-table">
                            <?php
                            $query = "SELECT prr.*, 
                                     CASE 
                                         WHEN prr.user_type = 2 THEN f.firstname 
                                         WHEN prr.user_type = 3 THEN s.firstname 
                                     END as firstname,
                                     CASE 
                                         WHEN prr.user_type = 2 THEN f.lastname 
                                         WHEN prr.user_type = 3 THEN s.lastname 
                                     END as lastname
                                     FROM password_reset_requests prr
                                     LEFT JOIN faculty f ON prr.user_type = 2 AND prr.user_id = f.faculty_id
                                     LEFT JOIN student s ON prr.user_type = 3 AND prr.user_id = s.student_id
                                     ORDER BY prr.date_requested DESC";
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $user_type_name = $row['user_type'] == 2 ? 'Faculty' : 'Student';
                                    $status_class = $row['status'] == 'pending' ? 'warning' : ($row['status'] == 'approved' ? 'success' : 'secondary');
                                    $status_text = ucfirst($row['status']);
                                    
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($row['request_id']) . '</td>';
                                    echo '<td>' . htmlspecialchars($user_type_name) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['webmail']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['date_requested']) . '</td>';
                                    echo '<td><span class="badge bg-' . $status_class . '">' . $status_text . '</span></td>';
                                    echo '<td>';
                                    if ($row['status'] == 'pending') {
                                        echo '<button class="btn btn-sm btn-success approve-btn" data-id="' . $row['request_id'] . '" data-user-type="' . $row['user_type'] . '" data-user-id="' . $row['user_id'] . '">';
                                        echo '<i class="fas fa-check"></i> Approve</button> ';
                                        echo '<button class="btn btn-sm btn-danger reject-btn" data-id="' . $row['request_id'] . '">';
                                        echo '<i class="fas fa-times"></i> Reject</button>';
                                    } else {
                                        echo '<span class="text-muted">Processed</span>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center">No password reset requests found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Password Reset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this password reset request? The user will be able to set their own new password after approval.</p>
                    <input type="hidden" id="approve_request_id" name="request_id">
                    <div id="approve-message" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirm-approve-btn">Approve Request</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Approve button click
            $(document).on('click', '.approve-btn', function() {
                const requestId = $(this).data('id');
                
                $('#approve_request_id').val(requestId);
                $('#approve-message').hide();
                $('#approveModal').modal('show');
            });

            // Reject button click
            $(document).on('click', '.reject-btn', function() {
                if (confirm('Are you sure you want to reject this password reset request?')) {
                    const requestId = $(this).data('id');
                    
                    $.ajax({
                        url: 'admin_approve_password.php',
                        method: 'POST',
                        data: {
                            action: 'reject',
                            request_id: requestId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage('Request rejected successfully.', 'success');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showMessage(response.message || 'Failed to reject request.', 'danger');
                            }
                        },
                        error: function() {
                            showMessage('An error occurred. Please try again.', 'danger');
                        }
                    });
                }
            });

            // Confirm approve button
            $('#confirm-approve-btn').click(function() {
                const formData = {
                    action: 'approve',
                    request_id: $('#approve_request_id').val()
                };
                
                const messageDiv = $('#approve-message');
                $(this).prop('disabled', true).text('Processing...');
                
                $.ajax({
                    url: 'admin_approve_password.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            messageDiv.html('<div class="alert alert-success">' + response.message + '</div>').show();
                            setTimeout(() => {
                                $('#approveModal').modal('hide');
                                location.reload();
                            }, 1500);
                        } else {
                            messageDiv.html('<div class="alert alert-danger">' + response.message + '</div>').show();
                            $('#confirm-approve-btn').prop('disabled', false).text('Approve Request');
                        }
                    },
                    error: function() {
                        messageDiv.html('<div class="alert alert-danger">An error occurred. Please try again.</div>').show();
                        $('#confirm-approve-btn').prop('disabled', false).text('Approve Request');
                    }
                });
            });

            function showMessage(message, type) {
                const alertDiv = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                $('#message-alert').html(alertDiv).show();
                setTimeout(() => $('#message-alert').fadeOut(), 5000);
            }
        });
    </script>
</body>
</html>

