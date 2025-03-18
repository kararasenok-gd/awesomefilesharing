function showModal(title, content = '', options = {}, modalid = '', modalOnLoad = () => {}) {
    const { closeButton = true } = options;
    const modal = document.createElement('div');
    modal.className = 'modal show';
    modal.id = modalid;
    
    const modalBox = document.createElement('div');
    modalBox.className = 'modal-box show';
    
    const modalHeader = document.createElement('div');
    modalHeader.className = 'modal-header';
    
    const modalTitle = document.createElement('div');
    modalTitle.className = 'modal-title';
    modalTitle.textContent = title;
    
    if (closeButton) {
        const closeBtn = document.createElement('button');
        closeBtn.className = 'modal-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', () => hideModal(modal));
        modalHeader.appendChild(closeBtn);
    }
    
    modalHeader.appendChild(modalTitle);
    
    const divider = document.createElement('div');
    divider.className = 'modal-divider';
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    
    if (typeof content === 'string') {
        modalContent.innerHTML = content;
    } else {
        modalContent.appendChild(content);
    }
    
    modalBox.append(modalHeader, divider, modalContent);
    modal.appendChild(modalBox);
    document.body.appendChild(modal);

    modalOnLoad();
    
    return modal;
}

function hideModal(modal) {
    const box = modal.querySelector('.modal-box');
    modal.classList.add('hide');
    box.classList.add('hide');
    
    setTimeout(() => {
        modal.remove();
    }, 300);
}