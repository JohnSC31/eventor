// VARIABLES GLOBALES
const URL_PATH = $('body').attr('data-url').replace(/[\\]/gi,'/');
const AJAX_URL = URL_PATH + 'app/controllers/Ajax.php';

(function () {
    "use strict";
  
    document.addEventListener('DOMContentLoaded', function (){
      // Despues de cargar todo el DOM se ejecuta el codigo

      //LOGIN
      $("body").on("submit", "form#admin_login", loginAdmin);

      // APERTURA DE LOS MODALS
      $("body").on("click", "[data-modal]", openModal);
      $("body").on("click", "[close-modal]", closeModal);

      // NAVEGACION DE ADMINISTRACION
      $("body").on("click", "[data-admin-nav]", function(e){
        e.stopPropagation();
        adminNavigation(e.currentTarget);
      });

      //LOGOUT
      $("body").on("click", "[data-admin-logout]", logoutAdmin);

      // carrusel de imagenes del producto
      $("body").on("click", "[data-carrousel-pass]", function(e){
        e.stopPropagation();
        changeCarrouselImage(e.currentTarget);
      });

      
      
  
    }); // end DOMContentLoaded
  
  
})();

// ///////////////// *******************************  FUNCIONES  ****************************** /////////////////////

// FUNCION PARA ABRIR Y CARGAR UN MODAL
function openModal(e){
  e.preventDefault();
  const modalName = $(this).attr('data-modal');
  const modalData = $(this).attr('data-modal-data') !== undefined ? JSON.parse($(this).attr('data-modal-data')) : {};

  const myData = {
    'ajaxMethod': 'loadModal',
    'modal': modalName,
    'data': modalData
  }

  $.ajax({
    url: AJAX_URL,
    type:'POST',
    dataType:'html',
    data: myData
  }).done(function(data){
    // console.log(data);
    $('div#modal_container').html(data);
    $('div#modal_container').css('display', 'block'); // estaba en flex
    $('body').css('overflow', 'hidden');

    // acciones para los modals
    if(modalName === "employee"){
      loadModalEmployeePaids();
    }
  });
}

// FUNCION PARA CERRAR UN MODAL
function closeModal(e = false){
  if(e) e.preventDefault();
  $('div.modal_container').css('display', 'none');
  $('div#modal_container').html('');
  $('body').css('overflow', 'auto');
  // if($('div.notification')) $('div.notification').remove();
}

// Funcionalidad para mostrar la notificacion
///////////// ************************ NOTIFICACION ************************ ///////////////
function showNotification(message, success, timer = true){
  const notification = $('<div></div>');
  notification.addClass('notification');
  notification.addClass((success) ? 'n_success' : 'n_error');

  const text = $("<p></p>").text(message);

  notification.html(text);
  // insert before toma de paramatros (que insertar, antes de que se insetar)
  $("#notification_container").html("");
  $("#notification_container").html(notification);
  // ocultar y mostrar la notif
  setTimeout(()=>{
      notification.addClass('visible');
      setTimeout(()=>{
        if(timer){ // si timer entonces de deshace sola
          notification.removeClass('visible');
          setTimeout(()=>{
              notification.remove();
          }, 500)
        }    
      }, 3000)   
  }, 100)
}

