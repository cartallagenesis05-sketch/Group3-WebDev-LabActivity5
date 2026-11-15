
// Select all delete forms
const deleteForms = document.querySelectorAll(".delete-form");

// Add confirmation before deleting a task
deleteForms.forEach(function(form) {

    form.addEventListener("submit", function(event) {

        const confirmDelete = confirm(
            "Are you sure you want to delete this task?"
        );

        if (!confirmDelete) {
            event.preventDefault();
        }

    });

});

// Display a message when the page is loaded
document.addEventListener("DOMContentLoaded", function() {
    console.log("To-do List App is ready!");
});
