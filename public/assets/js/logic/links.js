import { loadModuleLogic } from '../app.js';

export class Links {

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
        this.loadContent('./assets/regions/content/about.html', 'about');
        this.updateStyles('about', 'active-about');
        document.getElementById('content').style = "border-top: 20px solid #ff9300;";

    }

    clickContactHandler() {
        this.loadContent('./assets/regions/content/contact.html', 'contact', () => {
            // Ensure the form logic is run AFTER the script is loaded and content is in the DOM
           loadModuleLogic('contact');
        });
        this.updateStyles('contact', 'active-contact');
        document.getElementById('content').style = "border-top: 20px solid #d40e3f;";
    }

    clickExpertiseHandler() {
        this.loadContent('./assets/regions/content/expertise.html', 'expertise');
        this.updateStyles('expert', 'active-expert');
        document.getElementById('content').style = "border-top: 20px solid #1F85DE;";
    }

    clickResumeHandler() {
        this.loadContent('./assets/regions/content/resume.html', 'resume', () => {
            loadModuleLogic('resume');
        });
        this.updateStyles('resume', 'active-resume');
        document.getElementById('content').style = "border-top: 20px solid #00b300;";
    }

    
// this.loadContent('./assets/regions/content/blog.html', 'blog');

    // loadContent(url, callback) {
    //     let xhr = new XMLHttpRequest();
    //     xhr.open('GET', url, true);
    //     xhr.onreadystatechange = function () {
    //         if (this.readyState === 4 && this.status === 200) {
    //             document.getElementById("content").innerHTML = this.responseText;
    //             if (callback) callback();
    //         }
    //     }
    //     xhr.onerror = function () {
    //         console.log("Data request error...");
    //     }
    //     xhr.send();
    // }

    loadContent(url, scriptName = null, callback = null) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("content").innerHTML = this.responseText;
                
                if (scriptName) {
                    const existingScript = document.querySelector(`script[data-dynamic="${scriptName}"]`);
                    if (existingScript) existingScript.remove();
    
                    const script = document.createElement("script");
                    script.src = `./assets/js/logic/${scriptName}.js`;
                    script.type = "module";
                    script.defer = true;
                    script.dataset.dynamic = scriptName;
                    script.onload = () => {
                        if (callback) callback();
                    };
                    document.body.appendChild(script);
                } else if (callback) {
                    callback();
                }
            }
        };
        xhr.onerror = function () {
            console.log("Data request error...");
        };
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

