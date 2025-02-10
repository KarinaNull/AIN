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
