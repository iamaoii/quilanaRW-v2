$(document).ready(function () {
  const $questionType = $("#question_type");
  const $questionText = $("#question_text");
  const $questionSearchInput = $("#question_search_input, #search_questions");
  const $listGroupItems = $(".list-group-item");
  const $cardBody = $(".card-body");
  
  $questionType.change(function () {
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

  $("#add_mc_option").click(function () {
    const optionCount = $("#mc_options .option-group").length + 1;
    const newOption = `
            <div class="option-group d-flex align-items-center mb-2">
                <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
                <label><input type="radio" name="is_right" value="${optionCount}"> Correct</label>
                <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
            </div>
        `;
    $("#mc_options").append(newOption);
  });

  $("#add_cb_option").click(function () {
    const optionCount = $("#cb_options .option-group").length + 1;
    const newOption = `
            <div class="option-group d-flex align-items-center mb-2">
                <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
                <label><input type="checkbox" name="is_right[]" value="${optionCount}"> Correct</label>
                <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
            </div>
        `;
    $("#cb_options").append(newOption);
  });

  $(document).on("click", ".remove-option", function () {
    if ($(".option-group").length > 1) {
      $(this).closest(".option-group").remove();
      reindexCheckboxValues();
    } else {
      alert("At least one option is required.");
    }
  });

  function reindexCheckboxValues() {
    $("#mc_options .option-group").each(function (index) {
      $(this)
        .find('input[type="radio"][name="is_right"]')
        .val(index + 1);
    });
    $("#cb_options .option-group").each(function (index) {
      $(this)
        .find('input[type="checkbox"][name="is_right[]"]')
        .val(index + 1);
    });
  }

  // Add question button click
  $("#add_item_btn").click(function () {
    $("#manage_question").modal("show");
    $("#question-frm")[0].reset();
    $(".question-type-options").hide();
    $("#manageQuestionLabel").text("Add New Question");
  });

  let selectionMode = false;
  let selectedQuestions = new Set();

  $("#select_question_btn").click(function () {
    selectionMode = !selectionMode;
    toggleSelectionMode();
  });

  $("#add_to_btn").click(function () {
    if (selectedQuestions.size > 0) {
      $("#selected_questions_count").text(selectedQuestions.size);
      $("#addToModal").modal("show");
    }
  });

  function toggleSelectionMode() {
    const $selectBtn = $("#select_question_btn");
    const $listItems = $listGroupItems;
    
    if (selectionMode) {
      $selectBtn.html('<i class="fa fa-times"></i> Cancel Selection')
        .removeClass("btn-primary")
        .addClass("btn-warning");
      $listItems.addClass("selectable").css("cursor", "pointer");

      $("#select_all_container").show();
      $("#select_all_checkbox")
        .prop("checked", false)
        .prop("indeterminate", false);

      $listItems.each(function () {
        const questionId = $(this).find(".edit_question").data("id");
        if (!$(this).find(".question-checkbox").length && questionId) {
          $(this).prepend(`
                        <div class="form-check question-checkbox">
                            <input class="form-check-input" type="checkbox" value="${questionId}" id="question_${questionId}">
                        </div>
                    `);
        }
      });
    } else {
      $selectBtn.html('<i class="fa fa-list-check"></i> Select Question')
        .removeClass("btn-warning")
        .addClass("btn-primary");
      $listItems
        .removeClass("selectable selected")
        .css("cursor", "default");
      $(".question-checkbox").remove();
      selectedQuestions.clear();
      updateAddToButton();

      $("#select_all_container").hide();
      $("#select_all_checkbox")
        .prop("checked", false)
        .prop("indeterminate", false);
    }
  }

  $("#select_all_checkbox").change(function () {
    const isChecked = $(this).is(":checked");
    const $checkboxes = $(".question-checkbox input");

    $checkboxes.each(function () {
      const questionId = $(this).val();
      const questionItem = $(this).closest(".list-group-item");

      $(this).prop("checked", isChecked);

      if (isChecked) {
        selectedQuestions.add(questionId);
        questionItem.addClass("selected");
      } else {
        selectedQuestions.delete(questionId);
        questionItem.removeClass("selected");
      }
    });

    updateAddToButton();
  });

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
    updateSelectAllCheckbox();
  });

  function updateAddToButton() {
    const $addToBtn = $("#add_to_btn");
    const count = selectedQuestions.size;
    
    if (count > 0) {
      $addToBtn.prop("disabled", false)
        .html(`<i class="fa fa-folder-plus"></i> Add To... (${count})`);
    } else {
      $addToBtn.prop("disabled", true)
        .html('<i class="fa fa-folder-plus"></i> Add To...');
    }
  }

  function updateSelectAllCheckbox() {
    const $selectAll = $("#select_all_checkbox");
    const $checkboxes = $(".question-checkbox input");
    const totalCheckboxes = $checkboxes.length;
    const checkedCheckboxes = $checkboxes.filter(":checked").length;

    if (checkedCheckboxes === 0) {
      $selectAll.prop("checked", false).prop("indeterminate", false);
    } else if (checkedCheckboxes === totalCheckboxes) {
      $selectAll.prop("checked", true).prop("indeterminate", false);
    } else {
      $selectAll.prop("checked", false).prop("indeterminate", true);
    }
  }

  $(document).on("click", ".list-group-item.selectable", function (e) {
    if (!$(e.target).is("input, button, a, .btn")) {
      const checkbox = $(this).find(".question-checkbox input");
      checkbox.prop("checked", !checkbox.prop("checked"));
      checkbox.trigger("change");
    }
  });

  $("#existing_assessment").change(function () {
    const assessmentSelected = $(this).val() !== "";
    $("#confirm_add_to").prop("disabled", !assessmentSelected);
  });

  $("#new_assessment_btn").click(function () {
    if (selectedQuestions.size === 0) {
      alert("Please select at least one question to add to an assessment.");
      return;
    }
    $("#new_assessment_selected_count").text(selectedQuestions.size);
    $("#newAssessmentModal").modal("show");
  });

  $(document).on("submit", "#new_assessment_form", function (e) {
    e.preventDefault();
    const title = $("#new_assessment_title").val().trim();
    const type = $("#new_assessment_type").val();

    if (!title || !type) {
      alert("Please enter assessment title and type.");
      return;
    }

    const $submitBtn = $("#new_assessment_submit");
    $submitBtn.prop("disabled", true).text("Creating...");

    const formData = new FormData();
    formData.append("assessment_title", title);
    formData.append("assessment_type", type);

    fetch("databank_ajax_create_assessment.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => {
        if (!res.ok) throw new Error("Network response was not ok");
        return res.json();
      })
      .then((data) => {
        if (data.success) {
          const assessmentId = data.assessment_id;
          addSelectedQuestionsToAssessment(
            assessmentId,
            function (successAdd, message) {
              $("#newAssessmentModal").modal("hide");
              $("#addToModal").modal("hide");
              if (successAdd) {
                alert("Assessment created and " + message);
                location.reload();
              } else {
                alert("Assessment created, but failed to add questions: " + message);
                location.reload();
              }
            }
          );
        } else {
          alert("Error creating assessment: " + (data.message || "Unknown error"));
        }
      })
      .catch((err) => {
        console.error("Create error:", err);
        alert("Network error occurred: " + err.message);
      })
      .finally(() => {
        $submitBtn.prop("disabled", false).text("Create & Add");
      });
  });

  $("#confirm_add_to").click(function () {
    const assessmentId = $("#existing_assessment").val();
    if (!assessmentId) {
      alert("Please select an existing assessment.");
      return;
    }

    const $confirmBtn = $(this);
    $confirmBtn.prop("disabled", true)
      .html('<i class="fa fa-spinner fa-spin"></i> Adding...');

    addSelectedQuestionsToAssessment(assessmentId, function (success, message) {
      $("#addToModal").modal("hide");
      if (success) {
        alert(message);
        selectionMode = false;
        toggleSelectionMode();
        location.reload();
      } else {
        alert("Error: " + message);
      }
      $confirmBtn.prop("disabled", false)
        .html('<i class="fa fa-save"></i> Add to Assessment');
    });
  });

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
      .then((res) => {
        if (!res.ok) throw new Error("Network response was not ok");
        return res.json();
      })
      .then((data) => {
        if (data.success) {
          cb(true, data.message || "Added successfully");
        } else {
          cb(false, data.message || "Failed to add questions");
        }
      })
      .catch((err) => {
        console.error("Add to assess error:", err);
        cb(false, "Network error: " + err.message);
      });
  }

  $("#addToModal").on("hidden.bs.modal", function () {
    $("#existing_assessment").val("");
    $("#confirm_add_to").prop("disabled", true);
  });

  $("#question-frm").submit(function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const questionType = $questionType.val();
    const questionId = $('input[name="id"]').val();
    const url = questionId
      ? "databank_ajax_update_question.php"
      : "databank_ajax_save_question.php";

    if (!questionType) {
      alert("Please select a question type");
      return;
    }

    if (!$questionText.val().trim()) {
      alert("Please enter question text");
      return;
    }

    const $saveBtn = $("#save_question_btn");
    $saveBtn.prop("disabled", true)
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
        $saveBtn.prop("disabled", false).html("Save Question");
      });
  });

  $(document).on("click", ".remove_question", function () {
    const questionId = $(this).data("id");
    const $removeBtn = $(this);

    if (
      confirm(
        "Are you sure you want to delete this question? This action cannot be undone."
      )
    ) {
      $removeBtn.html('<i class="fa fa-spinner fa-spin"></i>')
        .prop("disabled", true);

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
            $removeBtn.html('<i class="fa fa-trash"></i>')
              .prop("disabled", false);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Network error occurred");
          $removeBtn.html('<i class="fa fa-trash"></i>')
            .prop("disabled", false);
        });
    }
  });

  $(document).on("click", ".edit_question", function () {
    const questionId = $(this).data("id");

    fetch("databank_ajax_get_question.php?question_id=" + questionId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          $("#manageQuestionLabel").text("Edit Question");
          $('input[name="id"]').val(questionId);
          $questionType.val(data.question.question_type);
          $questionText.val(data.question.question_text);
          $("#difficulty").val(data.question.difficulty);
          $("#points").val(data.question.total_points || 1);

          $questionType.trigger("change");

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
        const optionValue = index + 1; // Use 1-based indexing
        const optionHtml = `
                    <div class="option-group d-flex align-items-center mb-2">
                        <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text">${
                          option.option_text
                        }</textarea>
                        <label>
                            <input type="${
                              questionType === "1" ? "radio" : "checkbox"
                            }"
                                name="${
                                  questionType === "1"
                                    ? "is_right"
                                    : "is_right[]"
                                }"
                                value="${optionValue}"
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

  $("#manage_question").on("hidden.bs.modal", function () {
    $("#question-frm")[0].reset();
    $(".question-type-options").hide();
    $("#manageQuestionLabel").text("Add New Question");
    $('input[name="id"]').val("");
  });

  const debounceFn = (fn, delay) => {
    let timeoutId;
    return function () {
      const context = this;
      const args = arguments;
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => fn.apply(context, args), delay);
    };
  };

  let cachedListItems = $listGroupItems;
  const noteId = "#no_questions_note";

  function runQuestionFilter(searchTerm) {
    const term = (searchTerm !== undefined && searchTerm !== null) 
      ? searchTerm 
      : ($questionSearchInput.val() || "");
    const searchTermLower = term.toString().toLowerCase().trim();

    if (cachedListItems.length !== $listGroupItems.length) {
      cachedListItems = $listGroupItems;
    }

    if (searchTermLower === "") {
      cachedListItems.show();
      $(noteId).remove();
      return;
    }

    $("#clear_search").show();

    let visibleCount = 0;

    cachedListItems.each(function () {
      const $item = $(this);
      const questionText = $item.find("h6").text().toLowerCase();
      const questionType = $item.find("p:contains('Type:')").text().toLowerCase();
      const difficulty = $item.find("p:contains('Difficulty:')").text().toLowerCase();
      const options = $item.find(".option-item").text().toLowerCase();

      const searchableContent = questionText + " " + questionType + " " + difficulty + " " + options;

      if (searchableContent.includes(searchTermLower)) {
        $item.show();
        visibleCount++;
      } else {
        $item.hide();
      }
    });

    if (visibleCount === 0) {
      if (!$(noteId).length) {
        const note = $(
          '<div id="no_questions_note" class="text-muted" style="margin-top:20px; text-align:center; font-style: italic;">No questions found matching your search.</div>'
        );
        $cardBody.append(note);
      }
    } else {
      $(noteId).remove();
    }
  }

  const debouncedSearch = debounceFn(function () {
    runQuestionFilter($(this).val());
  }, 300);

  $questionSearchInput.on("input", debouncedSearch);
  
  $("#question_search_btn").on("click", function () {
    runQuestionFilter($questionSearchInput.val());
  });

  $("#clear_search").click(function () {
    $questionSearchInput.val("").trigger("input");
    $questionSearchInput.focus();
  });

  $questionSearchInput.keypress(function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $(this).blur();
    }
  });
});
