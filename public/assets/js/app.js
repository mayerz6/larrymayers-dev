document.addEventListener("DOMContentLoaded", () => {
  const navToggle = document.querySelector(".nav-toggle");
  const navLinks = document.getElementById("primary-navigation");

  if (navToggle && navLinks) {
    navToggle.addEventListener("click", () => {
      const isOpen = navToggle.getAttribute("aria-expanded") === "true";

      navToggle.setAttribute("aria-expanded", String(!isOpen));
      navLinks.classList.toggle("is-open", !isOpen);
    });
  }

  document.body.addEventListener("click", (e) => {
    const link = e.target.closest("a");

    if (!link) return;
    if (link.getAttribute("href") === "#") return;

    const url = new URL(link.href);

    // Only intercept internal links
    if (url.origin === window.location.origin) {
      e.preventDefault();
      if (navToggle && navLinks) {
        navToggle.setAttribute("aria-expanded", "false");
        navLinks.classList.remove("is-open");
      }
      navigate(`${url.pathname}${url.search}`);
    }
  });

});

document.addEventListener("submit", async (e) => {
    const form = e.target;

    if(form.id === "contactForm") {
        e.preventDefault();
        const formData = new FormData(form);
        const resMsg = document.getElementById("formResMsg");

        try{
            const res = await fetch("/contact", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            const data = await res.json();
            // console.log("Form submission response:", data);
            resMsg.className = data.status === "success"
                ? "text-success"
                : "text-danger";
            resMsg.textContent = data.message;
            if(data.status === "success") {
                form.reset();
            }
            
        } catch(err) {
            console.error("Form submission failed:", err);
        }

       
    }
});

document.addEventListener("submit", async (e) => {
    const form = e.target;
    if(form.id === "loginForm") {
        e.preventDefault();
        const formData = new FormData(form);
        const resMsg = document.getElementById("loginResMsg");


        try{
            const res = await fetch("/login", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            const data = await res.json();
          
            if(data.status === "success") {
                resMsg.className = "text-success";
                resMsg.textContent = data.message;
                
                setTimeout(() => {
                    // navigate(data.redirect);
                    window.location.href = data.redirect;
                }, 500);
            } else {
                resMsg.className = "text-danger";
                resMsg.textContent = data.message;
            }
            
        } catch(err) {
            console.error("Form submission failed:", err);
        }
    }
});


document.addEventListener("submit", async (e) => {
    const form = e.target;
    if (form.id === "resumeForm") {
        e.preventDefault();
        const formData = new FormData(form);
        const resMsg = document.getElementById("resumeResMsg");

        try {
            const res = await fetch("/resume/create", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            const data = await res.json();
            if(data.status === "success") {
                resMsg.className = "text-success";
                resMsg.textContent = data.message;
                form.reset();
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    refreshResumeList();
                }

            } else {
                resMsg.className = "text-danger";
                resMsg.textContent = data.message;
            }
            
        } catch(err) {
            console.error("Form submission failed:", err);
        }
    }

});


document.addEventListener("submit", async (e) => {
    const form = e.target;
    if (form.id === "projectForm") {
        e.preventDefault();
        const formData = new FormData(form);
        const resMsg = document.getElementById("projectResMsg");
    
        try {
            const res = await fetch("/projects/create", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });
            const data = await res.json();
            if (data.status === "success") {
                resMsg.className = "text-success";
                resMsg.textContent = data.message;
                form.reset();
                 setTimeout(() => {
                    // navigate(data.redirect);
                    window.location.href = data.redirect;
                }, 500);
            } else {
                resMsg.className = "text-danger";
                resMsg.textContent = data.message;
            }

        } catch(err) {
            console.error("Form submission failed:", err);
        }
    }
});

document.addEventListener("submit", async (e) => {
    const form = e.target;
    if (form.id === "blogPostForm") {
        e.preventDefault();
        const resMsg = document.getElementById("blogResMsg");
        const submitButton = form.querySelector('button[type="submit"]');
        const formAction = new URL(form.action || "/blog/create", window.location.origin);

        if (formAction.pathname === "/blog/create" && !validateBlogCreateForm(form, resMsg)) {
            return;
        }

        const formData = new FormData(form);

        try {
            const res = await fetch(formAction.href, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            const data = await res.json();
            if (resMsg) {
                resMsg.className = data.status === "success"
                    ? "text-success"
                    : "text-danger";
                resMsg.textContent = data.message;
            }

            if (data.status === "success") {
                form.reset();
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                }
            }
        } catch(err) {
            console.error("Blog post submission failed:", err);
            if (resMsg) {
                resMsg.className = "text-danger";
                resMsg.textContent = "Unable to create blog post. Please try again.";
            }
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }
});

function validateBlogCreateForm(form, resMsg) {
    const titleInput = form.querySelector('[name="title"]');
    const contentInput = form.querySelector('[name="content"]');
    const title = titleInput ? titleInput.value.trim() : "";
    const content = contentInput ? contentInput.value.trim() : "";
    const missingFields = [];

    if (titleInput) {
        titleInput.setAttribute("aria-invalid", title === "" ? "true" : "false");
    }

    if (contentInput) {
        contentInput.setAttribute("aria-invalid", content === "" ? "true" : "false");
    }

    if (title === "") {
        missingFields.push("title");
    }

    if (content === "") {
        missingFields.push("content");
    }

    if (missingFields.length === 0) {
        if (resMsg) {
            resMsg.className = "";
            resMsg.textContent = "";
        }
        return true;
    }

    if (resMsg) {
        resMsg.className = "text-danger";
        resMsg.textContent = missingFields.length === 2
            ? "Please enter a title and content before submitting."
            : `Please enter ${missingFields[0]} before submitting.`;
    }

    if (title === "" && titleInput) {
        titleInput.focus();
    } else if (contentInput) {
        contentInput.focus();
    }

    return false;
}

document.addEventListener("input", (e) => {
    const field = e.target;
    const form = field.closest("#blogPostForm");

    if (!form || !field.matches('[name="title"], [name="content"]')) {
        return;
    }

    const formAction = new URL(form.action || "/blog/create", window.location.origin);
    if (formAction.pathname !== "/blog/create") {
        return;
    }

    field.setAttribute("aria-invalid", field.value.trim() === "" ? "true" : "false");
});


document.addEventListener("click", async (e) => {
    // if (!e.target.classList.contains("delete-resume-btn")) return;
    if (e.target.classList.contains("delete-resume-btn")) {
        await handleDelete(e);
        return;
    }

     // EDIT MODE
    if (e.target.classList.contains("edit-resume-btn")) {
        handleEdit(e);
        return;
    }

    // SAVE EDIT
    if (e.target.classList.contains("save-edit-btn")) {
        await handleSave(e);
        return;
    }

    if (e.target.classList.contains("cancel-edit-btn")) {
    const item = e.target.closest(".resume-item");
    item.innerHTML = item.dataset.original;
    return;
    }



});

document.addEventListener("click", async (e) => {
    if (e.target.id === "logoutForm") {
        try {
            const res = await fetch("/logout", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });
            const data = await res.json();
            if (data.status === "success") {
                window.location.href = data.redirect;
            }
        } catch (err) {
            console.error("Logout failed:", err);
        }
    } else { return; }
});

async function handleDelete(e) {
    e.preventDefault();

    const item = e.target.closest(".resume-item");
    if(!item) return;
    
    const id = Number(item.dataset.id);
    // 🧠 Step 1 — Confirm
    if (!confirm("Are you sure you want to delete this entry?")) return;

    // 🧠 Step 2 — Save backup (for rollback)
    const parent = item.parentElement;
    const nextSibling = item.nextElementSibling;
    
    item.style.opacity = "0.5";
    // 🧠 Step 3 — Optimistic remove
    item.remove();

    try {
        const formData = new FormData();
        formData.append("id", id);

        const res = await fetch("/resume/delete", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            body: formData
        });
        if (!res.ok) throw new Error("Server error");
        const data = await res.json();

        if (data.status !== "success") {
            throw new Error("Delete failed");
        }
        refreshResumeList();

    } catch (err) {
        console.error(err);

        // ❌ Rollback
        if (nextSibling) {
            parent.insertBefore(item, nextSibling);
        } else {
            parent.appendChild(item);
        }

        alert("Failed to delete. Please try again.");
    }
}


