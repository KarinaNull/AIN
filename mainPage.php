<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Tenor+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.0">

</head>

<body>
    <?php
    include 'db.php';
    ?>

    <meta name="title" content="Профессиональное хлебопекарное оборудование | Печь для выпечки и технологические решения">
    <meta name="description" content="Широкий ассортимент хлебопекарного оборудования для производства хлеба, пирогов и кондитерских изделий. Конвекционные печи, тестомесы, расстоечные шкафы. Профессиональные решения для пекарен.">
    <meta name="keywords" content="печь для выпечки, хлебопекарное оборудование, оборудование для выпечки, конвекционная печь для выпечки, профессиональная печь для выпечки, печь для выпечки пирогов, оборудование для выпечки хлеба, оборудование хлебопекарной промышленности, хлебопекарное оборудование печи, хлебопекарное и технологическое оборудование">

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

        <?php
        require_once 'db.php';

        $companyQuery = "SELECT * FROM company_info LIMIT 1";
        $companyResult = $conn->query($companyQuery);
        $company = $companyResult->fetch_assoc();

        $statsQuery = "SELECT * FROM statistics";
        $statsResult = $conn->query($statsQuery);
        ?>

        <div class="about-ain" id="about">
            <h2 class="about-title"><?php echo htmlspecialchars($company['title']); ?></h2>
            <p class="about-description"><?php echo htmlspecialchars($company['description']); ?></p>

            <div class="statistics">
                <?php while ($stat = $statsResult->fetch_assoc()): ?>
                    <div class="statistic-item">
                        <span class="statistic-value"><?php echo htmlspecialchars($stat['statistic_value']); ?></span>
                        <span class="statistic-description"><?php echo htmlspecialchars($stat['statistic_description']); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>


        <?php
        require_once 'db.php';

        $categoryQuery = "SELECT * FROM equipment_categories";
        $categoryResult = $conn->query($categoryQuery);

        if ($categoryResult->num_rows > 0):
        ?>

            <div class="categories" id="categories">
                <p class="before-categories">AIN принимает более 10.000 заказов ежегодно</p>
                <p class="categories-main">Категории оборудования для производства</p>
                <p class="categories-description">хлеба и хлебобулочных изделий</p>
                <img src="img/productLine.png" alt="Производственная линия" class="categories-img">

                <div class="left-cat">
                    <?php for ($i = 0; $i < 2 && $category = $categoryResult->fetch_assoc(); $i++): ?>
                        <div class="categories-message">
                            <img src="<?php echo htmlspecialchars($category['image_url']); ?>" alt="Иконка" class="categories-message-img">
                            <p class="message-title"><?php echo htmlspecialchars($category['title']); ?></p>
                            <p class="message-text"><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="left-cat">
                    <?php while ($category = $categoryResult->fetch_assoc()): ?>
                        <div class="categories-message">
                            <img src="<?php echo htmlspecialchars($category['image_url']); ?>" alt="Иконка" class="categories-message-img">
                            <p class="message-title"><?php echo htmlspecialchars($category['title']); ?></p>
                            <p class="message-text"><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

        <?php
        else:
            echo "<p>Категории оборудования не найдены.</p>";
        endif;
        ?>


        <?php
        $imageDir = 'img/colleagues/';
        $images = glob($imageDir . '*.png');
        ?>

        <div class="colleagues">
            <?php if (count($images) > 0):
            ?>
                <?php foreach ($images as $image): ?>
                    <div class="colleague">
                        <img src="<?php echo $image; ?>" alt="Colleague Photo">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Изображения не найдены.</p>
            <?php endif; ?>
        </div>

        <?php
        $sql = "SELECT * FROM equipment";
        $result = $conn->query($sql);
        ?>

        <div class="equipment-section" id="important">
            <div class="equipment-info">
                <h2>Следует обратить внимание</h2>
                <p>на такое оборудование для выпекания хлеба, как:</p>

                <div class="equipment-list">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="equipment-item">
                                <div class="equipment-sub-item" data-image="<?php echo htmlspecialchars($row['image_url']); ?>">
                                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($row['description']); ?></p>
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

        <?php
        require_once 'db.php';

        $reviewsQuery = "SELECT * FROM reviews";
        $reviewsResult = $conn->query($reviewsQuery);
        ?>

        <div class="reviews-section" id="reviews">
            <?php if ($reviewsResult->num_rows > 0): ?>
                <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-icon">
                            <img src="<?php echo htmlspecialchars($review['review_icon']); ?>" alt="Цитата">
                        </div>
                        <div class="review-content">
                            <p><?php echo htmlspecialchars($review['review_text']); ?></p>
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
    <?php include 'db.php'; ?>
</body>
</body>

</html>