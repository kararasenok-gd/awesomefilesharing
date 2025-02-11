function getVersion() {
    fetch('/api/sys/getVer.php')
        .then(response => response.json())
        .then(data => document.getElementById('version').textContent = `v${data.version}`);
}

getVersion();