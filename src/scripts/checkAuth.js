function accCheck() {
    console.log('Running accCheck...');

    const accountName = document.getElementById("accountName");
    const accountAvatar = document.getElementById("accountAvatar");

    fetch("./api/sys/auth.php")
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                accountName.innerHTML = response.user.username;

                if (response.user.avatar) {
                    accountAvatar.src = response.user.avatar;
                }
            } else {
                window.location.href = "./login";
            }
        })
        .catch(error => {
            console.error(error);
            window.location.href = "./login";
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
            window.location.href = "./login";
        })
        .catch(error => {
            console.error(error);
            alert(error.message);
        });
}

accCheck();