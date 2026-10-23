
const feedbackForm = document.querySelector("form");
const summaryContainer = document.getElementById("feedbackSummary");
const recommendationsContainer = document.getElementById("recommendations");

function calculateAverage(teaching, practical, content) {
  return (teaching + practical + content) / 3;
}

function calculatePercentage(average) {
  return (average / 5) * 100;
}

function getClassification(average) {
  if (average >= 4.5) return "Excellent";
  if (average >= 3.5) return "Very Good";
  if (average >= 2.5) return "Good";
  if (average >= 1.5) return "Needs Improvement";
  return "Poor";
}

function getRecommendation(classification) {
  const recommendations = {
    Excellent: "Maintain current teaching approach",
    "Very Good": "Minor improvements may be considered",
    Good: "Improvement required in selected areas",
    "Needs Improvement": "Review teaching/content delivery",
    Poor: "Immediate improvement plan required"
  };

  return recommendations[classification];
}

function getErrorElement(id) {
  let errorElement = document.getElementById(id);

  if (!errorElement) {
    errorElement = document.createElement("p");
    errorElement.id = id;
    errorElement.className = "validation-error";
    errorElement.setAttribute("role", "alert");
  }

  return errorElement;
}

function showFieldError(field, message, errorId) {
  const errorElement = getErrorElement(errorId);
  errorElement.textContent = message;

  if (field.type === "radio") {
    const fieldset = field.closest("fieldset");
    fieldset.setAttribute("aria-invalid", "true");
    fieldset.style.borderColor = "#b91c1c";
    fieldset.appendChild(errorElement);
  } else {
    field.setAttribute("aria-invalid", "true");
    field.style.borderColor = "#b91c1c";
    field.insertAdjacentElement("afterend", errorElement);
  }
}

function clearFieldError(field, errorId) {
  const errorElement = document.getElementById(errorId);
  if (errorElement) errorElement.remove();

  if (field.type === "radio") {
    const fieldset = field.closest("fieldset");
    fieldset.removeAttribute("aria-invalid");
    fieldset.style.borderColor = "";
  } else {
    field.removeAttribute("aria-invalid");
    field.style.borderColor = "";
  }
}

function validateFeedback() {
  let isValid = true;
  const errors = [];

  const requiredFields = [
    {
      field: document.getElementById("studentName"),
      message: "Please enter the student name.",
      errorId: "studentNameError"
    },
    {
      field: document.getElementById("courseName"),
      message: "Please enter the course name.",
      errorId: "courseNameError"
    },
    {
      field: document.getElementById("facultyName"),
      message: "Please enter the faculty name.",
      errorId: "facultyNameError"
    }
  ];

  requiredFields.forEach(({ field, message, errorId }) => {
    clearFieldError(field, errorId);

    if (!field.value.trim()) {
      showFieldError(field, message, errorId);
      errors.push(message);
      isValid = false;
    }
  });

  const ratingGroups = [
    {
      name: "teachingRating",
      label: "Teaching",
      errorId: "teachingRatingError"
    },
    {
      name: "labRating",
      label: "Practical/Lab",
      errorId: "labRatingError"
    },
    {
      name: "contentRating",
      label: "Content",
      errorId: "contentRatingError"
    }
  ];

  ratingGroups.forEach(({ name, label, errorId }) => {
    const radios = [...feedbackForm.querySelectorAll(`input[name="${name}"]`)];
    const selectedRadio = radios.find((radio) => radio.checked);
    const field = radios[0];

    clearFieldError(field, errorId);

    if (!selectedRadio) {
      const message = `Please select a ${label} rating from 1 to 5.`;
      showFieldError(field, message, errorId);
      errors.push(message);
      isValid = false;
      return;
    }

    const rating = Number(selectedRadio.value);

    if (isNaN(rating) || rating < 1 || rating > 5) {
      const message = `${label} rating must be a number from 1 to 5.`;
      showFieldError(field, message, errorId);
      errors.push(message);
      isValid = false;
    }
  });

  if (!isValid) {
    alert(`Please correct the following:\n\n${errors.join("\n")}`);
  }

  return isValid;
}

