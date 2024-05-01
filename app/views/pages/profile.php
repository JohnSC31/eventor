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
                <li events-nav="requested" status="1">Solicitados</li>
                <li events-nav="active" class="active" status="2">Activos</li>
                <li events-nav="ended" status="3">Finalizados</li>
            </ul>
        </nav>

        <div class="client-events-list" id="client-events-list" idClient="<?php echo $_SESSION['CLIENT']['CID']; ?>">

        </div>
        
    </div>
</div>