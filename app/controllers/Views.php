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


        // METODOS PARA CARGAR LAS VISTAS

        // CARGA DEL HOME
        public function home(){
            $data = $this->getPageData('home','Tu organizador de eventos empresariales');
            $this->loadView('pages/home', $data); // se carga la vista necesaria
        }

        // CARGA DE INICIO DE SESION
        public function login(){
            $data = $this->getPageData('login','Inicio de Sesión');
            $this->loadView('pages/login', $data); // se carga la vista necesaria
        }

        // CARGA DE RECUPERAR CONTRASEÑA
        public function recovery(){
            $data = $this->getPageData('recovery','Recuperar contraseña');
            $this->loadView('pages/recovery', $data); // se carga la vista necesaria
        }


        // CARGA DE REGISTRO
        public function signup(){
            $data = $this->getPageData('signup','Registro de usuario');
            $this->loadView('pages/signup', $data); // se carga la vista necesaria
        }

        // CARGA DE PERFIL
        public function profile(){
            $data = $this->getPageData('profile','Perfil de usuario');
            $this->loadView('pages/profile', $data); // se carga la vista necesaria
        }

        // CARGA DE DETALLE DE EVENTO
        public function event(){
            $data = $this->getPageData('event','Detalle de evento');
            $this->loadView('pages/event', $data); // se carga la vista necesaria
        }

        // CARGA DE SOLICITAR EVENTO
        public function request(){
            $data = $this->getPageData('request','Solicitar evento');
            $this->loadView('pages/request', $data); // se carga la vista necesaria
        }


    }



?>