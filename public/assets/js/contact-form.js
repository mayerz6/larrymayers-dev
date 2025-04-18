class ContactForm {

    constructor(){
        this.emailInput = document.getElementById("contactForm");
    }

}

document.getElementById("contact_form").addEventListener("submit", async function(e){
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const responseMsg = document.getElementById("responseMsg");

    try{
        const response = await fetch(form.action, {
            method: form.method,
            body: formData,
        });

        // Handle non-200 responses gracefully
        if(!response.ok){
            const errorData = await response.json();
            responseMsg.textContent = errorData.message || "An error occurred on the server!!!";
            responseMsg.style.color = "red";
            return;
        }

        // If response is OK, Handle success
        const result = await response.json();
        responseMsg.textContent = result.message;
        responseMsg.style.color = "green";
        /** Clear FORM data on ASYNC response. */
        form.reset();
    } catch (error) {
        responseMsg.textContent = "Error: Could not connect to the server or parse response messages.";
        responseMsg.style.color = "red";
        console.error("Request failed: ", error);
    }
});
