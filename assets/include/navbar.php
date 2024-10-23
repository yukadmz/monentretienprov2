<head>
<link rel="stylesheet" href="./assets/css/navbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!--<div class="logo">
        <a href="#"><img src="./assets/img/logo.png"/></a>
    </div>-->
<?php 
session_start();

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Récupération du nom unique de cookie basé sur l'identifiant de l'utilisateur
$cookieName = 'role_' . $_SESSION['nom_utilisateur'];

// Vérification de l'existence du cookie
if (!isset($_COOKIE[$cookieName])) {
    // Si le cookie n'existe pas, redirige vers la page d'authentification
    header('Location: index.php');
    exit;
}

// Vérifie si l'utilisateur a le rôle d'administrateur
if ($_COOKIE[$cookieName] === 'administrateur') :
    // Contenu réservé aux administrateurs
?>
    <nav class="navbar" id="navbar">
    <ul class="nav-list" id="navList">
        <li <?php if ($page === 'dashboard') echo 'class="active"'; ?>><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i></a></li>
        <li <?php if ($page === 'agenda') echo 'class="active"'; ?>><a href="agenda.php"><i class="fas fa-calendar-alt"></i></a></li>
        <li <?php if ($page === 'ajouter_candidature') echo 'class="active"'; ?>><a href="ajouter_candidature.php"><i class="fas fa-plus-circle"></i></a></li>
        <li <?php if ($page === 'admin') echo 'class="active"'; ?>><a href="admin.php"><i class="fas fa-cog"></i></a></li>
        <li <?php if ($page === 'aide') echo 'class="active"'; ?>><a href="aide.php"><i class="fas fa-question-circle"></i></a></li>
        <li class="logout"><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i></a></li>
    </ul>
</nav>
<?php elseif ($_COOKIE[$cookieName] === 'modification') :
    // Contenu réservé aux utilisateurs avec le rôle de modification
?>
    <nav class="navbar" id="navbar">
    <ul class="nav-list" id="navList">
        <li <?php if ($page === 'dashboard') echo 'class="active"'; ?>><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i></a></li>
        <li <?php if ($page === 'agenda') echo 'class="active"'; ?>><a href="agenda.php"><i class="fas fa-calendar-alt"></i></a></li>
        <li <?php if ($page === 'ajouter_candidature') echo 'class="active"'; ?>><a href="ajouter_candidature.php"><i class="fas fa-plus-circle"></i></a></li>
        <li <?php if ($page === 'aide') echo 'class="active"'; ?>><a href="aide.php"><i class="fas fa-question-circle"></i></a></li>
        <li class="logout"><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i></a></li>
    </ul>
</nav>
<?php elseif ($_COOKIE[$cookieName] === 'lecture') :
    // Contenu réservé aux viewers
?>
    
    <nav class="navbar" id="navbar">
    <ul class="nav-list" id="navList">
        <li <?php if ($page === 'dashboard') echo 'class="active"'; ?>><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i></a></li>
        <li <?php if ($page === 'agenda') echo 'class="active"'; ?>><a href="agenda.php"><i class="fas fa-calendar-alt"></i></a></li>
        <li <?php if ($page === 'aide') echo 'class="active"'; ?>><a href="aide.php"><i class="fas fa-question-circle"></i></a></li>
        <li class="logout"><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i></a></li>
    </ul>
</nav>
<?php endif; ?>
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

