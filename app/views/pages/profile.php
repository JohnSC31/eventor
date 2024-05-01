<div class="container">
    <h1>Perfil</h1>
    <div class="client-profile">
        <div class="client-pic">
            <i class="fa-solid fa-user"></i> 
        </div>
        <div class="client-info">
            <p><?php echo $_SESSION['CLIENT']['COMPANY']; ?></p>
            <p><?php echo $_SESSION['CLIENT']['EMAIL']; ?></p>
            <p><i class="fa-solid fa-phone"></i> <?php echo $_SESSION['CLIENT']['PHONE']; ?></p>
            <p><i class="fa-solid fa-location-dot"></i> <?php echo $_SESSION['CLIENT']['PROVINCE']; ?>, <?php echo $_SESSION['CLIENT']['CANTON']; ?></p>
        </div>
    </div>

    <div class="client-events-container">
        <div class="client-events-header">
            <h2>Mis eventos</h2>
            <a href="<?php echo URL_PATH; ?>request" class="btn btn_red">Solicitar Evento </a>
        </div>

        <nav class="client-events-nav">
            <ul>
                <li>Solicitados</li>
                <li>Activos</li>
                <li>Finalizados</li>
            </ul>
        </nav>

        <div class="client-events-list">

            <div class="event-item">

                <div class="event-item-content">
                    <div class="event-icon-container">
                        <i class="fa-solid fa-people-group"></i>
                    </div>
                    <div class="event-summary">
                        <div class="event-item-header">
                            <p>Tipo de evento</p>
                            <p class="status">Activo</p>
                        </div>
                        
                        <p><i class="fa-solid fa-calendar-days"></i> Fecha y hora</p>
                        <p> <i class="fa-solid fa-location-dot"></i> Ubicacion</p>
                    </div>
                </div>

            </div><!-- .event-item -->


        </div>
        
    </div>
</div>