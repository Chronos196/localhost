<?php 
include("header.php");
include("db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О нас</title>
</head>
<body>
    <h1>Best Memories</h1>
    <p>
        Слоган:
        Каждый снимок — шедевр.
        Сохраним моменты вашей жизни.
    </p>
    <p>
        Создаем уникальные фотографии, которые помогают нашим клиентам стать успешнее. 
        Мы придумываем идеи, проводим съемку, обрабатываем фотографии, и берем на себя 
        всю организацию процесса. Вам остается только начать использовать результат.
    </p>
    <p>
        Профессиональна фотостудия «Best Memories»
        Это уютная и светлая, интерьерная студия с большими окнами и потолками высотой 4,5 метра 
        и залом 60 кв метров. Основное направление студии, семейные и детские фотосессии. 
        Студия оборудована всем необходимым для профессиональной фотосъемки любой сложности.
    </p>
    <p>
        Моноблоки Hensel, световые насадки жесткого и мягкого света, разного размера софтбоксы, 
        стрипбоксы, октабокс, портретные тарелка, рефлекторы, тубус. На окнах имеются горизонтальные 
        жалюзи для затемнения и диффузоры. Окна студии выходят на солнечную сторону.
    </p>
    <p>
        Белая угловая Циклорама 4*4- это очень удобное место для съемок маленьких видео роликов, 
        съемок одежды для каталога. Мы единственная студия с балконом над циклорамой, и с подвесной 
        системой над ней на З источника, для съемок Stop-motion с верхней точки на высоте 4-х метров 
        и возможностью подвешивать над ней людей и предметы.
    </p>
    <p>
        Интерьеры и реквизит – все это поможет Вам воплотить любую идею в жизнь. 
    </p>
    <p>
        Студия универсальна и подходит для каталожной и рекламной съемки, детских и семейных съемок, 
        а также съемок новорожденных, проведения мастер-классов и семинаров, а так-же видео съемок.
    </p>
    <section id="recent-works">
        <h2>Последние работы</h2>
        <div class="slider">
            <?php
            $recentWorks = getRecentWorks($pdo);
            foreach ($recentWorks as $work) {
                echo "<div class='slide'>";
                echo "<img src='{$work['photo_filename']}' alt='{$work['name']}'>";
                echo "<h3>{$work['name']}</h3>";
                echo '<p>' . nl2br($work['description']) . '</p>';
                // Добавьте другие необходимые данные о работе
                echo "</div>";
            }
            ?>
        </div>
    </section>
    <h2>Наши фотографы</h2>
    <div class="about-photographers">
        <?php
        $photographers = getPhotographers($pdo);
        foreach ($photographers as $photographer) {
            echo "<div class='photographer'>";
            echo "<img src='{$photographer['photo_filename']}' alt='{$photographer['name']}'>";
            echo "<h3>{$photographer['name']}</h3>";
            echo "</div>";
        }
        ?>
    </div>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>

    <!-- ... Ваш предыдущий HTML-код ... -->

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script>
        $(document).ready(function(){
            $('.slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000, // Интервал между слайдами в миллисекундах (например, 3000 мс = 3 секунды)
                dots: true,
                arrows: false,
            });
        });
    </script>
</body>
</html>