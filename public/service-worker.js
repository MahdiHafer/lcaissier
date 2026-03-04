const CACHE_NAME = "pwa-cache-v1";
const urlsToCache = [
    "/", // Assurez-vous que cela pointe vers la bonne route
    "/WabApp/public/login",
    "/WabApp/public/style.css",
    "/WabApp/public/iconapp-192x192.png",
    "/WabApp/public/iconapp-512x512.png"
];

// Installation du Service Worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log("Ouverture du cache");
                return cache.addAll(urlsToCache);
            })
            .catch((error) => {
                console.error("Erreur lors de l'ajout au cache:", error);
            })
    );
});

// Activation et nettoyage des anciens caches
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log("Suppression du cache obsolète:", cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

// Interception des requêtes
self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});
