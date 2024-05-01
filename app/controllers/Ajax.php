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

        // SIGNUP 
        private function clientSignup($client){


            $this->ajaxRequestResult(true, $client['clientName']);
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
                    'NAME' => $clientData['empresa'],
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

        // CARGAR LA LISTA DE SERVICIOS
        private function loadHomeServiceList($data){
            $this->db->query("CALL sp_get_servicios()");
            $serviceList = $this->db->results();
            
            // var_dump($serviceList);
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


    }


    $initClass = new Ajax;

?>