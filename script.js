// Находим все подэлементы equipment-sub-item
const equipmentSubItems = document.querySelectorAll('.equipment-sub-item');

// Находим изображение, которое нужно менять
const equipmentImage = document.querySelector('.equipment-image img');

// Добавляем обработчики событий для каждого подэлемента
equipmentSubItems.forEach(item => {
    item.addEventListener('mouseenter', () => {
        // Берём изображение из data-атрибута
        const newImageSrc = item.getAttribute('data-image');
        // Меняем изображение
        equipmentImage.src = newImageSrc;
    });

    item.addEventListener('mouseleave', () => {
        // Возвращаем исходное изображение при уходе с элемента
        equipmentImage.src = 'img/teh.png';
    });
});

// Функция для открытия модального окна
// Принимает ID модального окна (например, 'loginModal' или 'passwordModal')
function openModal(modalId) {
    // Находим элемент модального окна по его ID
    const modal = document.getElementById(modalId);
    // Проверяем, существует ли элемент с указанным ID
    if (modal) {
        // Если элемент найден, устанавливаем стиль display в 'flex', чтобы показать модальное окно
        modal.style.display = 'flex';
    }
}

// Функция для закрытия модального окна
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Добавляем обработчик события для кнопки "Изменить логин"
// Когда пользователь кликает на эту кнопку, открывается модальное окно для изменения логина
document.getElementById('openLoginModal').addEventListener('click', function () {
    // Вызываем функцию openModal с ID модального окна 'loginModal'
    openModal('loginModal');
});

// Добавляем обработчик события для кнопки "Изменить пароль"
// Когда пользователь кликает на эту кнопку, открывается модальное окно для изменения пароля
document.getElementById('openPasswordModal').addEventListener('click', function () {
    // Вызываем функцию openModal с ID модального окна 'passwordModal'
    openModal('passwordModal');
});

// Находим все элементы с классом 'close' (кнопки закрытия модальных окон)
// Итерируемся по каждому элементу, добавляя обработчик события click
document.querySelectorAll('.close').forEach(closeButton => {
    // Добавляем обработчик события click для каждой кнопки закрытия
    closeButton.addEventListener('click', function (event) {
        // Предотвращаем стандартное поведение элемента. Это гарантирует, что клик будет обрабатываться только нашим кодом, без дополнительных эффектов.
        event.preventDefault();

        // Получаем атрибут data-modal-id из кнопки закрытия
        // Этот атрибут содержит ID модального окна, которое нужно закрыть
        const modalId = this.getAttribute('data-modal-id');

        // Вызываем функцию closeModal с полученным ID модального окна
        closeModal(modalId);
    });
});