import { Links } from './logic/links.js';


document.addEventListener("DOMContentLoaded", () => {
    new Links(); // This should still bootstrap navigation
    loadModuleLogic("about"); // Default screen
});

export function loadModuleLogic(section) {
    const modules = {
        "about": null,
        "contact": () => import("./logic/contact.js").then(mod => mod.initContactForm()),
        "expert": null,
        "resume": () => import("./logic/resume.js").then(mod => mod.loadResumeData())
        // "blog": () => import("./logic/blog.js").then(mod => mod.initBlog())
    };

    if (modules[section]) modules[section]();
}


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




