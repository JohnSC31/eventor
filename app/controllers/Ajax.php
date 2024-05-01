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

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError)){
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
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);

            }else{
                // se inicia sesion con los datos
                $clientSession = array(
                    'SESSION' => TRUE,
                    'CID' => $clientData['id'],
                    'EMAIL' => $clientData['correo'],
                    'COMPANY' => $clientData['empresa'],
                    'DETAIL' => $clientData['detalle'],
                    'PROVINCE' => $clientData['provincia'],
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

        // LOGIN 
        private function clientEdit($client){

            $this->db->query("CALL sp_edit_cliente(?, ?, ?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $client['idCliente']);
            $this->db->bind(1, $client['idCanton']);
            $this->db->bind(2, $client['companyName']);
            $this->db->bind(3, $client['companDetail']);
            $this->db->bind(4, $client['phone']);
            $this->db->bind(5, $client['email']);
            $this->db->bind(6, $client['pass']);

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError)){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Cambios guardados correctamente");
            }
        }

        // ------------------- METODOS DE EVENTO ----------------------------

        // CREACION DE UN EVENTO 
        private function eventCreation($event){

            $this->db->query("CALL sp_new_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(2, $event['idCliente']);
            $this->db->bind(3, $event['idModalidad']);
            $this->db->bind(4, $event['idCanton']);
            $this->db->bind(5, $event['idTipoEvento']);
            $this->db->bind(6, $event['eventName']);
            $this->db->bind(7, $event['dateTime']);
            $this->db->bind(8, $event['details']);
            $this->db->bind(9, $event['duration']);
            $this->db->bind(10, $event['capacity']);
            $this->db->bind(11, $event['location']);

            $eventID = $this->db->results();
            
            if(!$eventID){
                $this->db->query("SELECT @variableMsgError");
                $varMsgError = $this->db->result();
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                // CREAR SERVICIOS TO-DO ***
                $this->addService($eventID);
                $this->ajaxRequestResult(true, "Cambios guardados correctamente");
            }
        }

        private function eventEdit($event){

            $this->db->query("CALL sp_new_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $event['idEvento']);
            $this->db->bind(2, $event['idCliente']);
            $this->db->bind(3, $event['idModalidad']);
            $this->db->bind(4, $event['idCanton']);
            $this->db->bind(5, $event['idTipoEvento']);
            $this->db->bind(6, $event['eventName']);
            $this->db->bind(7, $event['dateTime']);
            $this->db->bind(8, $event['details']);
            $this->db->bind(9, $event['duration']);
            $this->db->bind(10, $event['capacity']);
            $this->db->bind(11, $event['location']);

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError)){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                // CREAR SERVICIOS TO-DO ***
                $id = $this->db->result();
                $this->addService($event);
                $this->ajaxRequestResult(true, "Cambios guardados correctamente");
            }
        }

        // AGREGAR SERVICIO A UN EVENTO TO-DO ***
        private function addService($event){

            $this->db->query("CALL sp_new_servicio_evento(?, ?, @variableMsgError)");
            $this->db->bind(1, $event['idEvento']);
            $this->db->bind(2, $event['idServicio']);

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError)){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
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
                    <li change-event="<?php echo $event['id']; ?>" class="<?php echo get_object_vars($eventList[0]) == $event ? "active" : "";?>"><i class="<?php echo $event['icono']; ?>"></i></li>
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
                    <div class="event-item">
                        <div class="event-item-content">
                            <div class="event-icon-container">
                                <i class="<?php echo $event['icono']; ?>"></i>
                            </div>
                            <div class="event-summary">
                                <div class="event-item-header">
                                    <p><?php echo $event['tipoEvento']; ?></p>
                                    <p class="status"><?php echo $event['estadoEvento']; ?></p>
                                </div>
                                
                                <p><i class="fa-solid fa-calendar-days"></i> <?php echo $event['fechayHora']; ?></p>
                                <p> <i class="fa-solid fa-location-dot"></i> <?php echo $event['provincia'] + "," + $event['canton'] + ", " + $event['direccion']; ?></p>
                            </div>
                        </div>

                    </div><!-- .event-item -->
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
                    <li change-service="<?php echo $service['id']; ?>" class="<?php echo get_object_vars($serviceList[0]) == $service ? "active" : "";?>"><i class="<?php echo $service['icono']; ?>"></i></li>
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
        }


    }


    $initClass = new Ajax;

?>