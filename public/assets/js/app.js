class Links {

    constructor() {
        /* Instantiate the elements of your NAV links */
        this.aboutLink = document.getElementById('about');
        this.expertLink = document.getElementById('expert');
        this.contactLink = document.getElementById('contact');  
        this.resumeLink = document.getElementById('resume'); // New resume link

        this.contactLink.addEventListener("click", this.clickContactHandler.bind(this));
        this.aboutLink.addEventListener("click", this.clickAboutHandler.bind(this));
        this.expertLink.addEventListener("click", this.clickExpertiseHandler.bind(this));
        this.resumeLink.addEventListener("click", this.clickResumeHandler.bind(this)); // New handler for resume
    }

    clickAboutHandler() {
        this.loadContent('./assets/regions/content/about.html');
        this.updateStyles('about', 'active-about');
        document.getElementById('content').style = "border-top: 20px solid #ff9300;";

    }

    clickContactHandler() {
        this.loadContent('./assets/regions/content/contact.html', () => new Form());
        this.updateStyles('contact', 'active-contact');
        document.getElementById('content').style = "border-top: 20px solid #d40e3f;";
    }

    clickExpertiseHandler() {
        this.loadContent('./assets/regions/content/expertise.html');
        this.updateStyles('expert', 'active-expert');
        document.getElementById('content').style = "border-top: 20px solid #1F85DE;";
    }

    clickResumeHandler() {
        this.loadContent('./assets/regions/content/resume.html', this.loadResumeData);
        this.updateStyles('resume', 'active-resume');
        document.getElementById('content').style = "border-top: 20px solid #00b300;";
    }

    loadContent(url, callback) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("content").innerHTML = this.responseText;
                if (callback) callback();
            }
        }
        xhr.onerror = function () {
            console.log("Data request error...");
        }
        xhr.send();
    }

    updateStyles(activeLink, color) {
        document.querySelectorAll('#region-1 span').forEach(link => {
            link.classList.remove('active'); // Remove 'active' class from all links
        });
        document.getElementById(activeLink).classList.add('active'); // Add 'active' class to the clicked link
        document.getElementById('content').style.borderTopColor = window.getComputedStyle(document.getElementById(activeLink)).backgroundColor;
    }

    loadResumeData() {
        const resumeContainer = document.querySelector(".accordion");

        fetch('http://localhost:8080/larrymayers.site/public/resume.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(position => {
                    const detailsElement = document.createElement("details");

                    const summaryElement = document.createElement("summary");
                    summaryElement.innerHTML = `<h5>${position.title} <span>${position.duration}</span></h5>`;
                    detailsElement.appendChild(summaryElement);

                    const detailContents = document.createElement("div");
                    detailContents.className = "detail-contents";

                    const companyElement = document.createElement("u");
                    companyElement.innerHTML = `<span class="company_name">${position.company}</span>`;
                    detailContents.appendChild(companyElement);

                    if (position.duties.length > 0) {
                        const dutiesElement = document.createElement("p");
                        const dutiesList = document.createElement("ul");
                        dutiesElement.innerHTML = "<h6>Duties</h6>";

                        position.duties.forEach(duty => {
                            const dutyListItem = document.createElement("li");
                            dutyListItem.className = "duty-contents";
                            dutyListItem.textContent = duty;
                            dutiesList.appendChild(dutyListItem);
                        });

                        dutiesElement.appendChild(dutiesList);
                        detailContents.appendChild(dutiesElement);
                    }

                    detailsElement.appendChild(detailContents);
                    resumeContainer.appendChild(detailsElement);
                });
            })
            .catch(error => console.error('Error fetching resume data:', error));
    }
}


class FormCache{

    static load(formId){
        const form = document.getElementById(formId);
        if(!form) throw new Error(`Form #${formId} Not Found!`);

        this.form = form;
        this.emailInput = form.querySelector('emailInput');
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
            topic: this.msgTopic.value.trim(),
            message: this.usrMsg.value.trim()
        };

        try {
            this.emailConfirm.innerHTML = "...Sending Message";
            this.emailConfirm.style.color = "#EE5552";

        const response = await fetch("post_message.php", {
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



new Links();

/* Code to load navigation and footer content as you have it already */
fetch("./assets/regions/nav.html").then(function(response) {
    return response.text(); 
}).then(function(string) {
    document.querySelector("header").innerHTML = string;
}).catch(function(err) {
    console.log('Fetch error occurred', err);
});         

fetch("./assets/regions/footer.html").then(function(response) {
    return response.text();
}).then(function(string) {
    document.querySelector("footer").innerHTML = string;
}).catch(function(err) {
    console.log('Fetch error occurred', err);
});



/* Designation of the CLASS used to validate our user input. */
class Validator {

    static REQUIRED = "REQUIRED";
    static MIN_LENGTH = "MIN_LENGTH";
    static NUMBER = "NUMBER";
    static MAX_LENGTH = "MAX_LENGTH";
    static DROP_BOX = "DROP_BOX";
    static EMAIL = "EMAIL";

static validate(value, flag, validatorValue){
    if(flag === this.REQUIRED){
        return value.trim().length > 0;
    }
    if(flag === this.MIN_LENGTH){
        return value.trim().length > validatorValue;
    }
    if(flag === this.MAX_LENGTH){
        return value.length < validatorValue;
    }
    if(flag === this.NUMBER){
        return isNaN(value) ;
    }
    if(flag === this.DROP_BOX){
        return value > 0;
    }
    if(flag === this.EMAIL){
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }
}


}