function submitFeedback(event) {
  event.preventDefault();

  if (!validateFeedback()) return;

  const confirmed = confirm("Do you want to submit this feedback?");
  if (!confirmed) return;

  const suggestion = prompt(
    "Optional: What improvement would you suggest?",
    ""
  );

  const ratings = {
    Teaching: Number(
      feedbackForm.querySelector('input[name="teachingRating"]:checked').value
    ),
    "Practical/Lab": Number(
      feedbackForm.querySelector('input[name="labRating"]:checked').value
    ),
    Content: Number(
      feedbackForm.querySelector('input[name="contentRating"]:checked').value
    )
  };

  const average = calculateAverage(
    ratings.Teaching,
    ratings["Practical/Lab"],
    ratings.Content
  );
  const percentage = calculatePercentage(average);
  const classification = getClassification(average);
  const recommendation = getRecommendation(classification);

  const lowestRating = Math.min(...Object.values(ratings));
  const lowestCategories = Object.entries(ratings)
    .filter(([, rating]) => rating === lowestRating)
    .map(([category]) => category);

  const ratingCounts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
  Object.values(ratings).forEach((rating) => {
    ratingCounts[rating] += 1;
  });

  const countSummary = Object.entries(ratingCounts)
    .filter(([, count]) => count > 0)
    .map(([rating, count]) => `${rating}: ${count}`)
    .join(", ");

  summaryContainer.replaceChildren();
  recommendationsContainer.replaceChildren();

  const resultHeading = document.createElement("h3");
  resultHeading.textContent = "Feedback Summary";

  const resultDetails = document.createElement("p");
  resultDetails.textContent =
    `Teaching: ${ratings.Teaching}/5 | ` +
    `Practical/Lab: ${ratings["Practical/Lab"]}/5 | ` +
    `Content: ${ratings.Content}/5`;

  const score = document.createElement("p");
  score.textContent =
    `Average: ${average.toFixed(2)}/5 ` +
    `(${percentage.toFixed(2)}%) — ${classification}`;

  const countDetails = document.createElement("p");
  countDetails.textContent = `Number of ratings at each score: ${countSummary}`;

  const lowestDetails = document.createElement("p");
  lowestDetails.textContent =
    `Category requiring the greatest improvement: ` +
    `${lowestCategories.join(", ")} (rating: ${lowestRating}/5)`;

  summaryContainer.append(
    resultHeading,
    resultDetails,
    score,
    countDetails,
    lowestDetails
  );

  const recommendationHeading = document.createElement("h3");
  recommendationHeading.textContent = "Recommendations";

  const recommendationText = document.createElement("p");
  recommendationText.textContent = recommendation;

  recommendationsContainer.append(
    recommendationHeading,
    recommendationText
  );
  recommendationsContainer.style.display = "block";
  recommendationsContainer.style.visibility = "visible";
  recommendationsContainer.style.padding = "16px";
  recommendationsContainer.style.borderLeft = "4px solid #075985";
  recommendationsContainer.style.backgroundColor = "#ecfdf3";
  recommendationsContainer.style.overflowWrap = "anywhere";

  if (suggestion && suggestion.trim()) {
    const suggestionText = document.createElement("p");
    suggestionText.textContent =
      `Student suggestion: ${suggestion.trim()}`;
    recommendationsContainer.appendChild(suggestionText);
  }

  if (Object.values(ratings).every((rating) => rating >= 4.5)) {
    const excellentMessage = document.createElement("p");
    excellentMessage.textContent =
      "All three ratings are at least 4.5. Keep up the excellent work!";
    recommendationsContainer.appendChild(excellentMessage);
  }

  summaryContainer.style.borderLeft = "4px solid #075985";
  summaryContainer.style.padding = "12px";
  summaryContainer.style.overflowWrap = "anywhere";

  const classificationColors = {
    Excellent: "#166534",
    "Very Good": "#1d4ed8",
    Good: "#854d0e",
    "Needs Improvement": "#c2410c",
    Poor: "#b91c1c"
  };

  score.style.color = classificationColors[classification];
  score.style.fontWeight = "bold";

  alert("Feedback submitted successfully.");
}

function resetFeedback(event) {
  event.preventDefault();

  const confirmed = confirm(
    "Are you sure you want to reset the form and clear the results?"
  );

  if (!confirmed) return;

  feedbackForm.reset();

  feedbackForm
    .querySelectorAll(".validation-error")
    .forEach((errorElement) => errorElement.remove());

  feedbackForm
    .querySelectorAll('[aria-invalid="true"]')
    .forEach((field) => field.removeAttribute("aria-invalid"));

  feedbackForm.querySelectorAll("fieldset").forEach((fieldset) => {
    fieldset.style.borderColor = "";
  });

  feedbackForm
    .querySelectorAll("input, textarea")
    .forEach((field) => {
      field.style.borderColor = "";
    });

  summaryContainer.replaceChildren();
  recommendationsContainer.replaceChildren();
  recommendationsContainer.style.display = "none";
  recommendationsContainer.style.visibility = "hidden";
  recommendationsContainer.removeAttribute("style");
  summaryContainer.removeAttribute("style");
}

feedbackForm.addEventListener("submit", submitFeedback);

const resetButton = feedbackForm.querySelector('button[type="reset"]');
resetButton.addEventListener("click", resetFeedback);