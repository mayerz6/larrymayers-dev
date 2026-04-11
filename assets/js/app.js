document.addEventListener("DOMContentLoaded", () => {

  document.body.addEventListener("click", (e) => {
    const link = e.target.closest("a");

    if (!link) return;

    const url = new URL(link.href);

    // Only intercept internal links
    if (url.origin === window.location.origin) {
      e.preventDefault();
      navigate(url.pathname);
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
  navigate(window.location.pathname);
});