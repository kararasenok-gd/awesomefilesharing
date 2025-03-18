function onWindowSizeChange() {
    const w = window.innerWidth;
    const h = window.innerHeight;

    if (w <= 1600) { document.getElementsByClassName('leftbar')[0].style.display = 'none'; } else { document.getElementsByClassName('leftbar')[0].style.display = 'block'; }
}

window.addEventListener('resize', onWindowSizeChange);

function initMain() {
    onWindowSizeChange();
}

initMain();