import { Validator } from './validator.js';

export function initContactForm() {
    new Form();
}

class FormCache{

    static load(formId){
        const form = document.getElementById(formId);
        if(!form) throw new Error(`Form #${formId} Not Found!`);

        this.form = form;
        this.emailInput = form.querySelector('#emailInput');
        this.emailError = form.querySelector('#emailErrorMsg');
        this.topicInput = form.querySelector('#msgTopic');
        this.topicError = form.querySelector('#topicErrorMsg');
        this.msgInput = form.querySelector('#feedbackMsg');
        this.msgError = form.querySelector('#feedbackErrorMsg');
    }
}


class Form{

    constructor(){
    
        /* Design a function which will handle the processing of data submitted by the user. */
           /* this.emailInput <=> let emailInput */
           this.emailInput = document.getElementById("emailInput");
           this.emailErrorMsg = document.getElementById("emailErrorMsg");
           this.emailValid = false;
           this.msgTopic = document.getElementById("msgTopic");
           this.topicErrorMsg = document.getElementById("topicErrorMsg");
           this.topicValid = false;
           this.usrMsg = document.getElementById("feedbackMsg");
           this.feedbackErrorMsg = document.getElementById("feedbackErrorMsg");
           this.msgValid = false;
           this.emailConfirm = document.getElementById("emailConfirm");
           this.btnSubmit = document.getElementById("btnSubmit");


this.msgTopic.addEventListener("blur", () => {

    /* TOPIC SELECTION Check */
if(!Validator.validate(this.msgTopic.value, Validator.DROP_BOX)){
    this.topicErrorMsg.innerText = "Please select a feedback topic!";
    this.msgTopic.style = "border-color: #ff0000;";
    this.topicErrorMsg.style = "color: #ff0000;";

    return;
    
} else if (Validator.validate(this.msgTopic.value, Validator.DROP_BOX)){

    this.topicErrorMsg.innerText = "";
    this.msgTopic.style = "border-color: #2ecc71;";
    this.topicErrorMsg.style = "display: none";

    this.topicValid = true;
}
 
});

this.usrMsg.addEventListener("blur", () => {
    
 /* MESSAGE Check */
 if(!Validator.validate(this.usrMsg.value, Validator.REQUIRED)){
    this.feedbackErrorMsg.innerText = "Blank feedback NOT allowed!!!";
    this.usrMsg.style = "border-color: #ff0000;";
    this.feedbackErrorMsg.style = "color: #ff0000;";

    return;
    
} else if(!Validator.validate(this.usrMsg.value, Validator.MAX_LENGTH, 140)){
    this.feedbackErrorMsg.innerText = "Feedback exceeds limit!";
    this.usrMsg.style = "border-color: #ff0000;";
    this.feedbackErrorMsg.style = "color: #ff0000;";

    return;

}else if(Validator.validate(this.usrMsg.value, Validator.REQUIRED)){

    this.feedbackErrorMsg.innerText = "";
    this.usrMsg.style = "border-color: #2ecc71;";
    this.feedbackErrorMsg.style = "display: none";

    this.msgValid = true;
}
 

});

this.emailInput.addEventListener("blur", () => {
 /* EMAIL Check */
 if(!Validator.validate(this.emailInput.value, Validator.REQUIRED)){
    this.emailErrorMsg.innerText = "Missing Email Info!";
    this.emailInput.style = "border-color: #ff0000;";
    this.emailErrorMsg.style = "color: #ff0000;";

    return;

} else if(!Validator.validate(this.emailInput.value, Validator.EMAIL)){
    this.emailErrorMsg.innerText = "Email format incorrect!";
    this.emailInput.style = "border-color: #ff0000;";
    this.emailErrorMsg.style = "color: #ff0000;";

    return;

} else if(Validator.validate(this.emailInput.value, Validator.EMAIL)) {

    this.emailErrorMsg.innerText = "";
    this.emailInput.style = "border-color: #2ecc71;";
    this.emailErrorMsg.style = "display: none";

    this.emailValid = true;
}

});          

this.btnSubmit.addEventListener("click", (e) => {
    e.preventDefault();
    this.subMsg();
});

}


async subMsg(){
    /* Must validate the user input before processing. */
    if(this.emailValid && this.msgValid && this.topicValid){

        const data = {
            email: this.emailInput.value.trim(),
            topic: this.msgTopic.options[this.msgTopic.selectedIndex].text.trim(),
            message: this.usrMsg.value.trim()
        };

        try {
            this.emailConfirm.innerHTML = "...Sending Message";
            this.emailConfirm.style.color = "#EE5552";

        const response = await fetch("./post_message.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams(data)
        });
        console.log(data);
        const text = await response.text();
        if(!response.ok) throw new Error(text);

        this.emailConfirm.innerHTML = "<b>Email Successfully Sent!</b>";
        this.emailConfirm.style.color = "#2ecc71";

        console.log("Contact Form Submitted!", data);

        this.formDataDestroy(); // Optional reset/clear method
/* Reset Form INPUT fields */

    } catch (err) {
        this.emailConfirm.innerHTML = `❌ ${err.message}`;
        this.emailConfirm.style.color = "red";
        console.error("Submission error:", err);
    }
} else {
    this.emailConfirm.innerHTML = "<b>Please complete the form details above.</b>";
    this.emailConfirm.style.color = "#ff0000";
    console.log("Message NOT Sent!");
}

}

fadeIn(){

}

fadeOut(){

  
}

formDataDestroy(){

    this.emailInput.value = "";
    this.emailValid = false;
    this.emailInput.style = "border-color: #ced4da;";
 
    this.msgTopic.value = 0;
    this.topicValid = false;
    this.msgTopic.style = "border-color: #ced4da;";

    this.usrMsg.value = "";
    this.msgValid = false;
    this.usrMsg.style = "border-color: #ced4da;";

}
  

}
