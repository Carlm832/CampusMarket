const CACHE_VERSION = "campusmarket-v1";
const OFFLINE_URL = "/campusmarket/public/offline.html";

const CORE_ASSETS = [
  "/campusmarket/",
  "/campusmarket/index.php",
  "/campusmarket/manifest.webmanifest",
  "/campusmarket/public/css/style.css",
  "/campusmarket/public/js/theme.js",
  "/campusmarket/public/js/pwa.js",
  "/campusmarket/public/images/logo.png",
  OFFLINE_URL
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION).then((cache) => cache.addAll(CORE_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE_VERSION)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  if (event.request.method !== "GET") {
    return;
  }

  const requestUrl = new URL(event.request.url);
  if (!requestUrl.pathname.startsWith("/campusmarket/")) {
    return;
  }

  const isHtmlRequest = event.request.mode === "navigate";

  if (isHtmlRequest) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          const responseClone = response.clone();
          caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, responseClone));
          return response;
        })
        .catch(() => caches.match(event.request).then((cached) => cached || caches.match(OFFLINE_URL)))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) {
        return cached;
      }
      return fetch(event.request).then((response) => {
        if (response && response.status === 200 && response.type === "basic") {
          const responseClone = response.clone();
          caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, responseClone));
        }
        return response;
      });
    })
  );
});
