//=========================================//
/*/*            Contact Form               */
//=========================================//
document.addEventListener("DOMContentLoaded", () => {
    const form = document.forms["myForm"];
    const errorMsg = document.getElementById("error-msg");
    const responseMsg = document.getElementById("simple-msg");

    form?.addEventListener("submit", async (e) => {
        e.preventDefault();

        errorMsg.style.opacity = 0;
        errorMsg.innerHTML = "";

        const name = form["name"].value.trim();
        const email = form["email"].value.trim();
        const subject = form["subject"].value.trim();
        const number = form["number"].value.trim();
        const comments = form["comments"].value.trim();

        const showError = (message) => {
            errorMsg.innerHTML = `<div class="alert alert-warning error_message">${message}</div>`;
            fadeIn(errorMsg);
        };

        if (!name) return showError("*Please enter a Name*");
        if (!email) return showError("*Please enter an Email*");
        if (!subject) return showError("*Please enter a Subject*");
        if (!number) return showError("*Please enter a Number*");
        if (!comments) return showError("*Please enter Comments*");

        const payload = {
            issue: subject || "Contact Form Inquiry",
            description: `From: ${name} (${email}, ${number})\n\nComments:\n${comments}`,
            email: email,
            name: name
        };

        try {
            const endpoint = (typeof window.API_URL === 'function') 
                ? window.API_URL('/api/tickets') 
                : 'http://127.0.0.1:8000/api/tickets';

            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok || response.status === 200 || response.status === 201) {
                responseMsg.innerHTML = `<div id="success_page" class="alert alert-success">Thank you! Your message has been sent successfully. We will get back to you shortly.</div>`;
                form.reset();
            } else {
                const errorText = result.message || (result.errors ? Object.values(result.errors).flat().join(', ') : "Failed to send message.");
                errorMsg.innerHTML = `<div class="alert alert-danger">${errorText}</div>`;
                fadeIn(errorMsg);
            }
        } catch (err) {
            console.error("Error in form submission:", err);
            errorMsg.innerHTML = `<div class="alert alert-danger">Unable to reach the server. Please check your internet connection.</div>`;
            fadeIn(errorMsg);
        }
    });

    function fadeIn(element) {
        element.style.opacity = 0;
        element.style.display = "block";

        let opacity = 0;
        const interval = setInterval(() => {
            opacity += 0.1;
            element.style.opacity = opacity;
            if (opacity >= 1) clearInterval(interval);
        }, 50);
    }
});