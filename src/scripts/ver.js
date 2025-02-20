function getVersion() {
    fetch('/api/sys/getVer.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('version').textContent = `v${data.version}`

            fetch('https://api.github.com/repos/kararasenok-gd/awesomefilesharing/commits?per_page=1')
                .then(response => response.json())
                .then(ghdata => {
                    document.getElementById('commit').textContent = ghdata[0].sha.substring(0, 7);
                    document.getElementById('commit').href = `https://github.com/kararasenok-gd/awesomefilesharing/commit/${ghdata[0].sha}`;
                })
        });
}

getVersion();