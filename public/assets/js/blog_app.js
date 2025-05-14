
//  document.addEventListener("DOMContentLoaded", () => {
    window.addEventListener("load", () => {
        
        BlogContentManager.load();
        FormCache.load("blogForm");
        Validator.init();

     });
    

class Links {

    constructor() {
        /* Instantiate the elements of your NAV links */
        this.createLink = document.getElementById('create');
        this.updateLink = document.getElementById('update');
        this.deleteLink = document.getElementById('delete');  

        this.createLink.addEventListener("click", this.clickCreateHandler.bind(this));
        this.updateLink.addEventListener("click", this.clickUpdateHandler.bind(this));
        this.deleteLink.addEventListener("click", this.clickDeleteHandler.bind(this));
    }

    clickCreateHandler() {
        this.loadContent('blog_man.php?func=create', () => new Form());
        this.updateStyles('create', 'active-create');
        document.getElementById('content').style = "border-top: 20px solid #ff9300;";

    }

    clickUpdateHandler() {
        this.loadContent('blog.html?func=update', () => new Form());
        this.updateStyles('update', 'active-update');
        document.getElementById('content').style = "border-top: 20px solid #d40e3f;";
    }

    clickDeleteHandler() {
        this.loadContent('blog.html?func=delete');
        this.updateStyles('delete', 'active-delete');
        document.getElementById('content').style = "border-top: 20px solid #1F85DE;";
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
}

new Links();

class BlogContentManager {

    static load(){

        this.showLoading();
        
        fetch('./assets/posts/fetch_blog_posts.php')
        .then(response => response.json())
        .then(data => {
            const contentScreen = document.getElementById('content_screen');
            contentScreen.innerHTML = '';
    
            if (data.error) {
                contentScreen.innerHTML = `<p>${data.error}</p>`;
            } else {
                data.forEach(post => {
                    const postDiv = document.createElement('div');
                    postDiv.classList.add('blog_post');
    
                    postDiv.innerHTML = `
                        <h2>${post.title}</h2>
                        <span><strong>Author:</strong> ${post.author}</span> | <span><strong>Date:</strong> ${post.date}</span>
                        <br><br>
                        <p class="post-content">${post.contents.replace(/\n/g, '<br>')}</p>
                    `;
    
                    contentScreen.appendChild(postDiv);
                });
            }
    
        })
        .catch(error => {
            document.getElementById('content_screen').innerHTML = `<p>Error loading blog posts: ${error}</p>`;
        });
        
    }

    static showLoading() {
        const contentScreen = document.getElementById('content_screen');
        contentScreen.innerHTML = `<p style="color: gray;">⏳ Loading blog posts...</p>`;
    }
}

class BlogFormManager {
    static submit() {
        const titleValid = Validator.validateTitle();
        const authorValid = Validator.validateAuthor();
        const contentValid = Validator.validateContents();

        if (titleValid && authorValid && contentValid) {
            const data = {
                title: FormCache.title.value.trim(),
                author: FormCache.author.value.trim(),
                contents: FormCache.contents.value.trim()
            };

            // BlogFormManager.showSuccess("✔️ Form ready to submit.");

            fetch('./blog_post_insert.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    FormCache.messageDiv.textContent = "🎉 Blog post submitted successfully!";
                    FormCache.messageDiv.style.color = "green";
        
                    FormCache.successBanner.textContent = "✅ Post saved to database.";
                    FormCache.successBanner.style.display = "block";
                    FormCache.errorBanner.style.display = "none";
        
                    // BlogFormManager.resetForm();
                     // 👇 Delay for 2.5s before refreshing
                    setTimeout(() => {
                        
                        BlogFormManager.resetForm();
                        BlogContentManager.load();
                    }, 2500);

                } else {
                    throw new Error(result.error || "Unknown server error.");
                }
            })
            .catch(error => {
                FormCache.errorBanner.textContent = `⚠️ Submission failed: ${error.message}`;
                FormCache.errorBanner.style.display = "block";
                FormCache.successBanner.style.display = "none";
        
                console.error("Form submission error:", error);
            });
        
