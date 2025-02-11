function onLoad() {
    var nsfwElements = document.querySelectorAll('.nsfw');

    nsfwElements.forEach(function(element) {
        element.addEventListener('click', function() {
            this.classList.toggle('unblurred');
        });
    });
}

onLoad();