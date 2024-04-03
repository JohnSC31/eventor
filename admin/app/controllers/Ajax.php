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


    }


    $initClass = new Ajax;

?>