            // submit via fetch()...
           
    
            console.log("Submitting: ", data);
        } else {
            FormCache.messageDiv.textContent = "❌ Fix form errors.";
            FormCache.messageDiv.style.color = "red";
        }
        
            // FormCache.messageDiv.textContent = "✔️ Form ready to submit.";
            // FormCache.messageDiv.style.color = "green";

    }

    static showSuccess(message) {
        FormCache.messageDiv.textContent = message;
        FormCache.messageDiv.style.color = "green";
        FormCache.successBanner.textContent = "✅ Form input fields are valid based on backend testing 👌🏽";
        FormCache.successBanner.style.display = "block";
        FormCache.errorBanner.style.display = "none";
    }

    static showError(message) {
        FormCache.messageDiv.textContent = message;
        FormCache.messageDiv.style.color = "red";
        // FormCache.errorBanner.textContent = message;
        // FormCache.errorBanner.style.display = "block";
        FormCache.successBanner.style.display = "none";
    }

    static resetForm() {
        FormCache.title.value = "";
        FormCache.author.value = "";
        FormCache.contents.value = "";

        FormCache.titleError.textContent = "";
        FormCache.authorError.textContent = "";
        FormCache.contentsError.textContent = "";

        FormCache.messageDiv.textContent = "";
        FormCache.successBanner.style.display = "none";
        FormCache.errorBanner.style.display = "none";

        /* Fetch updated post details */ 
        
        
    }

}


class FormCache {
    static load(formId){
        const form = document.getElementById(formId);
        if(!form) throw new Error(`Form #${formId} Not Found!`);

        /* Assignment of CLASS attributes */
        this.form = form;
        this.title = form.querySelector('#title');
        this.titleError = form.querySelector('#btErrorMsg'); /* Blog Title (bt) Error Message */

        this.author = form.querySelector('#author');
        this.authorError = form.querySelector('#baErrorMsg'); /* Blog Author (ba) Error Message */

        this.contents = form.querySelector('#contents');
        this.contentsError = form.querySelector('#bcErrorMsg');

        this.messageDiv = document.getElementById('message');
        this.successBanner = document.getElementById('formSuccessBanner');
        this.errorBanner = document.getElementById('formErrorBanner');


    }


}

class Validator { 
    static init(){ 

        FormCache.title.addEventListener("blur", () => { this.validateTitle(); this.checkFormReady(); });
        FormCache.author.addEventListener("blur", () => { this.validateAuthor(); this.checkFormReady(); });
        FormCache.contents.addEventListener("blur", () => { this.validateContents(); this.checkFormReady(); });

        FormCache.form.addEventListener("submit", (e) => { 
            
            e.preventDefault(); 
            BlogFormManager.submit();
            
        });
        
    }

    static checkFormReady() {
        const titleValid = this.validateTitle();
        const authorValid = this.validateAuthor();
        const contentValid = this.validateContents();

        if (titleValid && authorValid && contentValid) {
            BlogFormManager.showSuccess("✔️ Form ready to submit.");
        } else {
            FormCache.successBanner.style.display = "none";
            FormCache.messageDiv.textContent = "";
        }
    }

    static validateTitle(){
        const value = FormCache.title.value.trim();
        if(value === ""){
            FormCache.titleError.textContent = "Title is required.";
            FormCache.titleError.style.color = 'red';
            return false;
        }
        FormCache.titleError.textContent = "";
        return true;
    }

    static validateAuthor(){
        const value = FormCache.author.value.trim();
        if(value === ""){
            FormCache.authorError.textContent = "Author is required.";
            FormCache.authorError.style.color = 'red';
            return false;
        }
        FormCache.authorError.textContent = "";
        return true;
    }

    static validateContents(){
        const value = FormCache.contents.value.trim();
        if(value.length < 10){
            FormCache.contentsError.textContent = "Content must be at least 10 characters in length";
            FormCache.contentsError.style.color = 'red';
            return false;
        }
        FormCache.contentsError.textContent = "";
        return true;
    }
 } 

