function getFiles() {
    const files = document.getElementById('files');
    const totalFilesSize = document.getElementById('totalFilesSize');
    const totalFiles = document.getElementById('totalFiles');
    const nsfwFiles = document.getElementById('nsfwFiles');

    const authStatus = isAuthorized()

    if (!authStatus) {
        fetch(`./templates/noauth.html`)
        .then(response => response.text())
        .then(template => {
            document.getElementById('content').innerHTML = template;
        });
        return
    }

    fetch('./api/sys/getFiles.php').then(response => response.json()).then(data => {
        if (data.success) {
            files.innerHTML = '';

            let totalSize = 0;
            let totalFilesCount = 0;
            let nsfwFilesCount = 0;

            for (let i = 0; i < data.files.length; i++) {
                const file = data.files[i];

                const fileElement = document.createElement('div');
                fileElement.className = 'file';

                if (file.is_nsfw == 1) {
                    fileElement.classList.add('nsfw');
                }

                const backgroundImgSetting = localStorage.getItem('showPreviews');
                if (file.file_type.includes('image') && backgroundImgSetting == '1') {
                    fileElement.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../file/?name=${file.filename}&raw=1')`;
                    fileElement.style.backgroundSize = "cover";
                    fileElement.style.backgroundPosition = "center";
                }

                const fileName = document.createElement('p');
                fileName.textContent = file.filename;
                fileElement.appendChild(fileName);

                const fileSize = document.createElement('p');
                fileSize.textContent = decodeBytes(file.size);
                fileElement.appendChild(fileSize);

                const fileType = document.createElement('p');
                fileType.textContent = file.file_type;
                fileElement.appendChild(fileType);

                const fileDate = document.createElement('p');
                fileDate.textContent = decodeUnixTime(file.upload_date);
                fileElement.appendChild(fileDate);

                const fileActions = document.createElement('div');
                fileActions.className = 'file-actions';

                const deleteButton = document.createElement('a');
                deleteButton.href = 'javascript:void(0)';
                deleteButton.textContent = 'Удалить';
                deleteButton.className = 'delete';
                deleteButton.setAttribute('onclick', `deleteFile('${file.id}')`);
                fileActions.appendChild(deleteButton);

                fileActions.appendChild(document.createElement('br'));

                const viewButton = document.createElement('a');
                viewButton.href = 'javascript:void(0)';
                viewButton.textContent = 'Просмотр';
                viewButton.className = 'view';
                viewButton.setAttribute('onclick', `openFile('${file.filename}')`);
                fileActions.appendChild(viewButton);

                fileActions.appendChild(document.createElement('br'));

                const shortenButton = document.createElement('a');
                shortenButton.href = 'javascript:void(0)';
                shortenButton.textContent = 'Сократить ссылку';
                shortenButton.className = 'link';
                shortenButton.setAttribute('onclick', `shorten('${file.id}')`);
                fileActions.appendChild(shortenButton);

                fileElement.appendChild(fileActions);

                files.appendChild(fileElement);

                totalSize += file.size;
                totalFilesCount++;
                if (file.is_nsfw == 1) {
                    nsfwFilesCount++;
                }
            }

            totalFilesSize.textContent = decodeBytes(totalSize);
            totalFiles.textContent = totalFilesCount;
            nsfwFiles.textContent = nsfwFilesCount;
        }
    });
}

function deleteFile(id) {
    if (!confirm('Вы уверены, что хотите удалить этот файл?')) { return }

    const fd = new FormData();
    fd.append('id', id);

    fetch('./api/sys/deleteFile.php', {
        method: 'POST',
        body: fd
    }).then(response => response.json()).then(data => {
        if (data.success) {
            getFiles();
        }
    });
}

function openFile(filename) {
    window.open(`./file?name=${filename}`, '_blank');
}

function shorten(id) {
    if (!confirm('Вы уверены, что хотите сократить ссылку?')) { return }

    const fd = new FormData();
    fd.append('id', id);

    fetch('./api/sys/createShortLink.php', {
        method: 'POST',
        body: fd
    }).then(response => response.json()).then(data => {
        if (data.success) {
            copyToClipboard(data.link);
            alert(`Ссылка сокращена и скопирована в клипборд! ${data.link}`);
        }
    });
}


getFiles();