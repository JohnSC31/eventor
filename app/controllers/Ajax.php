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

            // print_r($eventList);

            ?>
            <ul class="event-icon-container">
                <li change-event="1" class="active"><i class="fa-solid fa-utensils"></i></li>
                <li change-event="2" class=""><i class="fa-solid fa-people-group"></i></li>
                <li change-event="3" class=""><i class="fa-solid fa-person-running"></i></li>
                <li change-event="4" class=""><i class="fa-solid fa-chalkboard-user"></i></li>
            </ul>
            <div class="event-detail-container">
                <div class="event-detail" id="type-event-1">
                    <h3>Cenas</h3>
                    <p>Estrecha relaciones con tus clientes o mejora el ambiente de tu empresa con una cena de calidad 
                        sin preocuparte por los detalles.
                    </p>
                </div>
                
                <div class="event-detail" id="type-event-2">
                    <h3>Reuniones</h3>
                    <p>Estrecha relaciones con tus clientes o mejora el ambiente de tu empresa con una cena de calidad 
                        sin preocuparte por los detalles.
                    </p>
                </div> 

                <div class="event-detail" id="type-event-3">
                    <h3>Actividades deportivas</h3>
                    <p>Estrecha relaciones con tus clientes o mejora el ambiente de tu empresa con una cena de calidad 
                        sin preocuparte por los detalles.
                    </p>
                </div> 

                <div class="event-detail" id="type-event-4">
                    <h3>Capacitaciones</h3>
                    <p>Estrecha relaciones con tus clientes o mejora el ambiente de tu empresa con una cena de calidad 
                        sin preocuparte por los detalles.
                    </p>
                </div> 
            </div>
            <?php

        }


    }


    $initClass = new Ajax;

?>