async function loadResume() {
    const resumeContainer = document.querySelector(".accordion");
    if (!resumeContainer) return;

    const res = await fetch("/resume", {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    });
    if(!res.ok) {
        console.error("Failed to fetch resume data");
        return;
    }
    const data = await res.json();

    if (data.status !== "success"){
        console.error("Failed to fetch resume data");
        return
    }
                data.forEach(position => {
                const detailsElement = document.createElement("details");
                const summaryElement = document.createElement("summary");
                
                summaryElement.innerHTML = `<h5>${position.title} <span>${position.start_year} - ${position.end_year}</span></h5>`;
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
    
}



async function handleSave(e) {
    e.preventDefault();

    const item = e.target.closest(".resume-item");
    const id = item.dataset.id;
    const newTitle = item.querySelector(".edit-title").value;

    try {
        const formData = new FormData();
        formData.append("id", id);
        formData.append("title", newTitle);

        const res = await fetch("/resume/update", {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            body: formData
        });

        const data = await res.json();

        if (data.status !== "success") throw new Error();

        // ✅ Reload clean UI
        refreshResumeList();

    } catch {
        alert("Update failed");
        item.innerHTML = item.dataset.original;
    }
}


function handleEdit(e) {
    const item = e.target.closest(".resume-item");

    if (!item.dataset.original) {
        item.dataset.original = item.innerHTML;
    }

    const title = item.querySelector("h4").textContent;

    item.innerHTML = `
        <input class="edit-title" value="${title}" />
        <button class="save-edit-btn">Save</button>
        <button class="cancel-edit-btn">Cancel</button>
    `;
}
// if (e.target.classList.contains("edit-resume-btn")) {
//     const item = e.target.closest(".resume-item");

//     const title = item.querySelector("h4").textContent;

//     item.innerHTML = `
//         <input class="edit-title" value="${title}" />
//         <button class="save-edit-btn">Save</button>
//         <button class="cancel-edit-btn">Cancel</button>
//     `;
// }

async function refreshResumeList() {
    const res = await fetch("/resume/manage", {
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    });

    if (!res.ok) {
        console.error("Failed to refresh list");
        return;
    }

    const html = await res.text();
    const container = document.getElementById("resume-manage");
    if(container) {
        container.innerHTML = html;
    }
}


document.addEventListener("click", async (e) => {
  if (e.target.matches(".delete-btn")) {
    const id = e.target.dataset.id;

    const res = await fetch("/messages/delete", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: `id=${id}`
    });

    const data = await res.json();

    if (data.status === "success") {
      e.target.closest(".message-item").remove();
    }
  }
});

async function navigate(path) {
  try {
    const res = await fetch(path, {
      headers: {
        "X-Requested-With": "XMLHttpRequest"
      }
    });

    const html = await res.text();

    document.getElementById("app").innerHTML = html;

    window.history.pushState({}, "", path);

  } catch (err) {
    console.error("Navigation failed:", err);
  }
}

window.addEventListener("popstate", () => {
  navigate(`${window.location.pathname}${window.location.search}`);
});