// FUNCIONES PARA LA VALIDACION DE FORMULARIO
// validar inputs comunes
function validInput(input_value, max_length = false, msj = 'Campo Obligatorio'){
  
  if(input_value.length == 0){
    showNotification(msj, false);
    return false;
  }
  if(length > 0 && input_value.length > max_length){
    showNotification("Excede max de caracteres", false);
    return false;
  }

  return true;
  
}
// validar contrasenas
function validPassword(input_value){
  if(input_value.length == 0){
    showNotification("Ingrese una contreseña", false);
    return false;

  }else if (input_value.length < 7) {
    showNotification("Contreseña muy corta", false);
    return false;
  }
  return true
}
// validar correos
function validEmail(input_value){
  const validEmailPattern = /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
  if(input_value.length == 0){
    showNotification("Ingrese un correo", false);
    return false;
  }
  if (!validEmailPattern.test(input_value)){
    showNotification("Correo inválido", false);
    return false;

  }
  return true;
}
// valida archivos
function validFiles(fileInput){

  if(fileInput[0].files.length == 0){
    showNotification("Ingrese al menos una imagen", false);
    return false
  }

  for (var i = 0; i < fileInput[0].files.length; i++){
    
    if (fileInput[0].files[i] && fileInput[0].files[i].size < 2000000){ 
      return true;
    }
    var msjError;
    if(!fileInput[0].files[i]) msjError = 'Selecciona un archivo';

    if(fileInput[0].files[i] && fileInput[0].files[i].size > 2000000) msjError = 'El archivo seleccionado es muy grande';
    showNotification(msjError, false);
    return false;

  }

  
  
}

///////////// **************************************************************************************************** ///////////////
///////////// ********************************************** ADMIN AREA ****************************************** ///////////////
///////////// **************************************************************************************************** ///////////////

// FUNCION PARA INICIAR SESION DE ADMINSITRADOR
async function loginAdmin(e){
  e.preventDefault();
  // campos
  const input_email = $('input#email');
  const input_pass = $('input#pass');
  // validacion
  if(!validEmail(input_email.val())) return false;
  if(!validPassword(input_pass.val())) return false;

  // form data
  const loginFormData = new FormData();
  loginFormData.append('email', input_email.val());
  loginFormData.append('pass', input_pass.val());
  loginFormData.append('ajaxMethod', "adminLogin");  

  result = await ajaxRequest(loginFormData);
  showNotification(result.Message, result.Success, false);

  if(result.Success){
    setTimeout(()=>{
      window.location.href = URL_PATH + 'home';
    }, 1500)
  }
}

// CERRAR SESION DE ADMINSITRADOR
async function logoutAdmin(e){
  e.preventDefault();

  const logoutFormData = new FormData();
  logoutFormData.append('ajaxMethod', "adminLogout");  

  result = await ajaxRequest(logoutFormData);
  showNotification(result.Message, result.Success, false);

  if(result.Success){
    setTimeout(()=>{
      window.location.href = URL_PATH + 'login';
    }, 1500)
  }
}

// FUNCION PARA LA INICIALIZACION DE LAS DATATABLES
// ///////////////////////----------------------AJAX TABLE LOADES/ CARGADOR PARA LAS TABLAS AJAX ---------------------////////////////////////////
function initDataTable(table, ajaxMethod){
  const columns = getDataTableColumns(table);
  $("#"+table+"-table").DataTable({
    "responsive": true,
    "autoWidth": false,
    "processing": true,
    "serverSide": true,
    "ajax":{
      url: AJAX_URL,
      type:"POST",
      data: {ajaxMethod: ajaxMethod, table:table}
    },
    "columns": columns
  });
}

// FUNCION PARA OBTENER LAS COLUMNAS DE LAS DATATABLES
function getDataTableColumns(table){
  var columns = new Array();
  //COLS PARA VENTAS
  if(table === 'sells') columns = [{data: 'idSell'}, {data: 'clientName'}, {data: 'status'}, {data: 'date'}];

  // COLS PARA INVENTARIO
  if(table === 'inventory') columns = [{data: 'id'}, {data: 'name'}, {data: 'categorie'}, {data: 'price'}, {data: 'amount'}];

  // COLS PARA RECURSOS HUMANOS
  if(table === 'rrhh') columns = [{data: 'name'}, {data: 'email'}, {data: 'country'}, {data: 'rol'}, {data: 'department'}];

  // COLS PARA SERVICIO AL CLIENTE
  if(table === 'service') columns = [{data: 'client'}, {data: 'type'}, {data: 'employee'}, {data: 'date'}, {data: 'idOrder'}];

  //PARA LAS ACCIONES
  columns.push({data: 'actions', "orderable": false });

  return columns;
}

