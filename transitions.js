(() => {
  const DURATION_MS = 450;

  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (reduceMotion) {
    document.documentElement.classList.add("page-ready");
    return;
  }

  const isInternalPageLink = (anchor) => {
    if (!anchor || !anchor.href) return false;
    const url = new URL(anchor.href, window.location.href);
    return url.origin === window.location.origin && url.pathname !== window.location.pathname;
  };

  window.addEventListener("pageshow", () => {
    document.documentElement.classList.add("page-ready");
  });

  requestAnimationFrame(() => {
    document.documentElement.classList.add("page-ready");
  });

  document.addEventListener("click", (event) => {
    const anchor = event.target.closest("a");
    if (!anchor) return;
    if (anchor.target && anchor.target !== "_self") return;
    if (anchor.hasAttribute("download")) return;
    if (!isInternalPageLink(anchor)) return;

    event.preventDefault();
    document.documentElement.classList.remove("page-ready");
    document.documentElement.classList.add("page-leaving");

    window.setTimeout(() => {
      window.location.href = anchor.href;
    }, DURATION_MS);
  });
})();
