<div class="request-container container">
    <h1>Solicitar Evento</h1>

    <div class="request-form-container">
        <form id="request-event-form" method="post" enctype="multipart/form-data">
            <div class="col_2">

                <div class="col">
                    <div class="field">
                        <select name="modalidad" id="select-modality" required>
                            <!-- se cargan los cantones de la base de datos -->
                        </select>
                    </div>
                    <div class="field">
                        <select name="tipoEvento" id="select-event-type" required>
                            <!-- se cargan los cantones de la base de datos -->
                            <option value="0" selected >Tipo de evento</option>
                        </select>
                    </div>
                    <div class="field">
                        <input type="text" name="nombre-evento" id="event-name" placeholder="Nombre del Evento" required>
                    </div>
                    
                    <div class="field">
                        <textarea name="description" id="event-detail" cols="30" rows="5" require placeholder="Descripción"></textarea>
                    </div>
                </div>

                <div class="col">
                    <div class="field">
                        <select name="provincias" id="select-province" required>
                            <!-- se cargan las provincias de la base de datos -->
                            <option value="0" selected >Provincia</option>
                        </select>
                    </div>
                    <div class="field">
                        <select name="cantones" id="select-canton" required>
                            <!-- se cargan los cantones de la base de datos -->
                            <option value="0" selected >Cantón</option>
                        </select>
                    </div>

                    <div class="field">
                        <input type="text" name="direccion-evento" id="direction" placeholder="Dirección" required>
                    </div>

                    <div class="field">
                        <input type="datetime-local" id="date-time" name="fecha-hora" required />
                    </div>
                    <div class="col_2">
                        <div class="field">
                            <input type="text" id="duration" name="duration" data-mask="000" placeholder="Horas de duración" required />
                        </div>
                        <div class="field">
                            <input type="text" id="quotas" name="quotas" data-mask="00000" placeholder="Cupo máximo" required />
                        </div>
                    </div>
                </div>
            </div><!-- .col_2 -->
            <p>Servicios</p>
            <div class="request-form-services" id="request-form-services">
                <!-- <div class="field">
                    <input type="checkbox" id="service-1" name="service-1" />
                    <label for="horns"><i class="fa-solid fa-burger"></i> Alimentacion</label>
                </div> -->
            </div>
            <div class="submit col_2">
                <input type="submit" class="btn btn_red" value="Solicitar">
            </div>
        </form>
    </div>
</div>