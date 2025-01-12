function configureFooterLinks() {
    document.querySelectorAll('.nav-link').forEach(link => {
         link.addEventListener('click', function() {
             const page = this.getAttribute('data-page');
             checkPageExists(page);
         });
     });

     document.querySelector('.logofoot').addEventListener('click', () => {
        checkPageExists('/');
     })
 }
 function checkPageExists(pageUrl) {
    fetch(pageUrl, { method: 'HEAD' })
        .then(response => {
            if (response.ok) {
                window.location.href = pageUrl;
            } else {
                window.location.href = '/404';
            }
        })
        .catch(() => {
            window.location.href = '/404';
        });
}
 
configureFooterLinks();