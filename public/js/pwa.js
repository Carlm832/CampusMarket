(function () {
  if (!("serviceWorker" in navigator)) {
    return;
  }

  window.addEventListener("load", function () {
    navigator.serviceWorker
      .register("/campusmarket/sw.js")
      .catch(function (error) {
        console.error("Service worker registration failed:", error);
      });
  });
})();
