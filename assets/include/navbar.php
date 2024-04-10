<head>
<link rel="stylesheet" href="./assets/css/navbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!--<div class="logo">
        <a href="#"><img src="./assets/img/logo.png"/></a>
    </div>-->
<nav class="navbar" id="navbar">
    <ul class="nav-list" id="navList">
        <li <?php if ($page === 'dashboard') echo 'class="active"'; ?>><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i></a></li>
        <li <?php if ($page === 'agenda') echo 'class="active"'; ?>><a href="agenda.php"><i class="fas fa-calendar-alt"></i></a></li>
        <?php if ($_COOKIE['role'] === 'administrateur' || $_COOKIE['role'] === 'modification') : ?>
        <li <?php if ($page === 'ajouter_candidature') echo 'class="active"'; ?>><a href="ajouter_candidature.php"><i class="fas fa-plus-circle"></i></a></li>
        <?php endif; ?>
        <?php if ($_COOKIE['role'] === 'administrateur') : ?>
            <li <?php if ($page === 'gestion_utilisateurs') echo 'class="active"'; ?>><a href="gestion_utilisateurs.php"><i class="fas fa-users-cog"></i></a></li>
        <?php endif; ?>
        <?php if ($_COOKIE['role'] === 'administrateur') : ?>
        <li <?php if ($page === 'gestion_bdd') echo 'class="active"'; ?>><a href="gestion_bdd.php"><i class="fas fa-cog"></i></a></li>
        <?php endif; ?>
        <li class="logout"><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i></a></li>
    </ul>
</nav>
<script>
    window.addEventListener('scroll', function() {
    var navbar = document.querySelector('.navbar');
    if (window.scrollY > 0) {
        navbar.classList.add('fixed');
    } else {
        navbar.classList.remove('fixed');
    }
});

</script>
<script>
    // Récupère l'élément de la barre de navigation avec l'ID "navList"
    var navList = document.getElementById('navList');
    // Récupère les éléments li de la liste de navigation
    var navItems = navList.getElementsByTagName('li');

    // Parcourt chaque élément li
    for (var i = 0; i < navItems.length; i++) {
        // Vérifie si l'URL de la page correspond à l'URL du lien dans l'élément li
        if (window.location.href.includes(navItems[i].querySelector('a').getAttribute('href'))) {
            // Ajoute la classe "active" à l'élément li si c'est la page active
            navItems[i].classList.add('active');
        }
    }
</script>

</body>