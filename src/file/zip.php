<?php
$archive = isset($_GET['file']) ? $_GET['file'] : null;

if (!$archive) {
    http_response_code(400);
    echo "Error: No archive file specified.";
    exit;
}

$filePath = realpath(__DIR__ . '/../uploads/' . basename($archive));

if (!$filePath || !file_exists($filePath)) {
    http_response_code(404);
    echo "Error: File not found.";
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZIP Viewer</title>
    <script src="https://cdn.jsdelivr.net/npm/jszip/dist/jszip.min.js"></script>
    <style>
        body {
            background-color: #282828;
            color: #846f65;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        .progress-container {
            margin-bottom: 20px;
        }

        .progress-description {
            margin-bottom: 10px;
        }

        .progress-bar {
            width: 100%;
            background-color: #312d2b;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-inner {
            height: 20px;
            width: 0;
            background-color: #846f65;
            text-align: center;
            line-height: 20px;
            color: #282828;
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .file-view {
            background-color: #312d2b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .file-name {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .file-preview {
            max-width: 75%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
        }

        .download-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #846f65;
            color: #282828;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            background-color: #312d2b;
            color: #846f65;
        }

        pre {
            background-color: #312d2b;
            color: #b8b8b8;
            padding: 10px;
            border-radius: 8px;
            white-space: pre-wrap;
            overflow-x: auto;
            max-height: 300px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="progress-container" class="progress-container">
            <div id="progress-description" class="progress-description">Загрузка архива...</div>
            <div class="progress-bar">
                <div id="progress-bar-inner" class="progress-bar-inner">0%</div>
            </div>
        </div>
        <div id="file-list"></div>
    </div>

    <script>
        async function fetchArchive() {
            const progressBar = document.getElementById('progress-bar-inner');
            const progressDescription = document.getElementById('progress-description');
            const progressContainer = document.getElementById('progress-container');

            try {
                const response = await fetch(`./?name=<?php echo basename($archive); ?>&raw=1`);
                if (!response.ok) throw new Error('Failed to fetch archive');

                const reader = response.body.getReader();
                const contentLength = +response.headers.get('Content-Length');
                let receivedLength = 0;
                let chunks = [];

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;
                    chunks.push(value);
                    receivedLength += value.length;

                    const percent = Math.round((receivedLength / contentLength) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                }
                progressDescription.textContent = 'Обработка архива...';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';

                const zipData = new Blob(chunks);
                await processZip(zipData);
                progressContainer.style.display = 'none';
            } catch (error) {
                document.getElementById('file-list').innerHTML = `<div class="file-view">Error: ${error.message}</div>`;
            }
        }

        async function processZip(zipBlob) {
            const progressBar = document.getElementById('progress-bar-inner');
            const zip = new JSZip();
            const zipContent = await zip.loadAsync(zipBlob);
            const fileList = document.getElementById('file-list');
            const files = Object.keys(zipContent.files).sort((a, b) => a.localeCompare(b));
            const totalFiles = files.length;

            for (let i = 0; i < totalFiles; i++) {
                const filename = files[i];
                const file = zipContent.files[filename];

                const entry = document.createElement('div');
                entry.className = 'file-view';
                entry.innerHTML = `<div class="file-name">${filename}</div>`;

                if (!file.dir) {
                    const fileBlob = await file.async('blob');
                    const fileURL = URL.createObjectURL(fileBlob);

                    if (filename.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
                        const img = document.createElement('img');
                        img.src = fileURL;
                        img.className = 'file-preview';
                        entry.appendChild(img);
                    } else if (filename.match(/\.(mp3|wav|ogg|webm)$/i)) {
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.src = fileURL;
                        entry.appendChild(audio);
                    } else if (filename.match(/\.(mp4|webm|ogg)$/i)) {
                        const video = document.createElement('video');
                        video.controls = true;
                        video.src = fileURL;
                        video.className = 'file-preview';
                        entry.appendChild(video);
                    } else {
                        const fileData = await file.async('text');
                        const preview = document.createElement('pre');
                        preview.textContent = fileData;
                        entry.appendChild(preview);
                    }

                    const downloadLink = document.createElement('a');
                    downloadLink.href = fileURL;
                    downloadLink.download = filename;
                    downloadLink.className = 'download-btn';
                    downloadLink.textContent = 'Скачать';
                    entry.appendChild(downloadLink);
                }

                fileList.appendChild(entry);
                const percent = Math.round(((i + 1) / totalFiles) * 100);
                progressBar.style.width = percent + '%';
                progressBar.textContent = percent + '%';
            }
        }

        fetchArchive();
    </script>
</body>

</html>
