function decodeUnixTime(unixTime) {
    const date = new Date(unixTime * 1000);
    return date.toLocaleString();
}

function decodeBytes(bytes) {
    const units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];

    let unitIndex = 0;
    while (bytes >= 1024) {
        bytes /= 1024;
        unitIndex++;
    }

    return bytes.toFixed(2) + ' ' + units[unitIndex];
}

function copyToClipboard(textToCopy) {
    const tempInput = document.createElement('textarea');
    tempInput.value = textToCopy;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
}
