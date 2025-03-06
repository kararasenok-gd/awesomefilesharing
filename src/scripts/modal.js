function showModal(title, content = '', options = {}, modalid = '') {
    const { closeButton = true } = options;

    // Создаем обертку модального окна
    const modal = document.createElement('div');
    modal.className = 'modal show';
    modal.id = modalid;

    // Создаем контейнер содержимого
    const modalBox = document.createElement('div');
    modalBox.className = 'modal-box show';

    // Создаем заголовок с кнопкой закрытия
    const modalHeader = document.createElement('div');
    modalHeader.className = 'modal-header';

    const modalTitle = document.createElement('div');
    modalTitle.className = 'modal-title';
    modalTitle.textContent = title;

    // Кнопка закрытия
    if (closeButton) {
        const closeBtn = document.createElement('button');
        closeBtn.className = 'modal-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', () => hideModal(modal));
        modalHeader.appendChild(closeBtn);
    }

    modalHeader.appendChild(modalTitle);

    // Создаем разделитель
    const divider = document.createElement('div');
    divider.className = 'modal-divider';

    // Создаем контейнер основного контента
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    modalContent.innerHTML = content;

    // Собираем компоненты
    modalBox.append(modalHeader, divider, modalContent);
    modal.appendChild(modalBox);
    document.body.appendChild(modal);

    return modal;
}

function hideModal(modal) {
    const box = modal.querySelector('.modal-box');
    modal.classList.add('hide');
    box.classList.add('hide');

    setTimeout(() => {
        modal.remove();
    }, 300); // Соответствует длительности анимации
}