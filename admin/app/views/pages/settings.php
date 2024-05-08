<h1>Configuraciones</h1>

<div class="settings-list-container" id="event-type-form-container">
    <h2>Crear un tipo de evento</h2>

    <form id="event-type-form" method="post" enctype="multipart/form-data" action="create" id-event="">
        <div class="col_2">
            <div class="col-form">
                <div class="field">
                    <input type="text" name="tipo de evento" id="eventType" placeholder="Tipo de evento">
                </div>
                <div class="field">
                    <input type="text" name="tipo de evento" id="eventType-icon" placeholder="Icono de evento">
                </div>
            </div>

            <div class="col-form">
                <div class="field">
                    <input type="text" name="tipo de evento" id="eventType-price" placeholder="Precio de evento" data-mask="00000000">
                </div>
                <div class="field">
                    <textarea name="description" id="eventType-detail" cols="30" rows="3" require placeholder="Descripción de tipo de evento"></textarea>
                </div>
            </div>
        </div><!-- col_2 -->
        <div class="submit">
            <input type="submit" class="btn btn_green" value="Crear tipo de evento">
            <button class="btn btn_red" cancel-form-type-event="">Cancelar</button>
        </div>
    </form>

    <h2>Tipos de eventos</h2>
    <div class="settings-list" id="setting-list-event-type">

        <!-- <div class="setting-item">
            <div class="setting-content">
                <p>Tipo de evento</p>
            </div>
            <div class="setting-actions">
                <button class="btn btn_green"><i class="fa-solid fa-pencil"></i></button>
                <button class="btn btn_red"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div> -->
        
    </div><!-- settings-list -->

</div><!-- settings-list-container -->

<div class="settings-list-container">
    <h2>Crear un servicio</h2>

    <form id="service-form" method="post" enctype="multipart/form-data" action="create" id-service="">
        <div class="col_2">
            <div class="col-form">
                <div class="field">
                    <input type="text" name="servicio" id="service" placeholder="Servicio">
                </div>
                <div class="field">
                    <input type="text" name="tipo de evento" id="service-icon" placeholder="Icono de servicio">
                </div>
            </div>

            <div class="col-form">
                <div class="field">
                    <input type="text" name="tipo de evento" id="service-price" placeholder="Precio de servicio" data-mask="00000000">
                </div>
                <div class="field">
                    <textarea name="description" id="service-detail" cols="30" rows="3" require placeholder="Descripción de tipo de evento"></textarea>
                </div>
            </div>
        </div><!-- col_2 -->
        <div class="submit">
            <input type="submit" class="btn btn_green" value="Crear servicio">
            <button class="btn btn_red" cancel-form-service="" >Cancelar</button>
        </div>
    </form>

    <h2>Servicios</h2>
    <div class="settings-list" id="setting-list-service">

        <!-- <div class="setting-item">
            <div class="setting-content">
                <p>Servicio</p>
            </div>
            <div class="setting-actions">
                <button class="btn btn_green"><i class="fa-solid fa-eye"></i></button>
                <button class="btn btn_red"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div> -->
        
    </div><!-- settings-list -->

</div><!-- settings-list-container -->