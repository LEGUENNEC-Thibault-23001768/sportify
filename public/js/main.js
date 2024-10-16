fetch('footer.html')
  .then(response => response.text())
  .then(data => {
      document.getElementById('footer-placeholder').innerHTML = data;

      configureFooterLinks();
  })
  .catch(error => console.error('Erreur lors du chargement du footer :', error));

function configureFooterLinks() {
    const links = {
        'entreprise': 'entreprise.html',
        'a-propos': 'a-propos.html',
        'blog': 'blog.html',
        'changelog': 'changelog.html',
        'contact': 'contact.html',
        'partenaire': 'partenaire.html',
        'politique': 'politique.html'
    };

    for (const id in links) {
        const linkElement = document.querySelector(`#footer-placeholder #${id}`);
        if (linkElement) {
            linkElement.addEventListener('click', function() {
                checkPageExists(links[id]);
            });
        }
    }
}

function checkPageExists(pageUrl) {
    fetch(pageUrl, { method: 'HEAD' })
        .then(response => {
            if (response.ok) {
                window.location.href = pageUrl;
            } else {
                window.location.href = '404.html';
            }
        })
        .catch(() => {
            window.location.href = '404.html';
        });
}

document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        const page = this.getAttribute('data-page');
        checkPageExists(page);
    });
});
