const tabs = ['index', 'files', 'upload', 'settings'];

function loadTab(tab = null) {
    const content = document.getElementById('content');
    let style = document.getElementById('loaded-style');
    let script = document.getElementById('loaded-script');

    content.innerHTML = '<span class="spinner" style="position: absolute; top: 50%; left: 50%; width: 100px !important; height: 100px !important; border-width: 5px;"></span>';

    let isMainPage = false;

    console.log("Loading tab:", tab);

    if (tab === undefined || tab === null || tab === '' || !tabs.includes(tab)) {
        console.log('Setting tab to index');
        tab = 'index';
        isMainPage = true;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    history.replaceState(null, null, url.href);

    if (!isMainPage) {
        style.remove()
        style = document.createElement('link');
        style.id = 'loaded-style';
        style.rel = 'stylesheet';
        style.href = `./styles/${tab}.css`;
        document.head.appendChild(style);
    }

    fetch(`./templates/${tab}.html`)
        .then(response => response.text())
        .then(template => {
            content.innerHTML = template;

            if (!isMainPage) {
                script.remove()

                script = document.createElement('script');
                script.id = 'loaded-script';
                script.src = `./scripts/${tab}.js`;
                document.body.appendChild(script);
            } else {
                style.href = '';
                script.src = '';
            };
        });
}

window.addEventListener('load', () => {
    let tab = new URLSearchParams(window.location.search).get('tab') || 'index';
    loadTab(tab);
});