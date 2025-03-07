function getFiles(sort = "id", order = "DESC") {
    const files = document.getElementById('files');
    const totalFilesSize = document.getElementById('totalFilesSize');
    const totalFiles = document.getElementById('totalFiles');
    const nsfwFiles = document.getElementById('nsfwFiles');

    fetch(`./api/sys/getFiles.php?sort=${sort}&order=${order}`).then(response => response.json()).then(data => {
        if (data.success) {
            files.innerHTML = '';

            let totalSize = 0;
            let totalFilesCount = 0;
            let nsfwFilesCount = 0;

            for (let i = 0; i < data.files.length; i++) {
                const file = data.files[i];

                const fileElement = document.createElement('div');
                fileElement.className = 'file';

                fileElement.setAttribute('data-tags', file.tags);

                if (file.is_nsfw == 1) {
                    if (localStorage.getItem("showNSFW") == '1' || localStorage.getItem("showNSFW") == null) {
                        fileElement.classList.add('nsfw');
                    } else {
                        continue;
                    }
                }

                const backgroundImgSetting = localStorage.getItem('showPreviews');
                if (file.file_type.includes('image') && backgroundImgSetting == '1') {
                    fileElement.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../file/?name=${file.filename}&raw=1&ignore=1')`;
                    fileElement.style.backgroundSize = "cover";
                    fileElement.style.backgroundPosition = "center";
                }

                const fileName = document.createElement('p');
                fileName.textContent = file.displayname;
                fileElement.appendChild(fileName);

                if (file.displayname != file.filename) {
                    const fileOriginal = document.createElement('small');
                    fileOriginal.textContent = `Оригинальное имя: ${file.filename}`;
                    fileElement.appendChild(fileOriginal);
                }

                const fileSize = document.createElement('p');
                fileSize.textContent = decodeBytes(file.size);
                fileElement.appendChild(fileSize);

                const fileType = document.createElement('p');
                fileType.textContent = file.file_type;
                fileElement.appendChild(fileType);

                const fileDate = document.createElement('p');
                fileDate.textContent = decodeUnixTime(file.upload_date);
                fileElement.appendChild(fileDate);

                const fileViews = document.createElement('p');
                fileViews.textContent = `Просмотры: ${file.views} | Raw: ${file.views_raw} | Всего: ${file.views_raw + file.views}`;
                fileElement.appendChild(fileViews);

                const fileActions = document.createElement('div');
                fileActions.className = 'file-actions';

                const modalContentTemp = document.createElement('div');
    
                const modal_buttons = document.createElement('div');

                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Удалить';
                deleteButton.className = 'delete';
                deleteButton.setAttribute('onclick', `deleteFile('${file.id}')`);
                modal_buttons.appendChild(deleteButton);

                const viewButton = document.createElement('button');
                viewButton.textContent = 'Просмотр';
                viewButton.className = 'view';
                viewButton.setAttribute('onclick', `openFile('${file.filename}')`);
                modal_buttons.appendChild(viewButton);

                const shortenButton = document.createElement('button');
                shortenButton.textContent = 'Сократить ссылку';
                shortenButton.className = 'link';
                shortenButton.setAttribute('onclick', `shorten('${file.id}')`);
                modal_buttons.appendChild(shortenButton);

                const editDitailsButton = document.createElement('button');
                editDitailsButton.textContent = 'Редактировать';
                editDitailsButton.className = 'edit';
                editDitailsButton.setAttribute('onclick', `editFile('${file.id}')`);
                modal_buttons.appendChild(editDitailsButton);

                modalContentTemp.appendChild(modal_buttons);

                if (file.file_type.includes('image')) {
                    const img = document.createElement('img');
                    img.src = `../file/?name=${file.filename}&raw=1&ignore=1`;
                    modalContentTemp.appendChild(img);
                } else if (file.file_type.includes('video')) {
                    const video = document.createElement('video');
                    video.src = `../file/?name=${file.filename}&raw=1&ignore=1`;
                    video.controls = true;
                    modalContentTemp.appendChild(video);
                } else if (file.file_type.includes('audio')) {
                    const audio = document.createElement('audio');
                    audio.src = `../file/?name=${file.filename}&raw=1&ignore=1`;
                    audio.controls = true;
                    modalContentTemp.appendChild(audio);
                } else {
                    const pre = document.createElement('pre');
                    pre.textContent = "Тип файла не поддерживается";
                    modalContentTemp.appendChild(pre);
                }

                const openModal = document.createElement('a');
                openModal.href = 'javascript:void(0)';
                openModal.textContent = 'Подробнее';
                openModal.onclick = () => {
                    showModal("Подробности файла", modalContentTemp.innerHTML, { closeButton: true }, `modal-${file.id}`);
                }


                fileElement.appendChild(openModal);

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
            hideModal(document.getElementById(`modal-${id}`));
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

function editFile(id) {
    fetch(`./api/sys/getFileInfo.php?id=${id}`).then(response => response.json()).then(data => {
        if (data.success) {
            const modalContentTemp = document.createElement('div');
            
            const form = document.createElement('form');
            form.id = 'editForm';

            const label = document.createElement('label');
            label.textContent = 'Название: ';
            form.appendChild(label);

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'displayname';
            input.setAttribute('value', data.data.displayname);
            form.appendChild(input);
            
            form.appendChild(document.createElement('br'));

            const label2 = document.createElement('label');
            label2.textContent = 'NSFW: ';
            form.appendChild(label2);

            const input2 = document.createElement('input');
            input2.type = 'checkbox';
            input2.name = 'is_nsfw';
            if (data.data.is_nsfw == 1) {
                input2.setAttribute('checked', 'checked');
            }
            form.appendChild(input2);

            console.log(data.data.is_nsfw == 1)

            form.appendChild(document.createElement('br'));

            const label3 = document.createElement('label');
            label3.textContent = 'Теги (разделяются символом ";"): ';
            form.appendChild(label3);

            const input3 = document.createElement('input');
            input3.type = 'text';
            input3.name = 'tags';
            input3.setAttribute('value', data.data.tags);
            form.appendChild(input3);

            modalContentTemp.appendChild(form);

            const modal_buttons = document.createElement('button');
            modal_buttons.textContent = 'Сохранить';
            modal_buttons.className = 'save';
            modal_buttons.setAttribute('onclick', `saveFile('${data.data.id}', '${data.data.filename}')`);
            modalContentTemp.appendChild(modal_buttons);

            showModal("Редактирование файла", modalContentTemp.innerHTML, { closeButton: true }, `edit-modal-${data.data.id}`);
        }
    });
};

function saveFile(id, altDisplayName = 'Не именован') {
    const fd = new FormData(document.getElementById('editForm'));

    let displayname = document.getElementById('editForm').displayname.value;
    if (displayname == '' || displayname == null) {
        displayname = altDisplayName
    }

    fd.append('displayname', displayname);
    fd.append('is_nsfw', document.getElementById('editForm').is_nsfw.checked ? 1 : 0);
    fd.append('tags', document.getElementById('editForm').tags.value);

    fetch(`./api/sys/editFile.php?id=${id}`, {
        method: 'POST',
        body: fd
    }).then(response => response.json()).then(data => {
        if (data.success) {
            hideModal(document.getElementById(`edit-modal-${id}`));
            getFiles();
        }
    })
}

function appendSortParams() {
    const sort = document.getElementById('sort').value;
    const order = document.getElementById('order').value;
    
    getFiles(sort, order);
}

const tagsSearch = document.getElementById('searchByTagsCount');

function fetchTags(tag) {
    let count = 0;
    const elements = document.getElementsByClassName('file');
    for (let i = 0; i < elements.length; i++) {
        const element = elements[i];
        if (element.getAttribute('data-tags') != null) {
            const tags = element.getAttribute('data-tags').split(';');
            if (tags.includes(tag)) {
                element.classList.add('highlight');

                if (localStorage.getItem('hideSearch') == '1') { element.style.display = 'block'; }

                count++;
            } else {
                if (localStorage.getItem('hideSearch') != '1') {
                    element.classList.remove('highlight');
                } else {
                    element.style.display = 'none';
                }
            }
        }
    }

    tagsSearch.textContent = count;
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

    getFiles();
}

function searchByTags() {
    let search = document.getElementById('searchByTags').value;

    const elements = document.getElementsByClassName('file');
    if (search == '') {
        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            element.classList.remove('highlight');
            element.classList.remove('can-highlight');

            if (localStorage.getItem('hideSearch') == '1') { element.style.display = 'block'; }

            tagsSearch.textContent = `Все`;
        }
        return;
    } else {
        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            element.classList.add('can-highlight');
        }
    }

    fetchTags(search);
}

document.getElementById('searchByTags').addEventListener('input', searchByTags);


init();