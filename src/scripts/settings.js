function changePassword() {
    var oldPassword = document.getElementById('oldPassword').value;
    var newPassword = document.getElementById('newPassword').value;

    const fd = new FormData();
    fd.append('oldPasswd', oldPassword);
    fd.append('newPasswd', newPassword);

    fetch('./api/sys/changePasswd.php', {
        method: 'POST',
        body: fd
    }).then(response => response.json()).then(data => {
        if (data.success) {
            alert('Пароль успешно изменен!');
        } else {
            alert(data.error);
        }
    });
}

function deleteAccount() {
    const warns = [
        'Вы уверены, что хотите удалить свой аккаунт?\n\nЭто удалит безвозвратно все файлы',
        'Точно?',
        '100%?',
        'Абсолютно уверен?',
        'Уверен?',
        'Последнее предупреждение\n\nТЫ УВЕРЕН?'
    ];
    var currIndex = 0;

    while (currIndex < warns.length) {
        if (!confirm(warns[currIndex])) {
            return;
        }
        currIndex++;
    }

    const fd = new FormData();
    fd.append('password', document.getElementById('deletePassword').value);

    fetch('./api/sys/deleteAcc.php', {
        method: 'POST',
        body: fd
    }).then(response => response.json()).then(data => {
        if (data.success) {
            alert('Аккаунт успешно удален!');
            window.location.href = './login';
        } else {
            alert(data.error);
        }
    });
}

function togglePreview() {
    const showPreviews = document.getElementById('showPreviews').checked;
    localStorage.setItem('showPreviews', showPreviews? 1 : 0);
}

function toggleNSFW() {
    const showNSFW = document.getElementById('showNSFW').checked;
    localStorage.setItem('showNSFW', showNSFW? 1 : 0);
}

function togglehideSearch() {
    const hideSearch = document.getElementById('hideSearch').checked;
    localStorage.setItem('hideSearch', hideSearch? 1 : 0);
}

function init() {
    const authStatus = isAuthorized()

    if (!authStatus) {
        fetch(`./templates/noauth.html`)
        .then(response => response.text())
        .then(template => {
            document.getElementById('content').innerHTML = template;
        });
        return
    }

    const showPreviews = localStorage.getItem('showPreviews');
    if (showPreviews == '1') {
        document.getElementById('showPreviews').checked = true;
    }

    const showNSFW = localStorage.getItem('showNSFW');
    if (showNSFW == '1' || showNSFW == null) {
        document.getElementById('showNSFW').checked = true;
    }

    const hideSearch = localStorage.getItem('hideSearch');
    if (hideSearch == '1') {
        document.getElementById('hideSearch').checked = true;
    }
}

document.getElementById('changePassword').addEventListener('click', changePassword);
document.getElementById('deleteAccount').addEventListener('click', deleteAccount);
document.getElementById('showPreviews').addEventListener('change', togglePreview);
document.getElementById('showNSFW').addEventListener('change', toggleNSFW);
document.getElementById('hideSearch').addEventListener('change', togglehideSearch);

init();