function enableUploadButton(token) {
    captchaToken = token;
    uploadButton.disabled = false;
}

function resetCaptcha() {
    captchaToken = "";
    uploadButton.disabled = true;

    hcaptcha.reset();
}

function updateTable(files) {
    const filesTable = document.getElementById('filesTable');

    filesTable.innerHTML = '';

    const initTr = document.createElement('tr');
    const initTh = document.createElement('th');
    initTh.textContent = 'No';
    initTr.appendChild(initTh);
    const initTh1 = document.createElement('th');
    initTh1.textContent = 'Название';
    initTr.appendChild(initTh1);
    const initTh2 = document.createElement('th');
    initTh2.textContent = 'Параметры';
    initTr.appendChild(initTh2);
    const initTh3 = document.createElement('th');
    initTh3.textContent = 'Размер';
    initTr.appendChild(initTh3);
    const initTh4 = document.createElement('th');
    initTh4.textContent = 'Тип';
    initTr.appendChild(initTh4);
    const initTh5 = document.createElement('th');
    initTh5.textContent = 'Загружено?';
    initTr.appendChild(initTh5);

    filesTable.appendChild(initTr);

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        const tr = document.createElement('tr');
        tr.setAttribute('data-filename', file.name);
        tr.setAttribute('data-tags', '');
        tr.setAttribute('data-nsfw', false);
        
        const numCell = document.createElement('td');
        numCell.textContent = i + 1;
        tr.appendChild(numCell);

        const nameCell = document.createElement('td');
        const fileTempURL = URL.createObjectURL(file);
        nameCell.innerHTML = `<a href="${fileTempURL}" target="_blank">${file.name}</a>`;
        tr.appendChild(nameCell);

        const paramsCell = document.createElement('td');
        const paramsButton = document.createElement('button');
        paramsButton.textContent = 'Параметры';

        function paramsCellClickEvent() {
            const temp__modalContent = document.createElement('div');

            const filename = document.createElement('strong');
            filename.textContent = `Имя файла: ${file.name}`;

            const nsfwCheckbox = document.createElement('input');
            nsfwCheckbox.id = `nsfw-${file.name}`;
            nsfwCheckbox.type = 'checkbox';
            
            function onNsfwChange(event) {
                const checked = event.target.checked;
                tr.setAttribute('data-nsfw', checked);
            }
            nsfwCheckbox.addEventListener('change', onNsfwChange);

            const nsfwLabel = document.createElement('label');
            nsfwLabel.htmlFor = nsfwCheckbox.id;
            nsfwLabel.textContent = 'NSFW: ';
            nsfwLabel.appendChild(nsfwCheckbox);

            const tagsInput = document.createElement('input');
            tagsInput.type = 'text';
            tagsInput.name = 'tags';
            tagsInput.placeholder = 'Теги через ;';

            function onTagsChange(event) {
                tr.setAttribute('data-tags', event.target.value);
            }
            tagsInput.addEventListener('input', onTagsChange);

            const tagsLabel = document.createElement('label');
            tagsLabel.htmlFor = tagsInput.id;
            tagsLabel.textContent = 'Теги: ';
            tagsLabel.appendChild(tagsInput);

            const updateDataButton = document.createElement('button');
            updateDataButton.textContent = 'Обновить данные';
            updateDataButton.addEventListener('click', () => {
                tagsInput.setAttribute('value', tr.getAttribute('data-tags'));
                nsfwCheckbox.checked = tr.getAttribute('data-nsfw') == 'true';
            })

            temp__modalContent.appendChild(filename);
            temp__modalContent.appendChild(document.createElement('br'));
            temp__modalContent.appendChild(nsfwLabel);
            temp__modalContent.appendChild(document.createElement('br'));
            temp__modalContent.appendChild(tagsLabel);
            temp__modalContent.appendChild(document.createElement('br'));
            temp__modalContent.appendChild(updateDataButton);

            showModal('Параметры', temp__modalContent, {}, `params-${file.name}`);
        }
        paramsButton.addEventListener('click', paramsCellClickEvent);
        paramsCell.appendChild(paramsButton);
        tr.appendChild(paramsCell);

        const sizeCell = document.createElement('td');
        sizeCell.textContent = decodeBytes(file.size);
        tr.appendChild(sizeCell);

        const typeCell = document.createElement('td');
        typeCell.textContent = file.type;
        tr.appendChild(typeCell);

        const uploadedCell = document.createElement('td');
        uploadedCell.textContent = "❌ Не загружено";
        uploadedCell.id = `uploaded-${file.name}`;
        tr.appendChild(uploadedCell);

        filesTable.appendChild(tr);
    }
}

