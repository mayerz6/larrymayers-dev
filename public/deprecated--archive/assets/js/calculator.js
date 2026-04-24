function calculate(operation){
    const form = document.getElementById('cal_form'); // Reference the FORM element within the page.
   
    if (!form) {
        console.error("Form element not found");
        return;
    }

    const val1 = form.querySelector('input[name="val_1"]').value;
    const val2 = form.querySelector('input[name="val_2"]').value;

    // Create a JSON object with the form data
    const data = {
        val_1: val1,
        val_2: val2,
        operation: operation
    };

    /* PHP Form Submission VIA JS client side processing */
    fetch('http://localhost:8080/larrymayers.site/public/calculator.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data) /* Send the values collected via the FORM to the PHP file as a JSON Object */
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('result');
        if (data.error) {
            resultDiv.textContent = `Error: ${data.error}`;
        } else {
            resultDiv.textContent = `Result: ${data.result}`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}