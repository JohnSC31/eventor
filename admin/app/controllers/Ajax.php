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
        
        // --------------------------- SESSION DEL ADMINISTRADOR -------------------------------------------
        private function adminLogin($admin){

            // se validan las credenciales
            $this->db->query("{ CALL Clickship_loginEmployee(?, ?) }");

            $this->db->bind(1, $admin['email']);
            $this->db->bind(2, $admin['pass']);

            $loggedEmployee = $this->db->result();

            if($this->isErrorInResult($loggedEmployee)){
                $this->ajaxRequestResult(false, $loggedEmployee['Error']);

            }else{

                // se inicia sesion de administrador
                $adminSession = array(
                    'SESSION' => TRUE,
                    'ID' => $loggedEmployee['empleadoID'],
                    'EMIAL' => $loggedEmployee['correo'],
                    'NAME' => $loggedEmployee['apellidos'],
                    'ROLE' => $loggedEmployee['rol'],
                    // 'ROLE' => 'Gerente General'
                );

                $_SESSION['ADMIN'] = $adminSession;

                if(isset($_SESSION['ADMIN'])){
                    $this->ajaxRequestResult(true, "Se ha iniciados sesion");
                }else{
                    $this->ajaxRequestResult(false, "Error al iniciar sesion");
                }
            }

        }

        private function adminLogout($admin){
            unset($_SESSION['ADMIN']); 

            if(!isset($_SESSION['ADMIN'])){
              
                $this->ajaxRequestResult(true, "Se ha cerrado sesion");
            }else{ 
                $this->ajaxRequestResult(false, "Error al cerrar sesion");
            }
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

        // --------------------------- SECCION DE VENTAS -------------------------------------------
        // metodo para cargar las data table de ventas
        private function loadDataTableSells($REQUEST){
            // se realiza la consulta a la base de datos
            $this->db->query("my query");
            // NULLOS PORQUE TRAEN TODOS LOS RESULTADOS

            $sells = $this->db->results(); // se obtienen de la base de datos
            $totalRecords = count($sells);

            // var_dump($employees);

            $dataTableArray = array();

            foreach($sells as $key => $row){
                $row = get_object_vars($row);
                $btnDetail = "<button type='button' class='btn btn-warning btn-sm' data-modal='order' data-modal-data='{\"idOrder\": ".$row['ordenID']."}'><i class='fa-solid fa-eye'></i></button>";

                $sub_array = array();
                $sub_array['idSell'] = $row['ordenID'];
                $sub_array['clientName'] = $row['nombreCliente'];
                $sub_array['status'] = $row['estado'];
                $sub_array['date'] = date('j-n-Y', strtotime($row['fecha']));
                $sub_array['actions'] = $btnDetail;
                $dataTableArray[] = $sub_array;
            }

            echo $this->dataTableOutput(intval($REQUEST['draw']), $totalRecords, $totalRecords, $dataTableArray);

        }

        //Params: Draw, TotalFiltrados, TotalRecords, Datos
        //Result: un array codificado en formato json
        //Prepara los datos de la consulta hecha y los ordena para ser leidos por las dataTables
        public function dataTableOutput($draw, $totalFiltered, $totalRecords, $data){
            // $output = array();
            $output = array(
                "draw"				=>	$draw,
                "recordsTotal"      =>  $totalFiltered,  // total number of records
                "recordsFiltered"   =>  $totalRecords, // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data"				=>  $data
            );
        
            return json_encode($output);
        }

        // METODO PARA VALIDAR LOS MENSAJES DE ERRORES DE LOS SP (TRUE SI HAY ERROR, FALSE SI NO)
        private function isErrorInResult($result){
            return (isset($result['Error']) && $result['Error'] != "");
        }


        // ------------------- METODOS DE ADMIN ----------------------------

        // SIGNUP 
        private function adminSignup($admin){

            $this->db->query("CALL sp_new_admin(?, ?, ?, ?, ?, ?, @variableMsgError)");
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
            $this->db->bind(1, $event['idEvento']);
            $this->db->bind(2, $event['idEstado']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Se ha cambiado el estado del evento");
            }
        }

        // ------------------- METODOS DE TIPO DE EVENTO ----------------------------

        // CREAR TIPO DE EVENTO
        private function createEventType($eventType){
            $this->db->query("CALL sp_new_tipo_evento(?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $eventType['eventType']);
            $this->db->bind(2, $eventType['icon']);
            $this->db->bind(3, $eventType['price']);
            $this->db->bind(4, $eventType['description']);

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
            $this->db->bind(1, $eventType['idTipoEvento']);
            $this->db->bind(2, $eventType['eventType']);
            $this->db->bind(3, $eventType['icon']);
            $this->db->bind(4, $eventType['price']);
            $this->db->bind(5, $eventType['description']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Cambios guardados correctamente");
            }
        }

        // ELIMINAR TIPO DE EVENTO
        private function deleteEventType($eventType){
            $this->db->query("CALL sp_delete_tipo_evento(?, @variableMsgError)");
            $this->db->bind(1, $eventType['idTipoEvento']);

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

        // CREAR SERVICIO
        private function createService($service){
            $this->db->query("CALL sp_new_servicio(?, ?, ?, ?, @variableMsgError)");
            $this->db->bind(1, $service['service']);
            $this->db->bind(2, $service['icon']);
            $this->db->bind(3, $service['price']);
            $this->db->bind(4, $service['description']);

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
            $this->db->bind(5, $service['description']);

            $this->db->execute();

            $this->db->query("SELECT @variableMsgError");
            $varMsgError = $this->db->result();
            
            if(!is_null($varMsgError['@variableMsgError'])){
                $this->ajaxRequestResult(false, $varMsgError['@variableMsgError']);
            }else{
                $this->ajaxRequestResult(true, "Cambios guardados correctamente");
            }
        }

        // ELIMINAR SERVICIO
        private function deleteService($service){
            $this->db->query("CALL sp_delete_servicio(?, @variableMsgError)");
            $this->db->bind(1, $service['idTipoEvento']);

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
                                
                                <p><i class="fa-solid fa-calendar-days"></i> <?php echo $event['fecha y hora']; ?></p>
                                <p> <i class="fa-solid fa-location-dot"></i> <?php echo $event['provincia'] + "," + $event['canton'] + ", " + $event['direccion']; ?></p>
                                <p> Precio: <?php echo $event['precio total']; ?></p>
                            </div>
                        </div>

                    </div><!-- .event-item -->
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