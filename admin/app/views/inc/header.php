<!DOCTYPE html>
<html class="no-js" lang="es">

<head>

    <meta charset="utf-8">
    <title><?php  echo WEB_NAME . " | " .$data['TITLE'];?></title>

    <meta name="description" content="Eventor: Tu organizador de eventos">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="site.webmanifest">

    <!-- Link of google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet"> 
    <!-- Place favicon.ico in the root directory -->

    <!-- DATATABLTES AND BOOSTRAP STYLES-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Link para Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- NORMALIZE -->
    <link rel="stylesheet" href="<?php echo URL_PATH; ?>public/css/normalize.css">
    <!-- CSS de la zona cliente para user sus atributos  -->
    <link rel="stylesheet" href="<?php echo URL_PATH; ?>public/css/main.css"> 
    <!-- CSS exclusivo del admin area -->
    <link rel="stylesheet" href="<?php echo URL_ADMIN_PATH; ?>public/css/main.css">


    <meta name="theme-color" content="#fafafa">

</head>
<body id="<?php echo $data['ID'];?>" data-url="<?php echo URL_ADMIN_PATH; ?>">

    <div class="notification_container" id="notification_container" role="alert"></div>

    <div class="modal_container" id="modal_container" role="alert" aria-modal="true"></div>

    <?php if($data['ID'] !== 'login') : ?>
    
        <div class="dashboard-container">

            <div class="side-nav">
                <div class="navigation-container">
                    <a class="header-logo" href="<?php echo URL_PATH; ?>home">
                        <img src="<?php echo URL_PATH; ?>public/img/whiteLogo.png" alt="Eventor Logo">
                    </a>

                    <nav class="admin-navigation">
                        <ul id="admin_nav">
                            <li><a href="<?php echo URL_ADMIN_PATH . "home"?>" class="<?php echo $data['ID'] == 'home' | $data['ID'] == 'event' ? "active" : ""; ?>">
                            <i class="fa-solid fa-calendar-day"></i> <span class="hide_medium"> Eventos</span></a></li>

                            <li><a href="<?php echo URL_ADMIN_PATH . "clients"?>" class="<?php echo $data['ID'] == 'clients' ? "active" : ""; ?>">
                            <i class="fa-solid fa-user"></i> <span class="hide_medium"> Clientes</span></a></li>

                            <!-- <li><a href="<?php echo URL_ADMIN_PATH . "admins"?>" class="<?php echo $data['ID'] == 'admins' ? "active" : ""; ?>">
                            <i class="fa-solid fa-user-shield"></i> <span class="hide_medium"> Administradores</span></a></li> -->

                            <li><a href="<?php echo URL_ADMIN_PATH . "settings"?>" class="<?php echo $data['ID'] == 'settings' ? "active" : ""; ?>">
                            <i class="fa-solid fa-gears"></i> <span class="hide_medium"> Configuraciones</span></a></li>

                        </ul>
                    </nav>
                </div>

                <div class="side-nav-footer">
                    <button class="btn btn_white" data-admin-logout="true">Cerrar Sesión</button>
                    <p class="header_rights hide_medium">Todos los derechos resevados 2023</p>
                </div>

            </div>
            <main id="main-container">

        <?php endif;?>
    
 
    

        
        