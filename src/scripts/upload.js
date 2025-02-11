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
    initTh2.textContent = 'NSFW';
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
        
        const numCell = document.createElement('td');
        numCell.textContent = i + 1;
        tr.appendChild(numCell);

        const nameCell = document.createElement('td');
        const fileTempURL = URL.createObjectURL(file);
        nameCell.innerHTML = `<a href="${fileTempURL}" target="_blank">${file.name}</a>`;
        tr.appendChild(nameCell);

        const nsfwCell = document.createElement('td');
        const nsfwCheckbox = document.createElement('input');
        nsfwCheckbox.type = 'checkbox';
        nsfwCheckbox.id = `nsfw-${file.name}`;
        nsfwCell.appendChild(nsfwCheckbox);
        tr.appendChild(nsfwCell);

        const sizeCell = document.createElement('td');
        sizeCell.textContent = decodeBytes(file.size);
        tr.appendChild(sizeCell);

        const typeCell = document.createElement('td');
        typeCell.textContent = file.type;
        tr.appendChild(typeCell);

        const uploadedCell = document.createElement('td');
        uploadedCell.textContent = "❌";
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
        const formData = new FormData();
        formData.append('file', file);
        formData.append('isNSFW', document.getElementById(`nsfw-${file.name}`).checked ? 1 : 0);
        formData.append('hcaptcha', captchaToken);

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
                        a.href = `http://test.awesomefilesharing.rf.gd/file/?name=${data.filename}`;
                        a.innerHTML = `http://test.awesomefilesharing.rf.gd/file/?name=${data.filename}`;
                        a.target = '_blank';
                        p.appendChild(a);
                        uploadResults.appendChild(p);
                        uploadedElement.textContent = "✅";
                    } else {
                        uploadedElement.textContent = "❌";
                        const p = document.createElement('p');
                        p.textContent = `Ошибка загрузки ${file.name}: ${data.error}`;
                        uploadResults.appendChild(p);
                    }
                } catch (e) {
                    uploadedElement.textContent = "❌";
                    const p = document.createElement('p');
                    p.textContent = `Ошибка обработки ответа для ${file.name}`;
                    uploadResults.appendChild(p);
                }
            } else {
                uploadedElement.textContent = "❌";
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

updateFilesProgress();