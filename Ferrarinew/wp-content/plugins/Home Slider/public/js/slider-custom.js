document.addEventListener("DOMContentLoaded", function () {
    var el = document.querySelector('.splide');
    if (el) {
        var splide = new Splide(el, {
            arrowPath: 'M10 20 L30 20 L25 15 M30 20 L25 25',
            arrows   : false,  // attenzione: qui era una stringa, deve essere booleano
            type     : 'loop',
            perPage  : 1,
            autoplay : true,
            interval : 13000,
            speed    : 8000,
        });

        splide.mount();
    } else {
        console.warn('Nessun elemento con classe .splide trovato nel DOM');
    }
});