//RECARGAR LAS DATA TABLES
function refreshDataTables(table){
  $("#"+table+"-table").DataTable().ajax.reload();
}

// Funcionalidad de navegacion para el area de administracion
function adminNavigation(option){
  // style para el hover del menu
  if(!$(option).hasClass("active")){
    // se quita el active de todos y se coloca al actual
    $("ul#admin_nav li").removeClass("active");
    $(option).addClass("active")
  }
  // se ocultan todos los div
  $('div#dashboard_container > div').css('display', 'none');
  // se muestra el div correspondiente
  $('div#dashboard_container div.'+ $(option).attr("data-admin-nav") + '_container').css('display', 'block');

  // ACCIONES PARA LAS SECCIONES
  if($(option).attr("data-admin-nav") === 'sells'){

    if ( ! $.fn.DataTable.isDataTable('#sells-table') ) {
      initDataTable('sells', 'loadDataTableSells');
    }else{
      refreshDataTables('sells'); // recarga la tabla
    }
    
  }

  if($(option).attr("data-admin-nav") === 'inventory'){
    
    if ( ! $.fn.DataTable.isDataTable('#inventory-table') ) {
      initDataTable('inventory', 'loadDataTableInventory');
    }else{
      refreshDataTables('inventory'); // recarga la tabla
    }

    loadSelectOptions('catProduct'); // carga select de categoria
  }

  if($(option).attr("data-admin-nav") === 'human_resources'){

    if ( ! $.fn.DataTable.isDataTable('#rrhh-table') ) {
      initDataTable('rrhh', 'loadDataTableRrhh');
    }else{
      refreshDataTables('rrhh'); // recarga la tabla
    }

    loadSelectOptions('country'); // carga select de paises
    loadSelectOptions('rol'); // carga select de roles
    loadSelectOptions('department'); // carga select de departamentos
    loadSelectOptions('currency'); // carga select de monedas
  }

  if($(option).attr("data-admin-nav") === 'client_service'){
    
    if ( ! $.fn.DataTable.isDataTable('#service-table') ) {
      initDataTable('service', 'loadDataTableService');
    }else{
      refreshDataTables('service'); // recarga la tabla
    }
    
    loadSelectOptions('questionType'); // carga select de paises
  }

  if($(option).attr("data-admin-nav") === 'settings'){
    loadConfigList("roll"); // carga los roles
    loadConfigList("currency"); // carga las monedas
    loadConfigList("country"); // cargar paises
    loadSelectOptions('currency_country'); // carga select de paises
    loadConfigList("categorie"); // cargar categorias
    
  }
}



///////////// ************************ CARGAR LOS SELECT ************************ ///////////////
async function loadSelectOptions(idSelect){

  const selectFormData = new FormData();
  selectFormData.append("idSelect", idSelect);
  selectFormData.append('ajaxMethod', "loadSelectOptions");

  ajaxHTMLRequest(selectFormData, "select#" + idSelect);
}

///////////// ************************ AJAX BACKEND CONN ************************ ///////////////
// FUNCION QUE REALIZA LA CONECCION CON EL BACKEND
// Debe haber un campo en el form data indicando el metodo a utilizar en el ajax controller llamado 'ajaxMethod'
async function ajaxRequest(formData){
  return new Promise(resolve => {
    $.ajax({
      url:AJAX_URL,
      type:'POST',
      processData: false,
      contentType: false,
      data: formData
    }).done(function(data){
      console.log(data);
      resolve(JSON.parse(data));
    });
  });
}

// FUNCION QUE REALIZA LA CONECCION CON EL BACKEND Y RETORNA UN HTML
// Debe haber un campo en el form data indicando el metodo a utilizar en el ajax controller llamado 'ajaxMethod'
// html container indica el contenedor en el cual va ser insertado el html es un string indicando el id
async function ajaxHTMLRequest(formData, html_container){
$.ajax({
  url: AJAX_URL,
  type:'POST',
  processData: false,
  contentType: false,
  dataType:'html',
  data: formData
}).done(function(data){
  $(html_container).html(data);
});
}


  