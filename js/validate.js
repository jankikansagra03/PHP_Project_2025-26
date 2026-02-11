$(document).ready(function () {
  function validateInput(input) {
    var field = $(input);
    var value = field.val().trim();
    var errorfield = $("#" + field.attr("name") + "_error");
    var validationType = field.data("validation");
    var minLength = field.data("min") || 0;
    var maxLength = field.data("max") || 9999;
    var fileSize = field.data("filesize") || 0;
    var fileType = field.data("filetype") || "";
    let errorMessage = "";

    if (validationType) {
      // Required field validation
      if (validationType.includes("required") && value === "") {
        errorMessage = "This field is required.";
      }

      // Minimum length validation
      if (validationType.includes("min") && value.length < minLength) {
        errorMessage = `This field must be at least ${minLength} characters long.`;
      }

      // Maximum length validation
      if (validationType.includes("max") && value.length > maxLength) {
        errorMessage = `This field must be at most ${maxLength} characters long.`;
      }

      // Email format validation
      if (validationType.includes("email")) {
        const emailRegex = /^[\w-\.]+@([\w-]+\.)+[\w]{2,4}$/;
        if (!emailRegex.test(value)) {
          errorMessage = "Please enter a valid email address.";
        }
      }

      // // URL format validation
      // if (validationType.includes("url")) {
      //   const urlRegex =
      //     /^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/;
      //   if (!urlRegex.test(value)) {
      //     errorMessage = "Please enter a valid URL.";
      //   }
      // }

      // Numeric value validation
      if (validationType.includes("number")) {
        const numberRegex = /^[0-9]+$/;
        if (!numberRegex.test(value)) {
          errorMessage = "Please enter a valid number.";
        }
      }

      // Strong password validation (at least 8 chars, 1 upper, 1 lower, 1 number, 1 special)
      if (validationType.includes("strongPassword")) {
        const passwordRegex =
          /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(value)) {
          errorMessage =
            "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
        }
      }

      // Password confirmation validation
      if (validationType.includes("confirmPassword")) {
        const confirmPassword = $("#" + field.attr("name") + "_confirm").val();
        if (value !== confirmPassword) {
          errorMessage = "Passwords do not match.";
        }
      }

      // Dropdown selection validation
      if (validationType.includes("select") && value === "") {
        errorMessage = "Please select an option.";
      }

      // File size validation
      if (validationType.includes("fileSize")) {
        const file = field[0].files[0];
        if (file && file.size > fileSize * 1024) {
          errorMessage = `File size must be less than ${fileSize}KB.`;
        }
      }

      if (validationType.includes("fileType")) {
        const file = field[0].files[0];
        const fileExtension = file.name.split(".").pop().toLowerCase();
        const allowedExtensions = fileType
          .split(",")
          .map((ext) => ext.trim().toLowerCase());
        if (!allowedExtensions.includes(fileExtension)) {
          errorMessage = `File type must be ${fileType}.`;
        }
      }

      if (errorMessage) {
        errorfield.text(errorMessage).show();
        field.addClass("is-invalid").removeClass("is-valid");
        errorfield.addClass("small text-danger");
      } else {
        errorfield.text("").hide();
        field.removeClass("is-invalid").addClass("is-valid");
      }
    }
  }
  $("input, textarea, select").on("input change", function () {
    validateInput(this);
  });

  $("form").on("submit", function (e) {
    let isValid = true;
    $(this)
      .find("input,textarea,select")
      .each(function () {
        validateInput(this);
        let errorfield = $("#" + $(this).attr("name") + "_error");
        if (errorfield.text().trim() !== "") {
          isValid = false;
        }
      });
    if (!isValid) {
      e.preventDefault();
    }
  });
});
