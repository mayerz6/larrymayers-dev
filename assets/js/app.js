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