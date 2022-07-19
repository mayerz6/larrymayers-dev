class Links{

    constructor(){

        /* Instantiate the elements of your NAV links */
        this.aboutLink = document.getElementById('about');
        this.expertLink = document.getElementById('expert');
        this.contactLink = document.getElementById('contact');  

        this.contactLink.addEventListener("click", this.clickContactHandler.bind(this));
        this.aboutLink.addEventListener("click", this.clickAboutHandler.bind(this));
        this.expertLink.addEventListener("click", this.clickExpertiseHandler.bind(this));
        
    }

    /* Simple Method to FETCH content for the ABOUT section of the page. */
    clickAboutHandler(){

        let xhr = new XMLHttpRequest();

        xhr.open('GET', './assets/regions/content/about.html', true);

        xhr.onreadystatechange = function(){
            if(this.readyState == 4 && this.status == 200){
            
                document.getElementById("content").innerHTML = this.responseText;
              
            }
        }

        xhr.onerror = function(){
            console.log("Data request error...");
        }
 
        xhr.send();
        document.getElementById('about').style = "color: #fff; background-color: #ff9300;";
        document.getElementById('contact').style = "color: #777; background-color: #fff;";
        document.getElementById('expert').style = "color: #777; background-color: #fff;";
        document.getElementById('content').style = "border-top: 20px solid #ff9300;";



       // console.clear();

    }

    /* Simple Method to FETCH content for the Contact section of the page. */
    clickContactHandler(){
        
        let xhr = new XMLHttpRequest();

        xhr.open('GET', './assets/regions/content/contact.html', true);

        xhr.onreadystatechange = function(){
            if(this.readyState == 4 && this.status == 200){
                document.getElementById("content").innerHTML = this.responseText;
                       new Form();  
                    //  Form.loadForm(); 
            }
        }

        xhr.onerror = function(){
            console.log("Data request error...");
        }
 
        xhr.send();
        document.getElementById('about').style = "color: #777; background-color: #fff;";
        document.getElementById('contact').style = "color: #fff; background-color: #d40e3f;";
        document.getElementById('expert').style = "color: #777; background-color: #fff;";
        document.getElementById('content').style = "border-top: 20px solid #d40e3f;";
    }

    clickExpertiseHandler(){
        
        let xhr = new XMLHttpRequest();

        xhr.open('GET', './assets/regions/content/expertise.html', true);

        xhr.onreadystatechange = function(){
            if(this.readyState == 4 && this.status == 200){
              
                document.getElementById("content").innerHTML = this.responseText;
             
            }
        }

        xhr.onerror = function(){
            console.log("Data request error...");
        }
 
        xhr.send();
        document.getElementById('about').style = "color: #777; background-color: #fff;";
        document.getElementById('contact').style = "color: #777; background-color: #fff;";
        document.getElementById('expert').style = "color: #fff; background-color: #1F85DE;";
        document.getElementById('content').style = "border-top: 20px solid #1F85DE;";
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

this.btnSubmit.addEventListener("click", () => {
    
    /* Must validate the user input before processing. */
    if(this.emailValid && this.msgValid && this.topicValid){

        const request = new XMLHttpRequest();

        request.onload = () => {

            this.emailConfirm.innerHTML = "<b>Email Successfully Sent!</b>";
            this.emailConfirm.style = "color: #2ecc71;";

      try {
       //   console.log(request.responseText);
         responseObject = JSON.parse(request.responseText);
          } catch(e) {
           console.error(e);
         }
                console.log('Contact Form Submitted!');
                console.log(requestData);
    
               this.formDataDestroy();
               // this.emailConfirm.opacity = 0;

        }
        
            let requestData = `email=${this.emailInput.value}`;
            requestData += `&msgTopic=${this.msgTopic.value}`;
            requestData += `&usrMsg=${this.usrMsg.value}`;
           
            
            request.open('post', './mail.php');
            request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            request.send(requestData);
            this.emailConfirm.innerHTML = "...Sending Message";
            this.emailConfirm.style = "color: #EE5552;";
              
             
    } else {
         
        this.emailConfirm.innerHTML = "<b>Please complete the form details above.</b>";
        this.emailConfirm.style = "color: #ff0000;";
     
    console.log("Message NOT Sent!");
 }

    });



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

    static processFormData(){
    
       
    }

}


new Links();

 /* Unique approach to inserting content within the site dynamically. */ 
    fetch("./assets/regions/nav.html").then(function(response){
                return response.text(); 
                }).then(function(string){
                  //  Load navigation region.
                  document.querySelector("header").innerHTML = string;
                }).catch(function(err){
                    console.log('Fetch error occurred', err);
                });         

 /* Unique approach to inserting content within the site dynamically.    */ 
    fetch("./assets/regions/footer.html").then(function(response){
            return response.text();
        }).then(function(string){
            // Load footer region.
            document.querySelector("footer").innerHTML = string;
        }).catch(function(err){
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



