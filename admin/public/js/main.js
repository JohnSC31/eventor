// VARIABLES GLOBALES
const URL_PATH = $('body').attr('data-url').replace(/[\\]/gi,'/');
const AJAX_URL = URL_PATH + 'app/controllers/Ajax.php';

(function () {
    "use strict";
  
    document.addEventListener('DOMContentLoaded', function (){
      // Despues de cargar todo el DOM se ejecuta el codigo

      //LOGIN
      $("body").on("submit", "form#login-admin-form", loginAdmin);
      //LOGOUT
      $("body").on("click", "[data-admin-logout]", logoutAdmin);

      // APERTURA DE LOS MODALS
      $("body").on("click", "[data-modal]", openModal);
      $("body").on("click", "[close-modal]", closeModal);

      // HOME
      if($('body').attr('id') === 'home'){
        loadEventsByState(2);
        $("body").on("click", "[events-nav]", adminEventsNavigation);
        
      }

      // DETALLE DE EVENTO
      if($('body').attr('id') === 'event'){
        loadDetailEvent();
        $("body").on("click", "[update-status]", updateEventStatus);
      }

      // CLIENTS
      if($('body').attr('id') === 'clients'){
        loadAdminClients();
        // eliminar cliente
        $("body").on("click", "[delete-client]", deleteClient);
      }

      // SETTIGS / CONFIGURACIONES
      if($('body').attr('id') === 'settings'){
        // ------------------ TIPOS DE EVENTOS -------------------
        // FORM TIPO DE EVENTO
        $("body").on("submit", "form#event-type-form", eventTypeForm);
        $("body").on("click", "[cancel-form-type-event]", cancelEditEventType);
        loadSettingsEventType();
        // eliminar un tipo de evento
        $("body").on("click", "[delete-type-event]", deleteSettingEventType);
        // editar un evento
        $("body").on("click", "[edit-type-event]", loadEditEventType);

        // ------------------- SERVICIOS ---------------------
        $("body").on("submit", "form#service-form", serviceForm);
        $("body").on("click", "[cancel-form-service]", cancelEditService);
        loadSettingsServices();
         // eliminar un servicio
         $("body").on("click", "[delete-service]", deleteSettingService);
         // editar un servicio
         $("body").on("click", "[edit-service]", loadEditService);
      }

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
  showNotification(result.Message, result.Success);

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

// ///////////////// **********************  HOME  ********************* /////////////////////
function adminEventsNavigation(e){
  e.preventDefault();
  
  $('nav.admin-events-nav li').removeClass('active');

  $(this).addClass('active');

  loadEventsByState($(this).attr('status'));
}
// carga los eventos con el estado dado
function loadEventsByState(status){

  const loadEventsFormData = new FormData();
  loadEventsFormData.append("idStatus", status);
  loadEventsFormData.append('ajaxMethod', "loadEventsByState");
  
  ajaxHTMLRequest(loadEventsFormData, "div#admin-events-list");
}


// ///////////////// **********************  EVENT  ********************* /////////////////////
function loadDetailEvent(){
  const loadDetailEventFormData = new FormData();

  loadDetailEventFormData.append('id', $('div#event-detail-container').attr('id-event'));
  loadDetailEventFormData.append('ajaxMethod', "loadDetailEvent");
  
  ajaxHTMLRequest(loadDetailEventFormData, "div#event-detail-container");
}

async function updateEventStatus(e){
  e.preventDefault();

  const statusEventFormData = new FormData();

  statusEventFormData.append('idEvent', $('div#event-detail-container').attr('id-event'));
  statusEventFormData.append('idStatus', $(this).attr('update-status'));
  statusEventFormData.append('ajaxMethod', "changeEventState");

  result = await ajaxRequest(statusEventFormData);
  showNotification(result.Message, result.Success);

  if(result.Success){
    setTimeout(()=>{
      loadDetailEvent();
    }, 1500)
  }
}

// ///////////////// **********************  CLIENTS  ********************* /////////////////////
async function loadAdminClients(){

  const loadClientsFormData = new FormData();

  loadClientsFormData.append('ajaxMethod', "loadClients");
  
  ajaxHTMLRequest(loadClientsFormData, "div#admin-clients-container");
}

async function deleteClient(e){
  e.preventDefault();

  if(!confirm('La eliminación del cliente es permanente ¿desea continuar?')) return false;

  const settingFormData = new FormData();
  settingFormData.append('idClient', $(this).attr('delete-client'));

  settingFormData.append('ajaxMethod', "deleteClient");  

  result = await ajaxRequest(settingFormData);
  showNotification(result.Message, result.Success);

  if(result.Success){
    loadAdminClients();
  }
}

// ///////////////// **********************  SETTINGS  ********************* /////////////////////

// ------------------ CONFIGURACIONES PARA TIPO DE EVENTO ---------------------------
// VALIDACION DE FORMULARIO PARA CREAR UN EVENTO
async function eventTypeForm(e){
  e.preventDefault();
  const form_action = $(this).attr('action');
  // campos
  const input_eventType = $('input#eventType');
  const input_icon = $('input#eventType-icon');
  const input_price = $('input#eventType-price');
  const textarea_detail = $('textarea#eventType-detail');
  
  // validacion
  if(!validInput(input_eventType.val(), false, "Ingrese un tipo de evento")) return false
  if(!validInput(input_icon.val(), false, "Ingrese un icono")) return false
  if(!validInput(input_price.val(), false, "Ingrese un precio de evento")) return false
  if(!validInput(textarea_detail.val(), false, "Ingrese un detalle de tipo de evento")) return false

  // form data
  const settingFormData = new FormData();
  settingFormData.append('typeEvent', input_eventType.val());
  settingFormData.append('icon', input_icon.val());
  settingFormData.append('price', input_price.val());
  settingFormData.append('detail', textarea_detail.val());

  if(form_action === 'edit'){
    settingFormData.append('idEventType', $(this).attr('id-event'));
    settingFormData.append('ajaxMethod', "editEventType");
  }else{
    settingFormData.append('ajaxMethod', "createEventType");
  }

  result = await ajaxRequest(settingFormData);
  showNotification(result.Message, result.Success);

  if(result.Success){

    loadSettingsEventType();
    if(form_action === 'edit') cancelEditEventType();
    if(form_action === 'create') $(this)[0].reset();

  }

}

// FUNCION PARA CARGAR LOS TIPOS DE EVENTOS EN CONFIGURACIONES
async function loadSettingsEventType(){

  const loadSettingsFormData = new FormData();

  loadSettingsFormData.append('ajaxMethod', "loadSettingsEventType");
  
  ajaxHTMLRequest(loadSettingsFormData, "div#setting-list-event-type");
}

async function deleteSettingEventType(e){
  e.preventDefault();

  if(!confirm('La eliminación del tipo de evento es permanente ¿desea continuar?')) return false;

  const settingFormData = new FormData();
  settingFormData.append('idEventType', $(this).attr('delete-type-event'));

  settingFormData.append('ajaxMethod', "deleteEventType");  

  result = await ajaxRequest(settingFormData);
  showNotification(result.Message, result.Success);

  if(result.Success){
    loadSettingsEventType();
  }

}

// carga en el formulario el type de evento para editar
async function loadEditEventType(e){
  e.preventDefault();

  var eventType = JSON.parse($(this).attr('edit-type-event'));

  // carga en el form de crear evento
  $('input#eventType').val(eventType.tipo_evento);
  $('input#eventType-icon').val(eventType.icono);
  $('input#eventType-price').val(eventType.precio);
  $('textarea#eventType-detail').val(eventType.descripcion);

  // configuraciones para el formulario
  $('form#event-type-form').attr('action', "edit");
  $('form#event-type-form').attr('id-event', eventType.id);

  $('form#event-type-form div.submit input').val('Editar tipo de evento');

  // FOCUS AL FORMULARIO
  $('html').animate({
      scrollTop: $("div#event-type-form-container").offset().top
    },
    800 //speed
  );

  $("input#eventType").focus();
  
}

function cancelEditEventType(e = false){
  if(e) e.preventDefault();

  $('form#event-type-form')[0].reset();
  
  // configuraciones para el formulario
  $('form#event-type-form').attr('action', "create");
  $('form#event-type-form').attr('id-event', "");
  $('form#event-type-form div.submit input').val('Crear tipo de evento');
}

// ------------------ CONFIGURACIONES PARA SERVICIOS ---------------------------
// VALIDACION DE FORMULARIO PARA CREAR UN SERVICIO
async function serviceForm(e){
  e.preventDefault();
  const form_action = $(this).attr('action');
  // campos
  const input_service = $('input#service');
  const input_icon = $('input#service-icon');
  const input_price = $('input#service-price');
  const textarea_detail = $('textarea#service-detail');
  
  // validacion
  if(!validInput(input_service.val(), false, "Ingrese un servicio")) return false
  if(!validInput(input_icon.val(), false, "Ingrese un icono")) return false
  if(!validInput(input_price.val(), false, "Ingrese un precio de servicio")) return false
  if(!validInput(textarea_detail.val(), false, "Ingrese un detalle de servicio")) return false

  // form data
  const settingFormData = new FormData();
  settingFormData.append('service', input_service.val());
  settingFormData.append('icon', input_icon.val());
  settingFormData.append('price', input_price.val());
  settingFormData.append('detail', textarea_detail.val());

  if(form_action === 'edit'){
    settingFormData.append('idService', $(this).attr('id-service'));
    settingFormData.append('ajaxMethod', "editService");
  }else{
    settingFormData.append('ajaxMethod', "createService");
  }

  result = await ajaxRequest(settingFormData);
  showNotification(result.Message, result.Success);

  if(result.Success){
      loadSettingsServices();
      if(form_action === 'edit') cancelEditService();
      if(form_action === 'create') $(this)[0].reset();
  }

}

// FUNCION PARA CARGAR LOS SERVICIOS EN CONFIGURACIONES
async function loadSettingsServices(){

  const loadSettingsFormData = new FormData();

  loadSettingsFormData.append('ajaxMethod', "loadSettingsServices");
  
  ajaxHTMLRequest(loadSettingsFormData, "div#setting-list-service");
}

async function deleteSettingService(e){
  e.preventDefault();

  if(!confirm('La eliminación del servicio es permanente ¿desea continuar?')) return false;

  const settingFormData = new FormData();
  settingFormData.append('idService', $(this).attr('delete-service'));

  settingFormData.append('ajaxMethod', "deleteService");  

  result = await ajaxRequest(settingFormData);
  showNotification(result.Message, result.Success);

  if(result.Success){
    loadSettingsServices();
  }

}

// carga en el formulario el servicio para editar
async function loadEditService(e){
  e.preventDefault();

  var service = JSON.parse($(this).attr('edit-service'));

  // carga en el form de crear servicio
  $('input#service').val(service.servicio);
  $('input#service-icon').val(service.icono);
  $('input#service-price').val(service.precio);
  $('textarea#service-detail').val(service.descripcion);

  // configuraciones para el formulario
  $('form#service-form').attr('action', "edit");
  $('form#service-form').attr('id-service', service.id);

  $('form#service-form div.submit input').val('Editar servicio');

  $("input#service").focus(); // focus al formulario
  
}

function cancelEditService(e = false){
  if(e) e.preventDefault();

  $('form#service-form')[0].reset();
  
  // configuraciones para el formulario
  $('form#service-form').attr('action', "create");
  $('form#service-form').attr('id-service', "");
  $('form#service-form div.submit input').val('Crear servicio');
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


  