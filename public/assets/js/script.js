console.log("Running!!!, We're online & Ready to Roll!");

// Resume/Projects accordion ("app drawer" reveal): native <details>/
// <summary> gives keyboard support, screen-reader semantics, and open/
// close state for free, but two things still need handling in JS.
//
// 1. Browsers snap content open/closed instantly -- there's no CSS-only
//    way to transition from hidden to an element's natural (auto) height
//    in every supported browser. Fixed by overriding the browser's
//    default "display: none" on closed content (done in main.css) and
//    animating between 0 and the content's real measured height here,
//    in response to the native "toggle" event <details> already fires.
//
// 2. This app is a hybrid MVC+SPA (see app.js's navigate()): every
//    internal link click replaces #app's entire innerHTML via fetch
//    instead of a real page load. A listener attached directly to a
//    specific <details> element at page-load time goes stale the moment
//    a visitor clicks any nav link and #app's content gets swapped out
//    from under it -- the new elements were never wired up. The fix is
//    delegation: ONE listener on `document`, in the capture phase
//    (required because "toggle" does not bubble), so it keeps working
//    no matter how many times the SPA layer replaces #app's content.
document.addEventListener(
  "toggle",
  (event) => {
    const entry = event.target;
    if (!(entry instanceof HTMLDetailsElement)) return;
    if (!entry.matches(".resume-entry, .project-entry")) return;

    const body = entry.querySelector(".resume-entry-body, .project-entry-body");
    if (!body) return;

    body.style.maxHeight = entry.open ? `${body.scrollHeight}px` : "0px";
  },
  true
);

// A resize (or a web font finishing its swap) can change how tall an
// already-open entry's content really is -- keep open entries fitted
// rather than letting them clip or leave a gap. Re-queried fresh on every
// resize, so this one has no staleness problem of its own.
window.addEventListener("resize", () => {
  document
    .querySelectorAll(".resume-entry[open] .resume-entry-body, .project-entry[open] .project-entry-body")
    .forEach((body) => {
      body.style.maxHeight = `${body.scrollHeight}px`;
    });
});
