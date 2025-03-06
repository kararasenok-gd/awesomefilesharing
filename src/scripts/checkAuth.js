var userAuthorized = false;
const accountName = document.getElementById("accountName");
const accountAvatar = document.getElementById("accountAvatar");

function fetchToken() {
    const cookies = document.cookie.split(';');

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i].trim();

        if (cookie.startsWith('LongLifeToken=')) {
            const token = cookie.substring('LongLifeToken='.length);

            const fd = new FormData();
            fd.append('token', token);

            fetch("./api/sys/login.php", {
                method: "POST",
                body: fd
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        accountName.innerHTML = response.user.username;
                        userAuthorized = true;

                        if (response.user.avatar) {
                            accountAvatar.src = response.user.avatar;
                        }
                    }
                })
                .catch(error => {
                    console.error(error);
                    accountName.innerHTML = "Гость";
                });
        } else {
            accountName.innerHTML = "Гость";
        }
    }
}

function tmp___(response) {
    if (response.success) {
        accountName.innerHTML = response.user.username;
        userAuthorized = true;

        if (response.user.avatar) {
            accountAvatar.src = response.user.avatar;
        }
    } else {
        fetchToken();
    }
}

function accCheck() {
    console.log('Running accCheck...');

    fetch("./api/sys/auth.php")
        .then(response => response.json())
        .then(response => tmp___(response))
        .catch(error => {
            console.error(error);
            fetchToken();
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

    const cookies = document.cookie.split(';');

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i].trim();

        if (cookie.startsWith('LongLifeToken=')) {
            const cookieName = cookie.split('=')[0];
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
        }
    }

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