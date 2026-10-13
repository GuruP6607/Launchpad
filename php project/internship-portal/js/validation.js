
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-validate]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            let valid = true;

            const password = form.querySelector('[name="password"]');
            const confirm = form.querySelector('[name="confirm_password"]');

            form.querySelectorAll("[required]").forEach((field) => {
                field.setCustomValidity("");

                if (!field.value.trim()) {
                    field.setCustomValidity("Please complete this field.");
                    valid = false;
                } else if (!field.checkValidity()) {
                    valid = false;
                }
            });

            if (password && confirm && password.value !== confirm.value) {
                confirm.setCustomValidity("Passwords do not match.");
                valid = false;
            }

            if (!valid || !form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    });

    document.querySelectorAll("[data-confirm]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            const message = form.dataset.confirm || "Are you sure?";
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});