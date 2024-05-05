<?php 
    // CONTROLLADOR PARA LAS PETICIONES AJAX Y CONECIONES CON LA BASE DE DATOS
    if (!$_SERVER['REQUEST_METHOD'] === 'POST') { // se verifica que sea una peticion autentica
	    die('Invalid Request');
    }

    require_once '../config.php';
    require_once '../lib/Db.php';


    class Ajax {
        private $controller = "Ajax";
        private $ajaxMethod;
        private $data;
        private $db;

        public function __construct(){
            $this->db = new Db;
            $this->ajaxMethod = isset($_POST['ajaxMethod']) ? $_POST['ajaxMethod'] : NULL ;
            unset($_POST['ajaxMethod']);

            $this->data = [$_POST];

            if(method_exists($this->controller, $this->ajaxMethod)){
                call_user_func_array([$this->controller, $this->ajaxMethod], $this->data);
            }else{
                $this->ajaxRequestResult(false, "Metodo inexistente");
            }
        }

        //E: bool, str
        //S: none
        // Metodo para enviar las respuestas de ajax al js mediante un echo
        private function ajaxRequestResult($success = false, $message = 'Error desconocido', $dataResult = NULL){
            $result = array(
                'Success' => $success,
                'Message' => $message,
                'Data'    => $dataResult
            );
            echo json_encode($result);
        }


        // Metodo de prueba
        private function foo($data){
            $this->ajaxRequestResult(true, $data['message']);
        }

        // METODO PARA ABRIR UN MODAL
        private function loadModal($data){
            require_once '../views/modals/modal-'. $data['modal'] . '.php';
        }

        // ------------------- METODOS DE USUARIO ----------------------------

        // SIGNUP 
        private function clientSignup($client){

            $this->db->query("CALL sp_new_cliente(?, ?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $client['idCanton']);
            $this->db->bind(2, $client['companyName']);
            $this->db->bind(3, $client['companDetail']);
            $this->db->bind(4, $client['phone']);
            $this->db->bind(5, $client['email']);
            $this->db->bind(6, $client['pass']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                // INICIAR LA SESION DEL CLIENTE
                $this->clientLogin($client);
            }
        }

        // LOGIN 
        private function clientLogin($client){

            $this->db->query("CALL sp_login_cliente(?, ?, @variableMsgError)");
            $this->db->bind(1, $client['email']);
            $this->db->bind(2, $client['pass']);

            $clientData = $this->db->result();
            
            if(!$clientData){

                $this->db->query("SELECT @variableMsgError");
                $varMsgError = $this->db->result();
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError'], "inicia sesion");

            }else{
                // se inicia sesion con los datos
                $clientSession = array(
                    'SESSION' => TRUE,
                    'CID' => $clientData['id'],
                    'EMAIL' => $clientData['correo'],
                    'COMPANY' => $clientData['empresa'],
                    'DETAIL' => $clientData['detalle'],
                    'PROVINCE' => $clientData['provincia'],
                    'PROVINCEID' => $clientData['idProvincia'],
                    'CANTONID' => $clientData['idCanton'],
                    'CANTON' => $clientData['canton'],
                    'PHONE' => $clientData['telefono']
                );
    
                $_SESSION['CLIENT'] = $clientSession;
    
                if(isset($_SESSION['CLIENT'])){
                    // retorna sin errores
                    $this->ajaxRequestResult(true, "Se ha iniciado sesion correctamente");
                }else{
                    $this->ajaxRequestResult(false, "Error al iniciar sesion");
                }

            } 
        }

        // LOGOUT DEL CLIENTE
        private function clientLogout(){
            unset($_SESSION['CLIENT']); 

            if(!isset($_SESSION['CLIENT'])){
              
                $this->ajaxRequestResult(true, "Se ha cerrado sesion");
            }else{ 
                $this->ajaxRequestResult(false, "Error al cerrar sesion");
            }
        }

        // EDITAR CLIENTE 
        private function clientEdit($client){

            $this->db->query("CALL sp_edit_cliente(?, ?, ?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $client['idCliente']);
            $this->db->bind(1, $client['idCanton']);
            $this->db->bind(2, $client['companyName']);
            $this->db->bind(3, $client['companDetail']);
            $this->db->bind(4, $client['phone']);
            $this->db->bind(5, $client['email']);
            $this->db->bind(6, $client['pass']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Cambios guardados correctamente");
            }
        }

        // ------------------- METODOS DE EVENTO ----------------------------

        // CREACION DE UN EVENTO 
        private function eventCreation($event){

            $this->db->query("CALL sp_new_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $_SESSION['CLIENT']['CID']);
            $this->db->bind(2, $event['idModality']);
            $this->db->bind(3, $event['idCanton']);
            $this->db->bind(4, $event['idEventType']);
            $this->db->bind(5, $event['name']);
            $this->db->bind(6, $event['dateTime']);
            $this->db->bind(7, $event['detail']);
            $this->db->bind(8, $event['duration']);
            $this->db->bind(9, $event['quotas']);
            $this->db->bind(10, $event['direction']);

            $eventInserted = $this->db->result();
            
            if(!$eventInserted){
                $this->db->query("SELECT @variableMsgError");
                $varMsgError = $this->db->result();
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                // AGREGAR SERVICIOS
                $idServices = json_decode($event['idServices'], true);
                foreach($idServices as $idService){

                    if(!$this->addService($eventInserted['id_evento'], $idService)){
                        $this->ajaxRequestResult(false, "Se ha producido un error al agregar los servicios");
                        return; // se acaba la ejecucion
                    }
                }
                $this->ajaxRequestResult(true, "Evento creado correctamente");
            }
        }

        // EDITAR UN EVENTO
        private function eventEdit($event){

            $this->db->query("CALL sp_edit_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $event['idEvent']);
            $this->db->bind(2, $_SESSION['CLIENT']['CID']);
            $this->db->bind(3, $event['idModality']);
            $this->db->bind(4, $event['idCanton']);
            $this->db->bind(5, $event['idEventType']);
            $this->db->bind(6, $event['name']);
            $this->db->bind(7, $event['dateTime']);
            $this->db->bind(8, $event['detail']);
            $this->db->bind(9, $event['duration']);
            $this->db->bind(10, $event['quotas']);
            $this->db->bind(11, $event['direction']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                // AGREGAR SERVICIOS
                $idServices = json_decode($event['idServices'], true);
                foreach($idServices as $key => $idService){

                    if(!$this->addService($event['idEvent'], $idService)){
                        $this->ajaxRequestResult(false, "Se ha producido un error al editar los servicios");
                        return; // se acaba la ejecucion
                    }
                }
                $this->ajaxRequestResult(true, "Evento editado correctamente");
            }
        }

        // AGREGAR SERVICIO A UN EVENTO
        private function addService($idEvent, $idService){

            $this->db->query("CALL sp_new_servicio_evento(?, ?, @variableMsgError)");
            $this->db->bind(1, $idEvent);
            $this->db->bind(2, $idService);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            return is_null($varMsgError['@variableMsgError']) ? true : false;
        }

        // OBTENER DATOS DE UN EVENTO
        private function loadDetailEvent($event){

            $this->db->query("CALL sp_get_info_evento(?, @variableMsgError)");
            $this->db->bind(1, $event['id']);

            $eventData = $this->db->result();
            
            if(!$eventData){
                $this->db->query("SELECT @variableMsgError");
                $varMsgError = $this->db->result();
                ?>
                <p class="error"> Error al cargar el evento </p>
                <?php
            }else{
                // HTML
                $eventServices = $this->getEventServices($event['id']);
                ?>
                <div class="event-detail-header">
                    <button class="btn btn_green" data-modal="edit-event" data-modal-data='{"idEvent": <?php echo $event['id'];?>}' ><i class="fa-solid fa-pencil"></i></button>
                    <button class="btn btn_red" delete-event="<?php echo $event['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                </div>

                <div class="event-sumary-container">
                    <div class="event-icon">
                        <i class="<?php echo $eventData['icono']?>"></i>
                    </div>
                    <div class="event-summary">
                        <div class="event-summary-header">
                            <p><?php echo $eventData['tipo de evento']; ?></p>
                            <p class="status"><?php echo $eventData['estado del evento']; ?></p>
                        </div>
                        <p><?php echo $eventData['nombre del evento']; ?></p>
                        <p><i class="fa-solid fa-calendar-days"></i> <?php echo $eventData['fecha y hora']; ?></p>
                        <p><i class="fa-solid fa-location-dot"></i> <?php echo $eventData['provincia'] . ", " . $eventData['canton'] .", ".$eventData['direccion'] ; ?></p>
                    </div>
                </div>
                <div class="event-details-container">
                    <div class="left-details">
                        <p>Modalidad: <?php echo $eventData['modalidad']; ?></p>
                        <p>Cupos: <?php echo $eventData['cupos']; ?></p>
                        <p>Duracion: <?php echo $eventData['duracion']; ?></p>
                        <p>Precio: ₡<?php echo $eventData['precio total']; ?></p>
                    </div>
                    <div class="">
                        <p><b>Detalle</b></p>
                        <p><?php echo $eventData['detalles']; ?></p>
                    </div>
                    <div>
                        <p><b>Servicios</b></p>
                        <?php
                        if($eventServices && count($eventServices) > 0){
                            foreach($eventServices as $key => $eventService){
                                $eventService = get_object_vars($eventService);
                                ?>
                                <p><i class="<?php echo $eventService['icono']?>"></i> <?php echo $eventService['servicio']?></p>
                                <?php
                            }
                        }else{
                            ?>
                            <p>No hay servicios</p>
                            <?php
                        }
                        ?> 
                    </div>
                </div>

                <?php
                
            }
        }

        // OBTENER LA LISTA DE SERVICIOS DE UN EVENTO
        // retorna un arreglo con toda la informacion de los servicios
        private function getEventServices($idEvent){
            $this->db->query("CALL sp_get_servicios_evento(?, @variableMsgError)");
            $this->db->bind(1, $idEvent);

            return $this->db->results();
        }

        // ELIMINAR UN EVENTO
        private function deleteEvent($event){

            $this->db->query("CALL sp_delete_evento(?, @variableMsgError)");
            $this->db->bind(1, $event['idEvent']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();

            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Evento eliminado correctamente");
            }
        }

        // ------------------- METODOS DE CARGA DE HTML ----------------------------

        // CARGAR LA LISTA DE EVENTOS
        private function loadHomeEventsList($data){
            $this->db->query("CALL sp_get_tipos_evento()");
            $eventList = $this->db->results();

            ?>
            <ul class="event-icon-container">
                <?php foreach($eventList as $key => $event){ 
                    $event = get_object_vars($event); ?>
                    <li change-event="<?php echo $event['id']; ?>" class="<?php echo get_object_vars($eventList[0]) == $event ? "active" : "";?>"><i class="<?php echo $event['icono']; ?>" role="button" aria-label="<?php echo $event['tipo_evento']; ?>"></i></li>
                <?php } ?>

            </ul>
            <div class="event-detail-container">
                <?php foreach($eventList as $key => $event){
                    $event = get_object_vars($event); ?>

                    <div class="event-detail" id="type-event-<?php echo $event['id']; ?>">
                        <h3><?php echo $event['tipo_evento']; ?></h3>
                        <p><?php echo $event['descripcion']; ?></p>
                    </div>
                <?php } ?>

            </div>
            <?php

        }

        // CARGAR LISTA DE EVENTOS DE UN CLIENTE POR ESTADO
        private function loadClientEventsByState($data){
            $this->db->query("CALL sp_get_eventos_cliente_estado(?, ?, @variableMsgError)");
            $this->db->bind(1, $data['idClient']);
            $this->db->bind(2, $data['idStatus']);
            
            $events = $this->db->results();
            
            if(!$events){
                // no hay eventos
                ?>
                <div class="no-events">
                    <p>No hay eventos</p>
                </div>
                <?php
            }else{
                // se cargan los eventos
                foreach($events as $key => $event){
                    $event = get_object_vars($event);
                    ?>
                    <a href="<?php echo URL_PATH . "event/". $event['id']; ?>">
                        <div class="event-item">
                            <div class="event-item-content">
                                <div class="event-icon-container">
                                    <i class="<?php echo $event['icono']; ?>"></i>
                                </div>
                                <div class="event-summary">
                                    <div class="event-item-header">
                                        <p><?php echo $event['tipo de evento']; ?></p>
                                        <p class="status"><?php echo $event['estado del evento']; ?></p>
                                    </div>
                                    <p><?php echo $event['nombre del evento']; ?></p>
                                    <p><i class="fa-solid fa-calendar-days"></i> <?php echo $event['fecha y hora']; ?></p>
                                    <p> <i class="fa-solid fa-location-dot"></i> <?php echo $event['provincia'] . "," . $event['canton'] . ", " . $event['direccion']; ?></p>
                                    <p> Precio: ₡<?php echo $event['precio total']; ?></p>
                                </div>
                            </div>

                        </div><!-- .event-item -->
                    </a>
                    <?php
                }
            }
        }

        // CARGAR LA LISTA DE SERVICIOS
        private function loadHomeServiceList($data){
            $this->db->query("CALL sp_get_servicios()");
            $serviceList = $this->db->results();

            ?>
            <div class="service-detail-container">
                <?php foreach($serviceList as $key => $service){
                    $service = get_object_vars($service); ?>

                    <div class="service-detail" id="service-<?php echo $service['id']; ?>">
                        <h3><?php echo $service['servicio']; ?></h3>
                        <p><?php echo $service['descripcion']; ?></p>
                    </div>
                <?php } ?>

            </div>

            <ul class="service-icon-container">
                <?php foreach($serviceList as $key => $service){ 
                    $service = get_object_vars($service); ?>
                    <li change-service="<?php echo $service['id']; ?>" class="<?php echo get_object_vars($serviceList[0]) == $service ? "active" : "";?>"><i class="<?php echo $service['icono']; ?>" role="button" aria-label="<?php echo $service['servicio']; ?>"></i></li>
                <?php } ?>

            </ul>
            <?php

        }

        // CARGA DE LOS SELECT DINAMICOS
        private function loadSelectOptions($data){

            if($data['idSelect'] == "select-province"){
                // se cargan las provincias
                $this->db->query("CALL sp_get_provincias()");
                $provinces = $this->db->results();

                if(count($provinces) > 0){ ?>
                    <option value="" selected >Provincias</option>
                    <?php foreach($provinces as $province) { ?>
                        <option value="<?php echo $province->id ?>"> <?php echo $province->nombre; ?> </option>
                    <?php }
                }else{ ?>
                    <option value="">No hay provincias</option>
                <?php }

            }

            if($data['idSelect'] == "select-canton"){

                // se cargan los cantones de una provincia
                $this->db->query("CALL sp_get_cantones_provincia(?, @variableMsgError)");

                $this->db->bind(1, $data['idProvince']);
                $cantons = $this->db->results();

                if(count($cantons) > 0){ ?>
                    <option value="" selected >Cantones</option>
                    <?php foreach($cantons as $canton) { ?>
                        <option value="<?php echo $canton->id ?>"> <?php echo $canton->nombre; ?> </option>
                    <?php }
                }else{ ?>
                    <option value="">Cantón</option>
                <?php }

            }

            if($data['idSelect'] == 'select-modality'){

                // se cargan las modalidales
                $this->db->query("CALL sp_get_modalidades()");
                $modalities = $this->db->results();
 
                if(count($modalities) > 0){ ?>
                    <option value="" selected >Modalidades</option>
                    <?php foreach($modalities as $modality) { ?>
                        <option value="<?php echo $modality->id ?>"> <?php echo $modality->modalidad . "(₡ " . $modality->precio . ")"; ?> </option>
                    <?php }
                }else{ ?>
                    <option value="" selected >Modalidades</option>
                <?php }
            }

            if($data['idSelect'] == 'select-event-type'){

                // se cargan las modalidales
                $this->db->query("CALL sp_get_tipos_evento()");
                $eventTypes = $this->db->results();
 
                if(count($eventTypes) > 0){ ?>
                    <option value="" selected >Tipos de eventos</option>
                    <?php foreach($eventTypes as $eventType) { ?>
                        <option value="<?php echo $eventType->id ?>"> <?php echo $eventType->tipo_evento . "(₡ " . $eventType->precio . ")"; ?> </option>
                    <?php }
                }else{ ?>
                    <option value="" selected >Tipos de eventos</option>
                <?php }
            }
        }

        // CARGA DE SERVICIOS FORMULARIO SOLICITAR EVENTO
        private function loadCheckBoxServicesForm(){

            $this->db->query("CALL sp_get_servicios()");
            $serviceList = $this->db->results();
            foreach($serviceList as $key => $service){
                ?>
                <div class="field">
                    <input type="checkbox" id-service="<?php echo $service->id; ?>" name="<?php echo $service->servicio; ?>" aria-label="Seleccionar el servicio de <?php echo $service->servicio; ?>"/>
                    <label for="<?php echo $service->servicio; ?>"><i class="<?php echo $service->icono; ?>"></i> <?php echo $service->servicio; ?></label>
                </div>
                <?php
            }
        }


    }


    $initClass = new Ajax;

?>