const CACHE_NAME = 'v1';
const urlsToCache = [
    '/',
    '/index.php',
    '/style.css',
    '/dashboard.php',
    '/deconnexion.php',
    '/modif_candidature.php',
    '/ajouter_candidature.php',
    '/supprimer_candidature.php',
    '/offline.php',
    '/assets/data/candidatures.json',
    '/assets/data/utilisateurs.json',
    // Ajoutez ici tous les fichiers à mettre en cache
];

self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request).then(function(response) {
      if (response) {
        return response;
      }
      
      return fetch(event.request).then(function(response) {
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }
        
        return response;
      });
    }).catch(function() {
      return caches.match('offline.php');
    })
  );
});
