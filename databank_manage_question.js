$(document).ready(function () {
  // Show/hide question type options
  $("#question_type").change(function () {
    $(".question-type-options").hide();
    const selectedType = $(this).val();

    if (selectedType === "1") {
      $("#multiple_choice_options").show();
    } else if (selectedType === "2") {
      $("#checkbox_options").show();
    } else if (selectedType === "3") {
      $("#true_false_options").show();
    } else if (selectedType === "4") {
      $("#identification_options").show();
    } else if (selectedType === "5") {
      $("#fill_blank_options").show();
    }
  });

  // Add multiple choice option
  $("#add_mc_option").click(function () {
    const optionCount = $("#mc_options .option-group").length;
    const newOption = `
      <div class="option-group d-flex align-items-center mb-2">
        <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
        <label><input type="radio" name="is_right" value="${optionCount}"> Correct</label>
        <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
      </div>
    `;
    $("#mc_options").append(newOption);
  });

  // Add checkbox option
  $("#add_cb_option").click(function () {
    const optionCount = $("#cb_options .option-group").length;
    const newOption = `
      <div class="option-group d-flex align-items-center mb-2">
        <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
        <label><input type="checkbox" name="is_right[]" value="${optionCount}"> Correct</label>
        <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
      </div>
    `;
    $("#cb_options").append(newOption);
  });

  // Remove option
  $(document).on("click", ".remove-option", function () {
    if ($(".option-group").length > 1) {
      $(this).closest(".option-group").remove();
    } else {
      alert("At least one option is required.");
    }
  });

  // Add question button click
  $("#add_item_btn").click(function () {
    $("#manage_question").modal("show");
    $("#question-frm")[0].reset();
    $(".question-type-options").hide();
    $("#manageQuestionLabel").text("Add New Question");
  });

  // Selection mode state
  let selectionMode = false;
  let selectedQuestions = new Set();

  // Select Question Button
  $("#select_question_btn").click(function () {
    selectionMode = !selectionMode;
    toggleSelectionMode();
  });

  // Add To Button
  $("#add_to_btn").click(function () {
    if (selectedQuestions.size > 0) {
      $("#selected_questions_count").text(selectedQuestions.size);
      $("#addToModal").modal("show");
    }
  });

  // Toggle selection mode
  function toggleSelectionMode() {
    if (selectionMode) {
      $("#select_question_btn").html(
        '<i class="fa fa-times"></i> Cancel Selection'
      );
      $("#select_question_btn")
        .removeClass("btn-primary")
        .addClass("btn-warning");
      $(".list-group-item").addClass("selectable").css("cursor", "pointer");

      // Add checkboxes to each question
      $(".list-group-item").each(function () {
        const questionId = $(this).find(".edit_question").data("id");
        if (!$(this).find(".question-checkbox").length) {
          $(this).prepend(`
            <div class="form-check question-checkbox">
              <input class="form-check-input" type="checkbox" value="${questionId}" id="question_${questionId}">
            </div>
          `);
        }
      });
    } else {
      $("#select_question_btn").html(
        '<i class="fa fa-list-check"></i> Select Question'
      );
      $("#select_question_btn")
        .removeClass("btn-warning")
        .addClass("btn-primary");
      $(".list-group-item")
        .removeClass("selectable selected")
        .css("cursor", "default");
      $(".question-checkbox").remove();
      selectedQuestions.clear();
      updateAddToButton();
    }
  }

  // Handle question selection
  $(document).on("change", ".question-checkbox input", function () {
    const questionId = $(this).val();
    const questionItem = $(this).closest(".list-group-item");

    if ($(this).is(":checked")) {
      selectedQuestions.add(questionId);
      questionItem.addClass("selected");
    } else {
      selectedQuestions.delete(questionId);
      questionItem.removeClass("selected");
    }

    updateAddToButton();
  });

  // Update Add To button state
  function updateAddToButton() {
    if (selectedQuestions.size > 0) {
      $("#add_to_btn").prop("disabled", false);
      $("#add_to_btn").html(
        `<i class="fa fa-folder-plus"></i> Add To... (${selectedQuestions.size})`
      );
    } else {
      $("#add_to_btn").prop("disabled", true);
      $("#add_to_btn").html('<i class="fa fa-folder-plus"></i> Add To...');
    }
  }

  // Handle click on question items in selection mode
  $(document).on("click", ".list-group-item.selectable", function (e) {
    if (!$(e.target).is("input, button, a, .btn")) {
      const checkbox = $(this).find(".question-checkbox input");
      checkbox.prop("checked", !checkbox.prop("checked"));
      checkbox.trigger("change");
    }
  });

  // Assessment selection handling
  $("#existing_assessment").change(function () {
    const assessmentSelected = $(this).val() !== "";
    $("#confirm_add_to").prop("disabled", !assessmentSelected);
  });

  // ------------------------
  // CREATE NEW ASSESSMENT FLOW
  // ------------------------
  $("#new_assessment_btn").click(function () {
    $("#newAssessmentModal").modal("show");
    $("#new_assessment_selected_count").text(selectedQuestions.size);
  });

  $(document).on("submit", "#new_assessment_form", function (e) {
    e.preventDefault();
    const title = $("#new_assessment_title").val().trim();
    const type = $("#new_assessment_type").val();

    if (!title || !type) {
      alert("Please enter assessment name and type.");
      return;
    }

    $("#new_assessment_submit").prop("disabled", true).text("Creating...");

    const formData = new FormData();
    formData.append("assessment_title", title);
    formData.append("assessment_type", type);

    fetch("databank_ajax_create_assessment.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const assessmentId = data.assessment_id;
          addSelectedQuestionsToAssessment(assessmentId, function (
            successAdd,
            message
          ) {
            $("#newAssessmentModal").modal("hide");
            if (successAdd) {
              alert("Assessment created and questions added successfully!");
              location.reload();
            } else {
              alert(
                "Assessment created but failed to add questions: " + message
              );
              location.reload();
            }
          });
        } else {
          alert("Error creating assessment: " + (data.message || "Unknown"));
        }
      })
      .catch((err) => {
        console.error("Create error:", err);
        alert("Network error occurred.");
      })
      .finally(() => {
        $("#new_assessment_submit")
          .prop("disabled", false)
          .text("Create & Add");
      });
  });

  // Central function to add questions
  function addSelectedQuestionsToAssessment(assessmentId, cb) {
    const questionIds = Array.from(selectedQuestions);
    if (!assessmentId || questionIds.length === 0) {
      cb(false, "No assessment or no selected questions.");
      return;
    }

    const params = new URLSearchParams();
    params.append("assessment_id", assessmentId);
    questionIds.forEach((qid) => params.append("question_ids[]", qid));

    fetch("databank_ajax_add_to_assessment.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params.toString(),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          cb(true, data.message || "Added");
        } else {
          cb(false, data.message || "Failed to add");
        }
      })
      .catch((err) => {
        console.error("Add to assess error:", err);
        cb(false, "Network error");
      });
  }

  // ------------------------
  // CONFIRM ADD TO (existing assessment)
  // ------------------------
  $("#confirm_add_to").click(function () {
    const assessmentId = $("#existing_assessment").val();
    if (!assessmentId) {
      alert("Please select an existing assessment or create a new one.");
      return;
    }

    addSelectedQuestionsToAssessment(assessmentId, function (success, message) {
      if (success) {
        alert(message);
        $("#addToModal").modal("hide");
        selectionMode = false;
        toggleSelectionMode();
        location.reload();
      } else {
        alert("Error: " + message);
      }
    });
  });

  // Reset Add To modal
  $("#addToModal").on("hidden.bs.modal", function () {
    $("#existing_assessment").val("");
    $("#confirm_add_to").prop("disabled", true);
  });

  // ------------------------
  // FORM SUBMIT (Add/Edit Question)
  // ------------------------
  $("#question-frm").submit(function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const questionType = $("#question_type").val();
    const questionId = $('input[name="id"]').val();
    const url = questionId
      ? "databank_ajax_update_question.php"
      : "databank_ajax_save_question.php";

    if (!questionType) {
      alert("Please select a question type");
      return;
    }

    if (!$("#question_text").val().trim()) {
      alert("Please enter question text");
      return;
    }

    $("#save_question_btn")
      .prop("disabled", true)
      .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          $("#manage_question").modal("hide");
          alert("Question saved successfully!");
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to save question"));
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Network error occurred");
      })
      .finally(() => {
        $("#save_question_btn").prop("disabled", false).html("Save Question");
      });
  });

  // ------------------------
  // DELETE Question
  // ------------------------
  $(document).on("click", ".remove_question", function () {
    const questionId = $(this).data("id");

    if (
      confirm(
        "Are you sure you want to delete this question? This action cannot be undone."
      )
    ) {
      $(this).html('<i class="fa fa-spinner fa-spin"></i>');
      $(this).prop("disabled", true);

      fetch("databank_ajax_delete_question.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "question_id=" + questionId,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Question deleted successfully!");
            location.reload();
          } else {
            alert("Error: " + (data.message || "Failed to delete question"));
            $(this).html('<i class="fa fa-trash"></i>');
            $(this).prop("disabled", false);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Network error occurred");
          $(this).html('<i class="fa fa-trash"></i>');
          $(this).prop("disabled", false);
        });
    }
  });

  // ------------------------
  // EDIT Question
  // ------------------------
  $(document).on("click", ".edit_question", function () {
    const questionId = $(this).data("id");

    fetch("databank_ajax_get_question.php?question_id=" + questionId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          $("#manageQuestionLabel").text("Edit Question");
          $('input[name="id"]').val(questionId);
          $("#question_type").val(data.question.question_type);
          $("#question_text").val(data.question.question_text);
          $("#difficulty").val(data.question.difficulty);

          $("#question_type").trigger("change");

          if (["1", "2", "3"].includes(data.question.question_type)) {
            populateOptions(data.options, data.question.question_type);
          } else {
            if (data.answer) {
              if (data.question.question_type === "4") {
                $("#identification_answer").val(data.answer.correct_answer);
              } else {
                $("#fill_blank_answer").val(data.answer.correct_answer);
              }
            }
          }

          $("#manage_question").modal("show");
        } else {
          alert("Error: " + (data.message || "Failed to load question data"));
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Network error occurred");
      });
  });

  // Function to populate options (for edit mode)
  function populateOptions(options, questionType) {
    $("#mc_options, #cb_options").empty();

    if (questionType === "3") {
      const correctAnswer = options.find((opt) => opt.is_correct == 1);
      if (correctAnswer) {
        if (correctAnswer.option_text === "True") {
          $('input[name="tf_answer"][value="true"]').prop("checked", true);
        } else {
          $('input[name="tf_answer"][value="false"]').prop("checked", true);
        }
      }
    } else {
      options.forEach((option, index) => {
        const optionHtml = `
          <div class="option-group d-flex align-items-center mb-2">
            <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text">${option.option_text}</textarea>
            <label>
              <input type="${questionType === "1" ? "radio" : "checkbox"}"
                name="${questionType === "1" ? "is_right" : "is_right[]"}"
                value="${index}"
                ${option.is_correct ? "checked" : ""}>
              Correct
            </label>
            <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
          </div>
        `;
        if (questionType === "1") {
          $("#mc_options").append(optionHtml);
        } else {
          $("#cb_options").append(optionHtml);
        }
      });
    }
  }

  // Reset modal when closed
  $("#manage_question").on("hidden.bs.modal", function () {
    $("#question-frm")[0].reset();
    $(".question-type-options").hide();
    $("#manageQuestionLabel").text("Add New Question");
    $('input[name="id"]').val("");
  });
});