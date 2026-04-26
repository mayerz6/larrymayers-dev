


export function loadResumeData() {
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