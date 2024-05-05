<?php

    class Views{
        
        public function __construct(){

        }

        // METODO PARA CARGAR LAS VISTAS GENERALES
        private function loadView($viewName, $data = []){
            // chequea si la vista existe
            if(!file_exists('../app/views/'. $viewName . '.php')){
                die('la vista no existe');

            }else{
                // lo requerimos
                require_once '../app/views/inc/header.php';
                require_once '../app/views/'. $viewName . '.php';
                require_once '../app/views/inc/footer.php';
            }
        }

        // METODO PARA CARGAR LOS MODALS
        private function loadModal($modalName, $data = false){
            require_once '../views/'. $modalName . '.php';
        }

        // METODO PARA OBTENER LOS ATRIBUTOS DE LAS PAGINAS
        private function getPageData($id, $title){
            $data = array(
                'TITLE' => $title,
                'ID' => $id
            ); 
            return $data;
        }
        
        // VALIDAR LA SESION DEL USUARIO PARA EL ACCESO A LA PAGINA
        // params: bool, string
        private function validUserSession($userSession, $destiny){

            if($userSession){
                // valida que el usuario tenga sesion para entrar
                if(!isset($_SESSION['ADMIN']['SESSION'])) header('Location:'.URL_ADMIN_PATH.$destiny);
            }else{
                // valida que el usuario no tenga sesion para entrar
                if(isset($_SESSION['ADMIN']['SESSION'])) header('Location:'.URL_ADMIN_PATH.$destiny);
            }

            
        }



        // METODOS PARA CARGAR LAS VISTAS

        // CARGA DEL HOME
        public function home(){
            $this->validUserSession(true, 'login');
            
            $data = $this->getPageData('home','Administracion');
            $this->loadView('pages/home', $data); // se carga la vista necesaria
        }
        // CARGA DEL LOGIN
        public function login(){
            $this->validUserSession(false, 'home');
            $data = $this->getPageData('login','Inicio de sesión');
            $this->loadView('pages/login', $data); // se carga la vista necesaria
        }

        // CARGA DEL clientes
        public function clients(){
            $this->validUserSession(true, 'login');
            
            $data = $this->getPageData('clients','Administracion');
            $this->loadView('pages/clients', $data); // se carga la vista necesaria
        }

        // CARGA DEL configuraciones
        public function admins(){
            $this->validUserSession(true, 'login');
            
            $data = $this->getPageData('admins','Administracion');
            $this->loadView('pages/admins', $data); // se carga la vista necesaria
        }

        // CARGA DEL HOME
        public function settings(){
            $this->validUserSession(true, 'login');
            
            $data = $this->getPageData('settings','Administracion');
            $this->loadView('pages/settings', $data); // se carga la vista necesaria
        }
    
        // CARGA DE DETALLE DE EVENTO
        public function event($idEvent){
            $this->validUserSession(true, 'login');
            $data = $this->getPageData('event','Detalle de evento');
            $data['idEvent'] = $idEvent; 
            $this->loadView('pages/event', $data); // se carga la vista necesaria
        }

    }



?>