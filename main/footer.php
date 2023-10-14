<!-- Карта -->
<section id="contact" class="container" id="contact">
    <h2 class="text-center">Наши контакты</h2>
    <div class="row py-5">
        <div class="col-md-6">
            <!-- Вставьте код для вставки карты сюда -->
            <iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3A7b729175fd0e123e5e50eb1c32819ab57ed5e12bf27e0cb19897e7aef998ebe0&amp;source=constructor" width="100%" height="300" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
        </div>
        <div class="col-md-6">
          <address>
              <p><i class="fa fa-map-marker" aria-hidden="true" style="color: #007BFF;"></i> <strong style="color: #007BFF;"> Наш адрес:</strong> г. Воронеж, ул. Ленинградская, д. 33</p>
              <p><i class="fa fa-phone" aria-hidden="true" style="color: #007BFF;"></i><strong style="color: #007BFF;"> Телефон:</strong> +7 (123) 456-7890</p>
              <p><i class="fa fa-clock-o" aria-hidden="true" style="color: #007BFF;"></i> <span id="status-main"><span id="status" style="font-weight: bold; color: green;"></span> <span id="stat"></span></span></p>
          </address>
        </div>
    </div>
</section>
<!-- Подвал -->
<footer class="bg-bbb text-white text-center py-3">
  <div class="container">
      <div class="row">
          <div class="col-md-4">
              <h4>Адрес</h4>
              <p>г.Воронеж, ул. Ленинградская, д. 33</p>
          </div>
          <div class="col-md-4">
              <h4>Контакты</h4>
              <p>Тел: +7 (123) 456-7890<br>Email: info@itcube.pro</p>
              <p>9:00 до 18:00, Пн-Пт</p>
          </div>
          <div class="col-md-4">
              <h4>Социальные сети</h4>
              <p>Мы в соцсетях:<br>
                  <a href="#" class="social-icon"><i class="fa fa-vk"></i></a>
              </p>
          </div>
      </div>
  </div>
  <a href="#" class="back-to-top"><i class="fa fa-arrow-up"></i> Наверх</a>
</footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.js"></script>
<script>
// Функция для определения статуса (открыто или закрыто)
function checkStatus() {
    const now = new Date();
    const dayOfWeek = now.getUTCDay(); // День недели по UTC

    // Создаем объект времени с GMT+3
    const timeInGMT3 = new Date(now.getTime() + (3 * 60 * 60 * 1000));

    // Проверяем, является ли текущий день выходным (суббота или воскресенье)
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        // Если выходной, сообщаем, что откроется в понедельник в 9:00
        document.getElementById('status').innerHTML = 'Закрыто';
        $('#status').css('color', '#e64646');
        document.getElementById('stat').innerHTML = '· Откроется в понедельник в 9:00';
    } else {
        // Если будний день, проверяем текущее время в GMT+3
        const currentHourInGMT3 = timeInGMT3.getUTCHours();
        console.log(timeInGMT3.getUTCHours());
        if (currentHourInGMT3 >= 9 && currentHourInGMT3 < 18) {
            // Если текущее время между 9:00 и 18:00, то открыто
            document.getElementById('status').innerHTML = 'Открыто';
            document.getElementById('stat').innerHTML = '· до 18:00';
        } else if (currentHourInGMT3 < 9 || currentHourInGMT3 > 18) {
            // Если текущее время между 9:00 и 18:00, то открыто
            document.getElementById('status').innerHTML = 'Закрыто';
            $('#status').css('color', '#e64646');
            document.getElementById('stat').innerHTML = '· Откроется в 9:00';
        } else {
            // В остальных случаях сообщаем, сколько минут осталось до открытия
            const minutesUntilOpen = 60 - timeInGMT3.getUTCMinutes();
            document.getElementById('status').innerHTML = `Закроется через ${minutesUntilOpen} минут`;
            $('#status').css('color', '#ffc107');
        }
    }
}

// Вызываем функцию для определения статуса при загрузке страницы
checkStatus();

// Обновляем статус каждую минуту (можно увеличить интервал)
setInterval(checkStatus, 60000);
$(document).ready(function () {
    $('.news-container').slick({

        dots: true,
        slidesToShow: 3, // Количество отображаемых слайдов
        slidesToScroll: 1, // Количество прокручиваемых слайдов
        autoplay: true, // Автоматическая прокрутка
        autoplaySpeed: 5000, // Скорость автоматической прокрутки в миллисекундах
        prevArrow: $('.custom-slick-prev'), // Кнопка "Предыдущий слайд"
        nextArrow: $('.custom-slick-next'),
        responsive: [
        {
            breakpoint: 992, // Если ширина экрана меньше 992px
            settings: {
                slidesToShow: 2, // Измените количество отображаемых слайдов
            }
        },
        {
            breakpoint: 768, // Если ширина экрана меньше 768px
            settings: {
                slidesToShow: 1, // Измените количество отображаемых слайдов
            }
        }
        // Добавьте дополнительные настройки для других разрешений экрана при необходимости
    ]
    });

});

</script>


<script>
       $(document).ready(function () {
           // Обработчик клика на плюс/минус
           $('.toggle-icon').click(function () {
               var $icon = $(this).find('i');
               var $info = $(this).closest('.teacher').find('.teacher-info');
               if ($info.is(':visible')) {
                   $info.slideUp();
                   $icon.removeClass('fa-minus').addClass('fa-plus');
                    jQuery(this).find(".toggle-name-u").text('Описание');
               } else {
                   $info.slideDown();
                   $icon.removeClass('fa-plus').addClass('fa-minus');
                     jQuery(this).find(".toggle-name-u").text('Скрыть описание');
               }
           });
       });
       $(document).ready(function () {
           $("a[href^='#']").on("click", function (e) {
               e.preventDefault();

               var target = this.hash;
               var $target = $(target);

               $("html, body").animate({
                   scrollTop: $target.offset().top - 70 // Вычитаем высоту навигационной панели
               }, 1000); // Продолжительность анимации
           });
       });

   </script>
<script>
// Функция, которая скрывает предзагрузку и отображает сайт
function hidePreloader() {
    // Скрываем предзагрузку
    var preloader = document.querySelector('.preloader');
    preloader.style.display = 'none';

    // Отображаем сайт
    var siteContent = document.querySelector('body');
    siteContent.style.display = 'block';
}

// Ожидаем полной загрузки страницы
window.addEventListener('load', hidePreloader);
</script>
