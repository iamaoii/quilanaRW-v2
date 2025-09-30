<?php
include 'db_connect.php';
include 'auth.php';

// Handle update when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id   = $_POST['topic_id'] ?? null;
    $topic_name = $_POST['topic_name'] ?? '';

    if ($topic_id && $topic_name) {
        // Checks for duplicate 
        $check = $conn->prepare("SELECT COUNT(*) FROM rw_bank_topic WHERE topic_name = ? AND topic_id != ?");
        $check->bind_param("si", $topic_name, $topic_id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo "duplicate";
            exit;
        }

        $stmt = $conn->prepare("UPDATE rw_bank_topic SET topic_name = ? WHERE topic_id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("si", $topic_name, $topic_id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "error: missing topic_id or topic_name";
    }
    exit; 
}
?>


<style>
#topic-edit-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

#topic-edit-content {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    position: relative;
}

#topic-edit-title {
    margin-bottom: 20px;
    font-size: 22px;
    font-weight: bold;
    text-align: center;
}

.topic-edit-close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 22px;
    background: none;
    border: none;
    cursor: pointer;
}

.modal-footer {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.popup-btn {
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 14px;
    cursor: pointer;
}

.save-btn { background: #413E81; color: #fff; }
.save-btn:hover { background: #333274; }

.cancel-btn { background: #999; color: #fff; }
.cancel-btn:hover { background: #777; }

.popup-label {
    font-size: 14px;
    font-weight: 500;
    color: #1E1A43;
    display: block;
    margin-bottom: 6px;
}

</style>

<!-- ========= HTML =========== -->
<div id="topic-edit-overlay">
    <div id="topic-edit-content">
        <button class="topic-edit-close" onclick="closeEditTopicPopup()">&times;</button>
        <h2 id="topic-edit-title">Edit Topic</h2>

        <form id="edit-topic-form" method="POST" action="databank_edit_topic.php">
            <input type="hidden" name="topic_id" id="edit_topic_id">

            <div class="modal-body">
                <label class="popup-label">Topic Name</label>
                <input type="text" class="popup-input" name="topic_name" id="edit_topic_name" required>
            </div>

            <div class="modal-footer">
                <button type="button" class="popup-btn cancel-btn" onclick="closeEditTopicPopup()">Cancel</button>
                <button type="submit" class="popup-btn save-btn">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ========= JavaScript =========== -->
<script>
function openEditTopicPopup(topic_id, topic_name) {
    document.getElementById('edit_topic_id').value = topic_id;
    document.getElementById('edit_topic_name').value = topic_name;
    document.getElementById('topic-edit-overlay').style.display = 'flex';
}

function closeEditTopicPopup() {
    document.getElementById('topic-edit-overlay').style.display = 'none';
}

// Handle form submit
document.getElementById('edit-topic-form').addEventListener('submit', function(e) {
    e.preventDefault(); //

    const formData = new FormData(this);

    fetch('databank_edit_topic.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            document.getElementById('topic-edit-overlay').style.display = 'none';

            Swal.fire({
                icon: "success",
                title: "Updated!",
                text: "Topic updated successfully!",
                allowOutsideClick: true,
                allowEscapeKey: true,
                showConfirmButton: true,
                confirmButtonText: "OK",
                customClass: {
                    confirmButton: 'swal-btn'
                }
            }).then((result) => {
                if (result.isConfirmed || result.isDismissed) {
                    location.reload();
                }
            });

        } else if (data.trim() === 'duplicate') {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Topic already exists.",
                confirmButtonText: "OK",
                customClass: {
                    confirmButton: 'swal-btn'
                }
            });

        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Could not update topic."
            });
        }
    })
    .catch(err => {
        console.error("Fetch error:", err);
        Swal.fire({
            icon: "error",
            title: "Unexpected Error",
            text: "Something went wrong while saving."
        });
    });
});

</script>
