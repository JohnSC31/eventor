<?php 
    // CONTROLLADOR PARA LAS PETICIONES AJAX Y CONECIONES CON LA BASE DE DATOS
    if (!$_SERVER['REQUEST_METHOD'] === 'POST') { // se verifica que sea una peticion autentica
	    die('Invalid Request');
    }

    require_once '../../../app/config.php';
    require_once '../../../app/lib/Db.php';
    


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

        // Metodo para la carga de los modals
        private function loadModal($data){
            require_once '../views/modals/'. $data['modal'] . '.php';
        }


        // Metodo de prueba
        private function foo($data){
            $this->ajaxRequestResult(true, $data['message']);
        }

        private function loadSelectOptions($select){

            // se cargan de un select
            if($select['idSelect'] ===  "catProduct"){
                // carga categorias de home
                $this->db->query("query");
                $categories = $this->db->results(); // se obtienen de la base de datos

                if(count($categories) > 0){ ?>
                    <option value="" selected >Categorias</option>
                    <?php foreach($categories as $categorie) { ?>
                        <option value="<?php echo $categorie->idTipoProducto ?>"> <?php echo $categorie->tipoProducto; ?> </option>
                    <?php }
                }else{ ?>
                    <option value="">No hay Categorias</option>
                <?php }
            }

        }

        // ------------------- METODOS DE ADMIN ----------------------------

        // REGISTRO DE UN ADMINISTRADOR 
        private function adminSignup($admin){

            $this->db->query("CALL sp_new_admin(?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $admin['idRol']);
            $this->db->bind(2, $admin['name']);
            $this->db->bind(3, $admin['email']);
            $this->db->bind(4, $admin['pass']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                // INICIAR LA SESION DEL ADMIN
                $this->adminLogin($client);
            }
        }

        // LOGIN 
        private function adminLogin($admin){

            $this->db->query("CALL sp_login_admin(?, ?, @variableMsgError)");
            $this->db->bind(1, $admin['email']);
            $this->db->bind(2, $admin['pass']);

            $adminData = $this->db->result();
            
            if(!$adminData){

                $this->db->query("SELECT @variableMsgError");
                $varMsgError = $this->db->result();
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);

            }else{
                // se inicia sesion con los datos
                $adminSession = array(
                    'SESSION' => TRUE,
                    'AID' => $adminData['id'],
                    'NAME' => $adminData['nombre'],
                    'EMAIL' => $adminData['correo'],
                    'ROL' => $adminData['rol']
                );
    
                $_SESSION['ADMIN'] = $adminSession;
    
                if(isset($_SESSION['ADMIN'])){
                    // retorna sin errores
                    $this->ajaxRequestResult(true, "Se ha iniciado sesion correctamente");
                }else{
                    $this->ajaxRequestResult(false, "Error al iniciar sesion");
                }
            } 
        }

        // LOGOUT DEL ADMIN
        private function adminLogout(){

            unset($_SESSION['ADMIN']); 

            if(!isset($_SESSION['ADMIN'])){
              
                $this->ajaxRequestResult(true, "Se ha cerrado sesion");
            }else{ 
                $this->ajaxRequestResult(false, "Error al cerrar sesion");
            }
        }

        // EDITAR ADMIN 
        private function adminEdit($admin){

            $this->db->query("CALL sp_edit_admin(?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $admin['idAdmin']);
            $this->db->bind(2, $admin['idRol']);
            $this->db->bind(3, $admin['name']);
            $this->db->bind(4, $admin['email']);
            $this->db->bind(5, $client['pass']);

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

        private function changeEventState($event){
            $this->db->query("CALL sp_set_estado_evento(?, ?, @variableMsgError)");
            $this->db->bind(1, $event['idEvent']);
            $this->db->bind(2, $event['idStatus']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se ha cambiado el estado del evento");
            }
        }

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

                    <?php if($eventData['idEstado'] == 1) { ?>
                        <button class="btn btn_green" update-status="2">Activar</button>
                        <button class="btn btn_red" update-status="3">Finalizar</button>
                    <?php }elseif($eventData['idEstado'] == 2) {?>
                        <button class="btn btn_red" update-status="3">Finalizar</button>
                    <?php }elseif($eventData['idEstado'] == 3) {?>
                        <button class="btn btn_green" update-status="2">Activar</button>
                    <?php }?>

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

        // obtener todos los clientes
        private function loadClients(){
            $this->db->query("CALL sp_get_clientes()");

            $clients = $this->db->results();

            
            if(!$clients){
                // no hay eventos
                ?>
                <div class="no-clients">
                    <p>No hay clientes</p>
                </div>
                <?php
            }else{
                // se cargan los eventos
                foreach($clients as $key => $client){
                    $client = get_object_vars($client);
                    ?>
                    <div class="client-item">
                        <div class="client-item-header">
                            <button class="btn btn_red" arial-label="Eliminar cliente <?php echo $client['empresa']; ?>" delete-client="<?php echo $client['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <div class="client-item-content">
                            <div class="client-pic">
                                <i class="fa-solid fa-user"></i> 
                            </div>
                            <div class="client-info">
                                <p><?php echo $client['empresa']; ?></p>
                                <p><?php echo $client['correo']; ?></p>
                                <p><i class="fa-solid fa-phone"></i> <?php echo $client['telefono'] ?></p>
                                <p><i class="fa-solid fa-location-dot"></i> <?php echo $client['provincia'] ?>, <?php echo $client['canton'] ?></p>
                            </div>
                        </div>

                    </div>
                    <?php
                }
            }
        }

        private function deleteClient($client){
            $this->db->query("CALL sp_delete_cliente(?, @variableMsgError)");
            $this->db->bind(1, $client['idClient']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se ha eliminado el cliente correctamente");
            }
        }

        // ------------------- METODOS DE TIPO DE EVENTO ----------------------------

        // CARGAR LOS TIPOS DE EVENTOS EN COFIGURACIONES
        private function loadSettingsEventType(){
            $this->db->query("CALL sp_get_tipos_evento()");

            $eventTypes = $this->db->results();

            
            if(!$eventTypes){
                // no hay eventos
                ?>
                <div class="no-events">
                    <p>No hay tipos de eventos</p>
                </div>
                <?php
            }else{
                // se cargan los eventos
                foreach($eventTypes as $key => $eventType){
                    $eventType = get_object_vars($eventType);
                    ?>
                    <div class="setting-item">
                        <div class="setting-content">
                            <p><?php echo $eventType['tipo_evento'];?></p>
                        </div>
                        <div class="setting-actions">
                            <button class="btn btn_green" 
                                edit-type-event='<?php echo json_encode($eventType); ?>'>
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <button class="btn btn_red" delete-type-event="<?php echo $eventType['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        
        // CREAR TIPO DE EVENTO
        private function createEventType($eventType){
            $this->db->query("CALL sp_new_tipo_evento(?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $eventType['typeEvent']);
            $this->db->bind(2, $eventType['icon']);
            $this->db->bind(3, $eventType['price']);
            $this->db->bind(4, $eventType['detail']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se ha creado el tipo de evento correctamente");
            }
        }

        // EDITAR TIPO DE EVENTO
        private function editEventType($eventType){
            $this->db->query("CALL sp_edit_tipo_evento(?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $eventType['idEventType']);
            $this->db->bind(2, $eventType['typeEvent']);
            $this->db->bind(3, $eventType['icon']);
            $this->db->bind(4, $eventType['price']);
            $this->db->bind(5, $eventType['detail']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se edito el tipo evento correctamente");
            }
        }

        // ELIMINAR TIPO DE EVENTO
        private function deleteEventType($eventType){
            $this->db->query("CALL sp_delete_tipo_evento(?, @variableMsgError)");
            $this->db->bind(1, $eventType['idEventType']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se ha eliminado el tipo de evento correctamente");
            }
        }

        // ------------------- METODOS DE SERVICIO ----------------------------
        private function loadSettingsServices(){
            $this->db->query("CALL sp_get_servicios()");

            $services = $this->db->results();

            
            if(!$services){
                // no hay eventos
                ?>
                <div class="no-events">
                    <p>No hay servicios</p>
                </div>
                <?php
            }else{
                // se cargan los eventos
                foreach($services as $key => $service){
                    $service = get_object_vars($service);
                    ?>
                    <div class="setting-item">
                        <div class="setting-content">
                            <p><?php echo $service['servicio'];?></p>
                        </div>
                        <div class="setting-actions">
                            <button class="btn btn_green" 
                                edit-service='<?php echo json_encode($service); ?>'>
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <button class="btn btn_red" delete-service="<?php echo $service['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        // CREAR SERVICIO
        private function createService($service){
            $this->db->query("CALL sp_new_servicio(?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $service['service']);
            $this->db->bind(2, $service['icon']);
            $this->db->bind(3, $service['price']);
            $this->db->bind(4, $service['detail']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se ha creado el servicio correctamente");
            }
        }

        // EDITAR SERVICIO
        private function editService($service){
            $this->db->query("CALL sp_edit_servicio(?, ?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $service['idService']);
            $this->db->bind(2, $service['service']);
            $this->db->bind(3, $service['icon']);
            $this->db->bind(4, $service['price']);
            $this->db->bind(5, $service['detail']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Servicio editado correctamente");
            }
        }

        // ELIMINAR SERVICIO
        private function deleteService($service){
            $this->db->query("CALL sp_delete_servicio(?, @variableMsgError)");
            $this->db->bind(1, $service['idService']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se ha eliminado el servicio correctamente");
            }
        }

        // ------------------- METODOS DE CARGA DE HTML ----------------------------

        // CARGAR LISTA DE EVENTOS POR ESTADO
        private function loadEventsByState($data){
            $this->db->query("CALL sp_get_eventos_estado(?, @variableMsgError)");
            $this->db->bind(1, $data['idStatus']);
            
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
                    <a href="<?php echo URL_ADMIN_PATH . "event/". $event['id']; ?>">
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
        private function loadServiceList($data){
            $this->db->query("CALL sp_get_servicios()");
            $serviceList = $this->db->results();

            //HTML
        }

        // CARGAR LA LISTA DE EVENTOS
        private function loadEventsTypeList($data){
            $this->db->query("CALL sp_get_tipos_evento()");
            $eventList = $this->db->results();

            //HTML
        }


    }


    $initClass = new Ajax;

?>