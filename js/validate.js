$(document).ready(function () {
  function getErrorField(field) {
    var errorSelector = field.data("error");
    var errorField = errorSelector ? $(errorSelector) : $();

    if (!errorField.length) {
      var fieldName = field.attr("name") || "";
      errorField = field.closest("form").find("#" + fieldName + "_error");
    }

    if (!errorField.length) {
      errorField = field.next("small");
    }

    if (!errorField.length) {
      errorField = $("<small></small>");
      field.after(errorField);
    }

    errorField.addClass("small text-danger");
    return errorField;
  }

  function hasRule(rules, name) {
    return rules.indexOf(name.toLowerCase()) !== -1;
  }

  function validateInput(input) {
    var field = $(input);
    var validationType = (field.data("validation") || "")
      .toString()
      .toLowerCase();

    var rules = validationType
      .split(",")
      .map(function (rule) {
        return rule.trim();
      })
      .filter(function (rule) {
        return rule !== "";
      });

    // Fall back to native HTML constraints when data-validation is not provided.
    if (field.prop("required") && !hasRule(rules, "required")) {
      rules.push("required");
    }

    var fieldTag = (field.prop("tagName") || "").toLowerCase();
    if (fieldTag === "select" && !hasRule(rules, "select")) {
      rules.push("select");
    }

    var nativeType = (field.attr("type") || "").toLowerCase();
    if (nativeType === "email" && !hasRule(rules, "email")) {
      rules.push("email");
    }
    if (
      (nativeType === "number" || fieldTag === "input") &&
      !hasRule(rules, "number") &&
      field.attr("step") !== undefined &&
      nativeType === "number"
    ) {
      rules.push("number");
    }

    var value = field.val() ? field.val().toString().trim() : "";
    var minLength = Number(field.data("min") || field.attr("minlength") || 0);
    var maxLength = Number(
      field.data("max") || field.attr("maxlength") || 9999,
    );
    var minValue = Number(field.attr("min"));
    var maxValue = Number(field.attr("max"));
    var fileSizeMB = Number(field.data("filesize") || 0);
    var fileType = (field.data("filetype") || "").toString().toLowerCase();
    var errorField = getErrorField(field);
    var errorMessage = "";

    var inputType = (field.attr("type") || "").toLowerCase();
    var isFileInput = inputType === "file";
    var isCheckbox = inputType === "checkbox";
    var isSelect = field.is("select");

    if (hasRule(rules, "required")) {
      if (isCheckbox && !field.is(":checked")) {
        errorMessage = "This field is required.";
      } else if (
        isFileInput &&
        (!field[0].files || field[0].files.length === 0)
      ) {
        errorMessage = "Please select a file to upload.";
      } else if (!isFileInput && (value === "" || value === null)) {
        errorMessage = "This field is required.";
      }
    }

    if (
      !errorMessage &&
      hasRule(rules, "select") &&
      (value === "" || value === "0" || value === null)
    ) {
      errorMessage = "Please select an option.";
    }

    if (!errorMessage && !isFileInput && !isSelect && value !== "") {
      if (hasRule(rules, "min") && value.length < minLength) {
        errorMessage =
          "This field must be at least " + minLength + " characters long.";
      }

      if (!errorMessage && hasRule(rules, "max") && value.length > maxLength) {
        errorMessage =
          "This field must be at most " + maxLength + " characters long.";
      }

      if (!errorMessage && hasRule(rules, "alphabetic")) {
        var alphabetRegex = /^[a-zA-Z\s]+$/;
        if (!alphabetRegex.test(value)) {
          errorMessage = "Please enter alphabetic characters only.";
        }
      }

      if (!errorMessage && hasRule(rules, "email")) {
        var emailRegex = /^[\w-.]+@([\w-]+\.)+[\w]{2,}$/;
        if (!emailRegex.test(value)) {
          errorMessage = "Please enter a valid email address.";
        }
      }

      if (!errorMessage && hasRule(rules, "number")) {
        var numberRegex = /^\d+(\.\d+)?$/;
        if (!numberRegex.test(value)) {
          errorMessage = "Please enter a valid number.";
        }
      }

      if (!errorMessage && nativeType === "number" && value !== "") {
        var numericValue = Number(value);
        if (!Number.isNaN(minValue) && numericValue < minValue) {
          errorMessage =
            "Value must be greater than or equal to " + minValue + ".";
        }
        if (
          !errorMessage &&
          !Number.isNaN(maxValue) &&
          numericValue > maxValue
        ) {
          errorMessage =
            "Value must be less than or equal to " + maxValue + ".";
        }
      }

      if (!errorMessage && hasRule(rules, "strongpassword")) {
        var passwordRegex =
          /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(value)) {
          errorMessage =
            "Password must be at least 8 characters and include upper, lower, number, and special character.";
        }
      }

      if (!errorMessage && hasRule(rules, "confirmpassword")) {
        var confirmPassword = $(
          "#" + (field.attr("name") || "") + "_confirm",
        ).val();
        if (value !== confirmPassword) {
          errorMessage = "Passwords do not match.";
        }
      }
    }

    if (
      !errorMessage &&
      isFileInput &&
      field[0].files &&
      field[0].files.length > 0
    ) {
      var files = field[0].files;

      for (var i = 0; i < files.length; i++) {
        var file = files[i];

        if (hasRule(rules, "filesize") && fileSizeMB > 0) {
          if (file.size > fileSizeMB * 1024 * 1024) {
            errorMessage = "File size must be less than " + fileSizeMB + " MB.";
            break;
          }
        }

        if (!errorMessage && hasRule(rules, "filetype") && fileType !== "") {
          var fileExtension = file.name.split(".").pop().toLowerCase();
          var allowedExtensions = fileType.split(",").map(function (ext) {
            return ext.trim().replace(".", "");
          });
          if (allowedExtensions.indexOf(fileExtension) === -1) {
            errorMessage = "Allowed file types: " + fileType + ".";
            break;
          }
        }
      }
    }

    if (errorMessage) {
      errorField.text(errorMessage).show();
      field.addClass("is-invalid").removeClass("is-valid");
      return false;
    }

    errorField.text("").hide();
    field.removeClass("is-invalid").addClass("is-valid");
    return true;
  }

  $(document).on("input change", "input, textarea, select", function () {
    validateInput(this);
  });

  $(document).on("submit", "form", function (e) {
    var isValid = true;
    var firstInvalidField = null;

    $(this)
      .find("input, textarea, select")
      .each(function () {
        var fieldValid = validateInput(this);
        if (!fieldValid) {
          isValid = false;
          if (!firstInvalidField) {
            firstInvalidField = this;
          }
        }
      });

    if (!isValid) {
      e.preventDefault();

      var parentModal = $(this).closest(".modal");
      if (parentModal.length && typeof bootstrap !== "undefined") {
        var modalInstance = bootstrap.Modal.getOrCreateInstance(parentModal[0]);
        modalInstance.show();
      }

      if (firstInvalidField) {
        firstInvalidField.focus();
      }

      return false;
    }

    return true;
  });
});
