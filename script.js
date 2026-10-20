document.addEventListener('DOMContentLoaded', () => {
    const clickBtn = document.getElementById('click-btn');
    const goldDisplay = document.getElementById('gold-display');
    const eyesDisplay = document.getElementById('eyes-display');
    const buyBtns = document.querySelectorAll('.buy-btn');

    function formatMoney(amount) {
        return parseFloat(amount).toLocaleString('ru-RU', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Клик
    clickBtn.addEventListener('click', () => {
        fetch('api/action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=click'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                goldDisplay.textContent = 'Золото: ' + formatMoney(data.gold);
                eyesDisplay.textContent = 'Очей: ' + data.eyes;
            } else {
                console.error('Ошибка клика:', data.message);
            }
        })
        .catch(err => console.error('Сеть:', err));
    });

    // Покупка
    buyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const itemId = btn.dataset.id;
            const itemName = btn.dataset.name;

            fetch('api/action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=buy&item_id=' + encodeURIComponent(itemId)
            })
            .then(res => res.json())
            .then(data => {
                console.log('Ответ сервера:', data);

                if (data.success) {
                    goldDisplay.textContent = 'Золото: ' + formatMoney(data.gold);
                    // Обновляем количество "в наличии"
                    const card = btn.closest('.item-card');
                    const ownedEl = card.querySelector('.owned');
                    if (ownedEl) {
                        const current = parseInt(ownedEl.textContent.replace(/\D/g, ''), 10) || 0;
                        ownedEl.textContent = 'В наличии: ' + (current + 1);
                    }
                    // Плавное подтверждение
                    btn.textContent = '✓ Куплено';
                    setTimeout(() => { btn.textContent = 'Купить'; }, 800);
                } else {
                    alert('Не удалось купить «' + itemName + '»:\n' + data.message);
                }
            })
            .catch(err => {
                console.error('Сеть:', err);
                alert('Ошибка соединения с сервером');
            });
        });
    });
});