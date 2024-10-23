document.addEventListener('DOMContentLoaded', function() {
    var links = document.querySelectorAll('.sidebar ul li a');

    links.forEach(function(link) {
        link.addEventListener('click', function() {
            links.forEach(function(link) {
                link.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
});
