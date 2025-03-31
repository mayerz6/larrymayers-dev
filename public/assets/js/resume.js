document.addEventListener("DOMContentLoaded", function() {
    const resumeContainer = document.querySelector(".accordion");

    // AJAX request to fetch the resume data
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
                        // const dutyDiv = document.createElement("div");
                        const dutyListItem = document.createElement("li");
                        dutyListItem.className = "duty-contents";
                        dutyListItem.textContent = duty;
                        // dutiesElement.appendChild(dutyDiv);
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
});



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


