
<div class="admin_container">
    <div class="header_container">
        <nav class="admin_navigation">
            <div class="admin_logo">
                <img src="<?php echo URL_PATH; ?>public/img/LogoWhite.png" alt="Logo">
            </div>

            <ul id="admin_nav">
        
                    <li data-admin-nav="users"><i class="fa-solid fa-cart-shopping"></i> <span class="hide_medium"> Usuarios</span></li>
            </ul>

            <div class="logout_btn_container">
                <button class="btn btn_white" data-admin-logout="true">Cerrar Sesión</button>
            </div>

        </nav>
        <p class="header_rights hide_medium">Todos los derechos resevados 2023</p>
    </div>
    <div class="dashboard_container" id="dashboard_container">
        
        <!-- SECCION DE USUARIOS -->
        <?php require_once '../app/views/inc/admin-users.php'; ?>
        
    </div>
</div>

