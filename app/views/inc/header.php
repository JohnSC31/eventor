<!DOCTYPE html>
<html class="no-js" lang="es">

<head>

    <meta charset="utf-8">
    <title><?php  echo WEB_NAME . " | " .$data['TITLE'];?></title>

    <meta name="description" content="Eventor organizador de eventos empresariales">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="site.webmanifest">

    <!-- Link of google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet"> 


    <!-- Link para Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- NORMALIZE -->
    <link rel="stylesheet" href="<?php echo URL_PATH; ?>public/css/normalize.css">
    <link rel="stylesheet" href="<?php echo URL_PATH; ?>public/css/main.css">

  

    <meta name="theme-color" content="#fafafa">

</head>
<body id="<?php echo $data['ID'];?>" data-url="<?php echo URL_PATH; ?>">
 
<header class="web_header">
    <div class="fixed_bar">
        <div class="container">
            <a href="<?php echo URL_PATH; ?>home">
                <div class="logo">
                    <img src="<?php echo URL_PATH; ?>public/img/whiteLogo.png" alt="Página principal">
                </div>
            </a>
            <nav class="navigation">
                <ul>
                    <?php if(!isset($_SESSION['CLIENT']['SESSION']) && true){ ?>
                        <!-- BOTONES CUANDO NO HAY SESION -->
                        <li><a href="<?php echo URL_PATH; ?>signup" class="btn btn_red"><i class="fa-solid fa-user-plus"></i> <span class="hide_medium"> Registro </span></a></li>
                        <li><a href="<?php echo URL_PATH; ?>login" class="btn btn_green"><i class="fa-solid fa-right-to-bracket"></i> <span class="hide_medium"> Iniciar Sesión </span></a></li>
                    <?php } else { ?>
                        <!-- SESION INICIADA -->
                        <li><a href="<?php echo URL_PATH; ?>request" class="btn btn_red"><i class="fa-solid fa-calendar-days"></i></i> <span class="hide_medium"> Solicitar </span></a></li>
                        <li><a href="<?php echo URL_PATH; ?>profile" class="btn btn_green"><i class="fa-solid fa-user"></i> <span class="hide_medium"> Perfil </span></a></li>
                        <li><a href="javascript:void(0);" class="btn btn_black" log-out="true"><i class="fa-solid fa-right-from-bracket" aria-label="Cerrar Sesión"></i></a></li>

                    <?php } ?>
                </ul>
            </nav>
        </div>
        
    </div>
    <div class="bar_space"></div>

</header>

<div class="notification_container" id="notification_container" role="alert"></div>

<div class="modal_container" id="modal_container" role="alert" aria-modal="true"></div>

<main id="main-container">
