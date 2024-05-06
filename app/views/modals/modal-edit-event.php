<?php 

    // obtener los datos del evento
    $this->db->query("CALL sp_get_info_evento(?, @variableMsgError)");
    $this->db->bind(1, $data['data']['idEvent']);

    $event = $this->db->result();

    // carga para los selects

    // se cargan las modalidales
    $this->db->query("CALL sp_get_modalidades()");
    $modalities = $this->db->results();

    // se cargan las modalidales
    $this->db->query("CALL sp_get_tipos_evento()");
    $eventTypes = $this->db->results();

    // se cargan las provincias
    $this->db->query("CALL sp_get_provincias()");
    $provinces = $this->db->results();

    // se cargan los cantones de una provincia
    $this->db->query("CALL sp_get_cantones_provincia(?, @variableMsgError)");

    $this->db->bind(1, $event['idProvincia']);
    $cantons = $this->db->results();

    // SERVICIOS
    $this->db->query("CALL sp_get_servicios()");
    $services = $this->db->results();

    // los servicios del evento
    $this->db->query("CALL sp_get_servicios_evento(?, @variableMsgError)");
    $this->db->bind(1, $data['data']['idEvent']);

    $eventServices = $this->db->results();
    
?>

<div class="myModal modal-edit-client">
    <div class="modal_header">
        <button close-modal="" class="btn btn_red" aria-label="Cerrar cuadro de edición"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-content">

        <form id="edit-event-form" method="post" enctype="multipart/form-data" id-event="<?php echo $data['data']['idEvent']?>">
            <div class="col_2">

                <div class="col">
                    <div class="field">
                        <select name="modalidad" id="select-modality" required>

                        <?php if(count($modalities) > 0){ ?>
                            <option value="">Modalidades</option>
                            <?php foreach($modalities as $modality) { ?>
                                <option value="<?php echo $modality->id ?>" <?php echo $modality->modalidad == $event['modalidad'] ? "selected" : ""; ?>> <?php echo $modality->modalidad . "(₡ " . $modality->precio . ")"; ?> </option>
                            <?php }
                        }else{ ?>
                            <option value="" selected >Modalidades</option>
                        <?php } ?>

                        </select>
                    </div>
                    <div class="field">
                        <select name="tipoEvento" id="select-event-type" required>
                            <?php if(count($eventTypes) > 0){ ?>
                                <option value="" >Tipos de eventos</option>
                                <?php foreach($eventTypes as $eventType) { ?>
                                    <option value="<?php echo $eventType->id ?>" <?php echo $eventType->tipo_evento == $event['tipo de evento'] ? "selected" : ""; ?>> <?php echo $eventType->tipo_evento . "(₡ " . $eventType->precio . ")"; ?> </option>
                                <?php }
                            }else{ ?>
                                <option value="" selected >Tipos de eventos</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="field">
                        <input type="text" name="nombre-evento" id="event-name" placeholder="Nombre del Evento" required value="<?php echo $event['nombre del evento']; ?>">
                    </div>
                    
                    <div class="field">
                        <textarea name="description" id="event-detail" cols="30" rows="5" require placeholder="Descripción"><?php echo $event['detalles']; ?></textarea>
                    </div>
                </div>

                <div class="col">
                    <div class="field">
                        <select name="provincias" id="select-province" required>
                            <?php if(count($provinces) > 0){ ?>
                                <option value="">Provincias</option>
                                <?php foreach($provinces as $province) { ?>
                                    <option value="<?php echo $province->id ?>" <?php echo $event['idProvincia'] ==  $province->id ? "selected" :""; ?> > <?php echo $province->nombre; ?> </option>
                                <?php }
                            }else{ ?>
                                <option value="">No hay provincias</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="field">
                        <select name="cantones" id="select-canton" required>
                            <?php if(count($cantons) > 0){ ?>
                                <option value="" selected >Cantones</option>
                                <?php foreach($cantons as $canton) { ?>
                                    <option value="<?php echo $canton->id ?>" <?php echo $event['idCanton'] ==  $canton->id ? "selected" :""; ?> > <?php echo $canton->nombre; ?> </option>
                                <?php }
                            }else{ ?>
                                <option value="">Cantón</option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="field">
                        <input type="text" name="direccion-evento" id="direction" placeholder="Dirección" required value="<?php echo $event['direccion']; ?>">
                    </div>

                    <div class="field">
                        <input type="datetime-local" id="date-time" name="fecha-hora" required value="<?php echo $event['fecha y hora']; ?>" />
                    </div>
                    <div class="col_2">
                        <div class="field">
                            <input type="text" id="duration" name="duration" data-mask="000" placeholder="Horas de duración" required  value="<?php echo $event['duracion']; ?>"/>
                        </div>
                        <div class="field">
                            <input type="text" id="quotas" name="quotas" data-mask="00000" placeholder="Cupo máximo" required value="<?php echo $event['cupos']; ?>" />
                        </div>
                    </div>
                </div>
            </div><!-- .col_2 -->
            <p>Servicios</p>
            <div class="request-form-services" id="request-form-services">
                <?php foreach($services as $key => $service){
                    ?>
                    <div class="field">
                        <input type="checkbox" id-service="<?php echo $service->id; ?>" name="<?php echo $service->servicio; ?>" 
                        <?php foreach($eventServices as $key => $eventService) {
                            echo $eventService->id == $service->id ? "checked" : "";   
                        }?>
                        />
                        <label for="<?php echo $service->servicio; ?>"><i class="<?php echo $service->icono; ?>"></i> <?php echo $service->servicio; ?></label>
                    </div>
                    <?php
                } ?>
            </div>
            <div class="submit col_2">
                <input type="submit" class="btn btn_green" value="Editar">
            </div>
        </form>

    </div><!-- .modal-content -->
</div>

<script src="<?php echo URL_PATH; ?>public/js/jquery.mask.js"></script>