function uploadFiles() {
    const fileInput = document.getElementById('fileInput');
    const uploadResults = document.getElementById('uploadResults');

    let captchaToken = "";

    const files = fileInput.files;

    if (files.length === 0) {
        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const filedata = document.querySelector(`tr[data-filename="${file.name}"]`);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('isNSFW', filedata.getAttribute('data-nsfw') == 'true' ? 1 : 0);
        formData.append('hcaptcha', captchaToken);
        formData.append('tags', filedata.getAttribute('data-tags'));

        const uploadedElement = document.getElementById(`uploaded-${file.name}`);
        uploadedElement.innerHTML = '';

        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        
        const progressText = document.createElement('div');
        progressText.className = 'progress-text';
        progressText.textContent = '0%';

        progressContainer.appendChild(progressBar);
        progressContainer.appendChild(progressText);
        uploadedElement.appendChild(progressContainer);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', './api/sys/upload.php', true);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressText.textContent = percent + '%';
            }
        });

        xhr.onload = function() {
            progressContainer.remove();
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        const p = document.createElement('p');
                        p.textContent = `Файл ${file.name} успешно загружен! `;
                        const a = document.createElement('a');
                        a.href = `https://awesomefilesharing.rf.gd/file/?name=${data.filename}`;
                        a.innerHTML = `https://awesomefilesharing.rf.gd/file/?name=${data.filename}`;
                        a.target = '_blank';
                        p.appendChild(a);
                        uploadResults.appendChild(p);
                        uploadedElement.textContent = "✅ Загружено";
                    } else {
                        uploadedElement.textContent = "❌ Ошибка загрузки";
                        const p = document.createElement('p');
                        p.textContent = `Ошибка загрузки ${file.name}: ${data.error}`;
                        uploadResults.appendChild(p);
                    }
                } catch (e) {
                    uploadedElement.textContent = "❌ Ошибка обработки ответа";
                    const p = document.createElement('p');
                    p.textContent = `Ошибка обработки ответа для ${file.name}: ${e}`;
                    uploadResults.appendChild(p);
                }
            } else {
                uploadedElement.textContent = "❌ Ошибка HTTP";
                const p = document.createElement('p');
                p.textContent = `Ошибка HTTP ${xhr.status} при загрузке ${file.name}`;
                uploadResults.appendChild(p);
            }
        };

        xhr.onerror = function() {
            progressContainer.remove();
            uploadedElement.textContent = "❌";
            const p = document.createElement('p');
            p.textContent = `Сетевая ошибка при загрузке ${file.name}`;
            uploadResults.appendChild(p);
        };

        xhr.send(formData);
    }

    fileInput.value = '';
}


function updateFilesProgress() {
    fetch('./api/sys/getSize.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const progressCount = document.getElementById('progressCount');
            const progressMax = document.getElementById('progressMax');
            const progressTotal = document.getElementById('progressTotal');
            const progress = document.getElementById('progressBar');
            progressCount.textContent = decodeBytes(data.size);
            progressMax.textContent = decodeBytes(data.max);
            progressTotal.textContent = data.files;

            progress.max = data.max;
            progress.value = data.size;
        }
    });
}

fileInput.addEventListener('change', () => {
    updateTable(fileInput.files);
});

document.getElementById('uploadButton').addEventListener('click', () => {
    uploadFiles();
});

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

    updateFilesProgress();
}

init();