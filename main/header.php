<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
<!-- Предзагрузка -->
 <div class="preloader">
     <img src="/upload/it-logo.png" alt="Логотип" class="logo img-fluid">
     <div class="loading"></div>
 </div>
  <!-- Шапка -->
  <header class="text-white text-center py-5 d-md-block d-none">
     <div class="container">
         <div class="row">
             <div class="col-md-3">
                <img src="/upload/obr.png" alt="Национальные прокеты России" class="img-fluid" style="height: 150px;" />
             </div>
             <div class="col-md-6">
                 <img src="/upload/it-logo.png" alt="IT-Куб Воронеж" class="img-fluid" style="height: 150px;" />
             </div>
             <div class="col-md-3" style="margin:auto;">
                 <a href="student/student.php" class="btn btn-warning mr-2">Студент</a>
                 <a href="teacher/teacher_login.php" class="btn btn-success">Преподаватель</a>
             </div>
         </div>
     </div>
 </header>
  <!-- Навигация -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light d-md-block" id="navbar">
        <div class="container">
          <div class="row moblogo justify-content-center d-lg-none d-xl-none">
              <a href="/" class="navbar-brand">
                  ITCUBE.PRO
              </a>
            </div>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>

            <!-- Навигационное меню -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="/"><span class="icon"><i class="fa fa-home" aria-hidden="true"></i></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#training"><span class="icon"><i class="fa fa-book" aria-hidden="true"></i></span> Направление</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<? if ($_SERVER[REQUEST_URI] != "/") { echo "/";}?>#team"><span class="icon"><i class="fa fa-users" aria-hidden="true"></i></span> Коллектив</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<? if ($_SERVER[REQUEST_URI] != "/") { echo "/";}?>#schedule"><span class="icon"><i class="fa fa-list-alt" aria-hidden="true"></i></span> Расписание</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<? if ($_SERVER[REQUEST_URI] != "/") { echo "/";}?>#partners"><span class="icon"><i class="fa fa-building-o" aria-hidden="true"></i></span> Партнеры</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<? if ($_SERVER[REQUEST_URI] != "/") { echo "/";}?>#contact"><span class="icon"><i class="fa fa-phone" aria-hidden="true"></i></span> Контакты</a>
                    </li>

                </ul>
                <ul class="navbar-nav d-lg-none d-xl-none">
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/teacher_login.php"><span class="icon"><i class="fa fa-lock" aria-hidden="true"></i></span> Преподаватель</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/student.php"><span class="icon"><i class="fa fa-graduation-cap" aria-hidden="true"></i></span> Студент</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Направления обучения -->
   <section class="container directions" id="training">
     <div class="row">
       <?php
       $stmt = $pdo->prepare("SELECT * FROM directions");
       $stmt->execute();
       $directionsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
       foreach ($directionsList as $direction) :
       ?>
               <a href="/direction/<?php echo $direction['link']; ?>" class="direction col-md-3">
                   <img src="<?php echo $direction['icon']; ?>" alt="<?php echo $direction['name']; ?>" class="direction-icon">
                   <div>
                       <h3 class="direction-title"><?php echo $direction['name']; ?></h3>
                       <p class="direction-age"><?php echo $direction['age']; ?></p>
                   </div>
               </a>
       <?php endforeach; ?>
       <a href="/direction/mobile-development" class="direction col-md-3">
           <img src="/upload/mobile.webp" alt="Мобильная разработка" class="direction-icon">
           <div>
               <h3 class="direction-title">Мобильная разработка</h3>
               <p class="direction-age">От 12 до 18 лет</p>
           </div>
       </a>

   </section>
   <hr>
