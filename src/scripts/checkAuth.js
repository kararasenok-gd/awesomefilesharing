var userAuthorized = false;

function tmp___(response) {
    if (response.success) {
        accountName.innerHTML = response.user.username;
        userAuthorized = true;

        if (response.user.avatar) {
            accountAvatar.src = response.user.avatar;
        }
    } else {
        accountName.innerHTML = "Гость";
    }
}

function accCheck() {
    console.log('Running accCheck...');

    const accountName = document.getElementById("accountName");
    const accountAvatar = document.getElementById("accountAvatar");

    fetch("./api/sys/auth.php")
        .then(response => response.json())
        .then(response => tmp___(response))
        .catch(error => {
            console.error(error);
            accountName.innerHTML = "Гость";
        });
}

function getAuthInfo() {
    return fetch("./api/sys/auth.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Unknown error');
            }
            return data;
        });
}

function logout() {
    if (!isAuthorized()) {
        window.location.href = './login';
        return
    }

    if (!confirm('Вы уверены, что хотите выйти?')) { return }
    fetch("./api/sys/logout.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Unknown error');
            }
            window.location.reload()
        })
        .catch(error => {
            console.error(error);
            alert(error.message);
        });
}

function isAuthorized() {
    return userAuthorized;
}

accCheck();