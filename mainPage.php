<?php
// Устанавливаем заголовок CSP для защиты от XSS
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://apis.google.com 'unsafe-inline'; style-src 'self' https://fonts.googleapis.com; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com; connect-src 'self' http://api.openweathermap.org");

// Подключение к базе данных
include 'db.php';

// Функция для безопасного вывода данных
function safe_output($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Запрос информации о компании
$companyQuery = $conn->prepare("SELECT * FROM company_info LIMIT 1");
$companyQuery->execute();
$companyResult = $companyQuery->get_result();
$company = $companyResult->fetch_assoc();

// Запрос статистики
$statsQuery = $conn->prepare("SELECT * FROM statistics");
$statsQuery->execute();
$statsResult = $statsQuery->get_result();

// Запрос категорий оборудования
$categoryQuery = $conn->prepare("SELECT * FROM equipment_categories");
$categoryQuery->execute();
$categoryResult = $categoryQuery->get_result();

// Запрос отзывов
$reviewsQuery = $conn->prepare("SELECT * FROM reviews");
$reviewsQuery->execute();
$reviewsResult = $reviewsQuery->get_result();

// Запрос оборудования
$equipmentQuery = $conn->prepare("SELECT * FROM equipment");
$equipmentQuery->execute();
$equipmentResult = $equipmentQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Tenor+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.0">
    <script src="geolocation.js"></script>
</head>

<body>
    <div class="container">
        <div class="welckome-block">
            <img src="img/line2.png" alt="Line Image" class="line-image">

            <div class="navbar">
                <ul class="nav-list">
                    <li><a href="#about">О нас</a></li>
                    <li><a href="#important">Важно</a></li>
                    <li><a href="#contacts">Контакты</a></li>
                    <li><a href="#reviews">Отзывы</a></li>
                </ul>
                <div class="account">
                    <a href="./acount.php">Учетная запись</a>
                </div>
            </div>
            <div class="welcome-message">
                <h1 class="welcome-title">Самый быстрый путь к</h1>
                <h2 class="implementation-focus">Внедрению</h2>
                <h3 class="future-business">бизнеса будущего</h3>
            </div>

            <div class="guarantee-container">
                <img src="img/logo.png" alt="Логотип" class="guarantee-logo" width="67px" height="32px">
                <p class="guarantee-text">Коммерческие услуги, предоставляемые AIN, членом FDIC, в соответствии с
                    лицензией Mastercard International Inc.</p>
            </div>
        </div>

        <div class="about-ain" id="about">
            <h2 class="about-title"><?php echo safe_output($company['title']); ?></h2>
            <p class="about-description"><?php echo safe_output($company['description']); ?></p>

            <div class="statistics">
                <?php while ($stat = $statsResult->fetch_assoc()): ?>
                    <div class="statistic-item">
                        <span class="statistic-value"><?php echo safe_output($stat['statistic_value']); ?></span>
                        <span class="statistic-description"><?php echo safe_output($stat['statistic_description']); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <?php if ($categoryResult->num_rows > 0): ?>
            <div class="categories" id="categories">
                <p class="before-categories">AIN принимает более 10.000 заказов ежегодно</p>
                <p class="categories-main">Категории оборудования для производства</p>
                <p class="categories-description">хлеба и хлебобулочных изделий</p>
                <img src="img/productLine.png" alt="Производственная линия" class="categories-img">

                <div class="left-cat">
                    <?php for ($i = 0; $i < 2 && $category = $categoryResult->fetch_assoc(); $i++): ?>
                        <div class="categories-message">
                            <img src="<?php echo safe_output($category['image_url']); ?>" alt="Иконка" class="categories-message-img">
                            <p class="message-title"><?php echo safe_output($category['title']); ?></p>
                            <p class="message-text"><?php echo safe_output($category['description']); ?></p>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="left-cat">
                    <?php while ($category = $categoryResult->fetch_assoc()): ?>
                        <div class="categories-message">
                            <img src="<?php echo safe_output($category['image_url']); ?>" alt="Иконка" class="categories-message-img">
                            <p class="message-title"><?php echo safe_output($category['title']); ?></p>
                            <p class="message-text"><?php echo safe_output($category['description']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Категории оборудования не найдены.</p>
        <?php endif; ?>

        <div class="colleagues">
            <?php
            $imageDir = 'img/colleagues/';
            $images = glob($imageDir . '*.png');
            if (count($images) > 0):
                foreach ($images as $image): ?>
                    <div class="colleague">
                        <img src="<?php echo safe_output($image); ?>" alt="Colleague Photo">
                    </div>
                <?php endforeach;
            else: ?>
                <p>Изображения не найдены.</p>
            <?php endif; ?>
        </div>

        <div class="equipment-section" id="important">
            <div class="equipment-info">
                <h2>Следует обратить внимание</h2>
                <p>на такое оборудование для выпекания хлеба, как:</p>

                <div class="equipment-list">
                    <?php if ($equipmentResult->num_rows > 0): ?>
                        <?php while ($row = $equipmentResult->fetch_assoc()): ?>
                            <div class="equipment-item">
                                <div class="equipment-sub-item" data-image="<?php echo safe_output($row['image_url']); ?>">
                                    <h3><?php echo safe_output($row['title']); ?></h3>
                                    <p><?php echo safe_output($row['description']); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Нет данных об оборудовании.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="equipment-image">
                <img src="img/teh.png" alt="Оборудование для хлебопечения">
            </div>
        </div>

        <div class="contact-section" id="contact-form">
            <div class="contact-info">
                <h2>Пожалуйста, оставьте</h2>
                <h2>ваш E-mail и контактный номер</h2>
                <p>мы с вами свяжемся</p>
                <p class="phone-number-text">Позвонить в отдел продаж</p>
                <p class="phone-number">+7 (901) 790-93-84</p>
            </div>

            <div class="contact-image">
                <img src="img/hand.png" alt="Рука робота">
            </div>

            <form class="contact-form" action="contact-handler.php" method="POST">
                <?php
                if (isset($_GET['status'])) {
                    if ($_GET['status'] == 'success') {
                    } elseif ($_GET['status'] == 'error') {
                    }
                }
                ?>

                <label for="email">Ваш E-mail</label>
                <input type="email" id="email" name="email" placeholder="Введите ваш E-mail" required>

                <label for="phone">Ваш номер телефона</label>
                <input type="tel" id="phone" name="phone" placeholder="Введите ваш номер телефона" required>

                <label for="message">Ваше сообщение</label>
                <textarea id="message" name="message" placeholder="Введите ваше сообщение" required></textarea>

                <button type="submit">ОТПРАВИТЬ</button>
            </form>
        </div>

        <?php
        if (isset($_GET['status'])) {
            $status = $_GET['status'];
            if ($status == 'success') {
                echo "<div id='notification' class='notification success'>Заявка успешно создана! </div>";
            } elseif ($status == 'error') {
                echo "<div id='notification' class='notification error'>Если вы зарегистрируетесь, отслеживать состояние вашей заявки будет проще! Ожидайте звонка.</div>";
            }
        }
        ?>

        <script>
            window.onload = function() {
                var notification = document.getElementById('notification');
                if (notification) {
                    setTimeout(function() {
                        notification.style.display = 'none';
                    }, 3000)

                }
                header('Location: mainPage.php');
            };
        </script>

        <div class="reviews-section" id="reviews">
            <?php if ($reviewsResult->num_rows > 0): ?>
                <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-icon">
                            <!-- $review['review_icon'] — это значение из базы данных или другого источника, которое содержит путь к изображению. -->
                            <!-- safe_output() очищает и защищает данные перед их выводом. -->
                            <img src="<?php echo safe_output($review['review_icon']); ?>" alt="Цитата">
                        </div>
                        <div class="review-content">
                            <p><?php echo safe_output($review['review_text']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Отзывы не найдены.</p>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section company-info">
                    <h2>AIN COMPANY</h2>
                    <p><a href="#categories">Категории оборудования</a></p>
                    <p><a href="#important">Cледует обратить внимание</a></p>
                    <p><a href="#contact-form">Создать заявку</a></p>
                </div>

                <div class="footer-section contacts" id="contacts">
                    <h2>Контакты</h2>
                    <p>Почта: <a href="mailto:ainhelp@mail.ru">ainhelp@mail.ru</a></p>
                    <p>Адрес: г.Москва, ул.Перерва 54</p>
                </div>

                <div class="footer-logo">
                    <a href="#"><img src="img/AIn.png" alt="Instagram"></a>
                </div>
            </div>

            <div class="footer-social">
                <a href="https://www.instagram.com" target="_blank"><img src="img/instagram.png" alt="Instagram"></a>
                <a href="https://www.twitter.com" target="_blank"><img src="img/twiter.png" alt="Twitter"></a>
                <a href="https://www.facebook.com" target="_blank"><img src="img/facebook.png" alt="Facebook"></a>
                <a href="https://www.youtube.com" target="_blank"><img src="img/youtube.png" alt="YouTube"></a>
                <a href="https://www.tiktok.com" target="_blank"><img src="img/tiktok.png" alt="TikTok"></a>
            </div>

            <div class="footer-bottom">
                <p>© AIN, все права защищены.</p>
                <p><a href="#">Политика конфиденциальности</a> | <a href="#">Положения и условия</a></p>
            </div>

            <div class="footer-disclaimer">
                <p>Оборудование предоставляется компанией AIN в соответствии с лицензией на производство и установку хлебопекарных аппаратов.
                    Технологии компании AIN сертифицированы и соответствуют стандартам безопасности, обеспечивая надежность встраиваемого оборудования для хлебопекарных комбинатов.
                    Если вы не являетесь гражданином страны, в которой действует AIN, вы можете подать заявку, предоставив паспорт и действующую визу. Обратите внимание: вам потребуется предоставить идентификационный номер налогоплательщика после его получения. Если вы гражданин страны, идентификационный номер налогоплательщика обязателен для заключения договора.
                    ***Акция: увеличенные гарантии на оборудование до конца 2024 года.</p>
                <p>† Условия гарантии наилучшего тарифа с AIN</p>
            </div>
        </footer>
    </div>
    <script src="script.js"></script>
</body>

</html>