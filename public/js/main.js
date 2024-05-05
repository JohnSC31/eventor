// VARIABLES GLOBALES
const URL_PATH = $('body').attr('data-url').replace(/[\\]/gi,'/');
const AJAX_URL = URL_PATH + 'app/controllers/Ajax.php';

(function () {
    "use strict";
  
    document.addEventListener('DOMContentLoaded', function (){
      // Despues de cargar todo el DOM se ejecuta el codigo

      // Abrir un modal
      $("body").on("click", "[data-modal]", openModal);

      // Cerrar un modal
      $("body").on("click", "[close-modal]", closeModal);

      // boton de cerrar sesion
      $("body").on("click", "[log-out]", clientLogout);

      // HOME
      if($("body").attr('id') === 'home'){
        loadHomeEventsList();
        loadHomeServiceList();
        // cambiar de tipos de eventos
        $("body").on("click", "[change-event]", changeEvent);
        // cambiar servicios
        $("body").on("click", "[change-service]", changeService);
      }


      //SIGNUP FORM
      if($('body').attr('id') === 'signup'){
        $("body").on("submit", "form#signup-form", clientSignupForm);

        loadSelectOptions('select-province');
        $("select#select-province").on("change", loadCantonsProvince);
      }
      
      //LOGIN FORM
      $("body").on("submit", "form#login-form", clientLoginForm);

      // PERFIL
      if($("body").attr('id') === 'profile'){
        // Carga los otros eventos
        loadClientEventsByState(2); //carga eventos activos
        $("body").on("click", "[events-nav]", clientEventsNavigation);

        // editar usuario
        $("body").on("submit", "form#edit-client-form", editClientForm);
      }
      
      // SOLICITUD
      if($('body').attr('id') === 'request'){
        // carga de selects
        loadSelectOptions("select-modality");
        loadSelectOptions("select-event-type");

        loadSelectOptions('select-province');
        $("select#select-province").on("change", loadCantonsProvince);

        // carga los servicios
        loadCheckBoxServicesForm();

        // Validacion del formulario
        $("body").on("submit", "form#request-event-form", eventRequestForm);
      }

      // DETALLE DE EVENTO
      if($('body').attr('id') === 'event'){
        loadDetailEvent();

        // formulario
        $("body").on("submit", "form#edit-event-form", editEventForm);

        
        $("body").on("click", "[delete-event]", deleteEvent);
        
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
    $('div#modal_container').html(data);
    $('div#modal_container').css('display', 'block'); // estaba en flex
    $('body').css('overflow', 'hidden');

  });
}

// FUNCION PARA CERRAR UN MODAL
function closeModal(e){
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

///////////// ************************ VALIDACIONES FORMULARIOS ************************ ///////////////

// FUNCIONES PARA LA VALIDACION DE FORMULARIO
function validInput(input_value, max_length = false, msj = 'Campo Obligatorio'){
  
  if(input_value.length == 0){
    showNotification(msj, false);
    return false;
  }
  if(length > 0 && input_value.length > max_length){
    showNotification("Excede el máximo de caracteres", false);
    return false;
  }

  return true;
  
}
// VALIDACIONES PARA UNA CONTRSENA
function validPassword(input_value){

  if(input_value.length == 0){
    showNotification("Ingrese una contraseña", false);
    return false;

  }else if (input_value.length < 7) {
    showNotification("La contraseña es muy corta", false);
    return false;

  }else if (input_value.length > 30) {
    showNotification("La contraseña es muy larga", false);
    return false;

  }else if(!(/.*[a-z]/).test(input_value)){
    showNotification("La contraseña debe tener al menos una minúscula", false);
    return false;

  }else if(!(/.*[A-Z]/).test(input_value)){
    showNotification("La contraseña debe tener al menos una mayuscula", false);
    return false;

  }else if(!(/.*[0-9]/).test(input_value)){
    showNotification("La contraseña debe tener al menos un número", false);
    return false;
  }
  return true
}
// VALIDACIONES PARA UN CORREO
function validEmail(input_value){
  const validEmailPattern = /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
  if(input_value.length == 0){
    showNotification("Ingrese un correo", false);
    return false;
  }
  if (!validEmailPattern.test(input_value)){
    showNotification("El correo es inválido", false);
    return false;

  }
  return true;
}

///////////// ************************ CARGA SE SELECTS DINAMICOS ************************ ///////////////
async function loadSelectOptions(idSelect){

  const selectFormData = new FormData();
  selectFormData.append("idSelect", idSelect);
  selectFormData.append('ajaxMethod', "loadSelectOptions");

  ajaxHTMLRequest(selectFormData, "select#" + idSelect);
}

// ///////////////// **********************  HOME  ********************* /////////////////////

// funcion para cargar la lista de eventos en la pagina principal
function loadHomeEventsList(){

  const eventsFormData = new FormData();
  eventsFormData.append('ajaxMethod', "loadHomeEventsList");

  ajaxHTMLRequest(eventsFormData, "div#event-list-container");
}

// funcion para cargar la lista de servicios en la pagina principal
function loadHomeServiceList(){

  const eventsFormData = new FormData();
  eventsFormData.append('ajaxMethod', "loadHomeServiceList");

  ajaxHTMLRequest(eventsFormData, "div#service-list-container");
}

function changeEvent(e){
  e.preventDefault();
  const showEventId = $(this).attr('change-event');

  $('div.event-detail-container div.event-detail').css('display', 'none');
  $('ul.event-icon-container li').removeClass('active');
  

  $('div.event-detail-container div#type-event-'+showEventId).css('display', 'block');
  $(this).addClass('active');
  // console.log(showEventId);
  
}

function changeService(e){
  e.preventDefault();
  const showServiceId = $(this).attr('change-service');

  $('div.service-detail-container div.service-detail').css('display', 'none');
  $('ul.service-icon-container li').removeClass('active');
  

  $('div.service-detail-container div#service-'+showServiceId).css('display', 'block');
  $(this).addClass('active');
  // console.log(showEventId);
  
}


// ///////////////// **********************  SIGNUP  ********************* /////////////////////

// FUNCION PARA EL REGISTRO DE UN NUEVO USUARIO
async function clientSignupForm(e){
  e.preventDefault();

  // optienen los campos del formulario
  const input_company_name = $('input#company-name');
  const input_company_detail = $('input#company-detail');
  const select_canton = $('select#select-canton');
  
  const input_phone = $('input#phone');
  const input_email = $('input#email');
  const input_password = $('input#password');
  const input_confirmPassword = $('input#confirm-password');

  // validan los datos
  if(!validInput(input_company_name.val(), false, "Ingrese el nombre de empresa")) return false;
  if(!validInput(input_company_detail.val(), false, "Ingrese el detalle de empresa")) return false;
  if(!validInput(select_canton.val(), false, "Seleccione un canton")) return false;

  if(!validInput(input_phone.val(), false, "Ingrese un telefono")) return false;
  if(!validEmail(input_email.val())) return false;
  if(!validPassword(input_password.val())) return false;

  // se validan las contrasena
  if(input_password.val() !== input_confirmPassword.val()){
    showNotification("Las contraseñas no coinciden", false);
    return false;
  }

  const signupFormData = new FormData();
  signupFormData.append('companyName', input_company_name.val());
  signupFormData.append('companDetail', input_company_detail.val());
  signupFormData.append('idCanton', select_canton.val());

  signupFormData.append('phone', input_phone.val().replace(/[^\d]/g, ''));
  signupFormData.append('email', input_email.val());
  signupFormData.append('pass', input_password.val());

  signupFormData.append('ajaxMethod', "clientSignup");  

  result = await ajaxRequest(signupFormData);
  showNotification(result.Message, result.Success, false);

  if(result.Success){
    setTimeout(()=>{
      window.location.href = URL_PATH + 'home';
    }, 1500)
  }

}

// FUNCION PARA EL INICIO DE SESION DE UN USUARIO
async function clientLoginForm(e){
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
  loginFormData.append('ajaxMethod', "clientLogin");  

  result = await ajaxRequest(loginFormData);
  showNotification(result.Message, result.Success, true);

  if(result.Success){
    setTimeout(()=>{
      window.location.href = URL_PATH + 'profile';
    }, 1500)
  }

}

async function clientLogout(e){
  e.preventDefault();

   // form data
   const loginFormData = new FormData();
   loginFormData.append('ajaxMethod', "clientLogout");  
 
   result = await ajaxRequest(loginFormData);
   showNotification(result.Message, result.Success, true);
 
   if(result.Success){
     setTimeout(()=>{
       window.location.href = URL_PATH + 'home';
     }, 1500)
   }
}

async function loadCantonsProvince(e){
  e.preventDefault();

  if($(this).val() == ""){
    $( "select#select-canton").html('<option value="">Cantón</option>');
  }else{
    const selectFormData = new FormData();
    selectFormData.append("idSelect", 'select-canton');
    selectFormData.append("idProvince", $(this).val());
    selectFormData.append('ajaxMethod', "loadSelectOptions");
  
    ajaxHTMLRequest(selectFormData, "select#select-canton");
  }


}

// ///////////////// **********************  PROFILE  ********************* /////////////////////

function clientEventsNavigation(e){
  e.preventDefault();
  
  $('nav.client-events-nav li').removeClass('active');

  $(this).addClass('active');

  loadClientEventsByState($(this).attr('status'));
}

// carga los eventos de este cliente con el estado dado
function loadClientEventsByState(status){

  const loadEventsFormData = new FormData();
  loadEventsFormData.append("idStatus", status);
  loadEventsFormData.append("idClient", $("div#client-events-list").attr('idClient'));
  loadEventsFormData.append('ajaxMethod', "loadClientEventsByState");
  
  ajaxHTMLRequest(loadEventsFormData, "div#client-events-list");
}

async function editClientForm(e){
  e.preventDefault();

  // optienen los campos del formulario
  const input_company_name = $('input#company-name');
  const input_company_detail = $('input#company-detail');
  const select_canton = $('select#select-canton');
  
  const input_phone = $('input#phone');
  const input_email = $('input#email');
  // const input_password = $('input#password');
  // const input_confirmPassword = $('input#confirm-password');

  // validan los datos
  if(!validInput(input_company_name.val(), false, "Ingrese el nombre de empresa")) return false;
  if(!validInput(input_company_detail.val(), false, "Ingrese el detalle de empresa")) return false;
  if(!validInput(select_canton.val(), false, "Seleccione un canton")) return false;

  if(!validInput(input_phone.val(), false, "Ingrese un telefono")) return false;
  if(!validEmail(input_email.val())) return false;
  // if(!validPassword(input_password.val())) return false;

  // // se validan las contrasena
  // if(input_password.val() !== input_confirmPassword.val()){
  //   showNotification("Las contraseñas no coinciden", false);
  //   return false;
  // }

  const signupFormData = new FormData();
  signupFormData.append('companyName', input_company_name.val());
  signupFormData.append('companDetail', input_company_detail.val());
  signupFormData.append('idCanton', select_canton.val());

  signupFormData.append('phone', input_phone.val().replace(/[^\d]/g, ''));
  signupFormData.append('email', input_email.val());
  // signupFormData.append('pass', input_password.val());

  signupFormData.append('ajaxMethod', "clientEdit");  

  result = await ajaxRequest(signupFormData);
  showNotification(result.Message, result.Success, false);

  if(result.Success){
    setTimeout(()=>{
      closeModal();
    }, 1500)
  }
}
// ///////////////// **********************  REQUEST  ********************* /////////////////////
function loadCheckBoxServicesForm(){

  const loadServicesFormData = new FormData();
  loadServicesFormData.append('ajaxMethod', "loadCheckBoxServicesForm");
  
  ajaxHTMLRequest(loadServicesFormData, "div#request-form-services");
}

async function eventRequestForm(e){
  e.preventDefault();

  // optienen los campos del formulario
  const select_modality = $('select#select-modality');
  const select_event_type = $('select#select-event-type');
  const input_event_name = $('input#event-name');
  const textarea_event_detail = $('textarea#event-detail');

  const select_canton = $('select#select-canton');
  const input_direction = $('input#direction');
  const input_dateTime = $('input#date-time');
  const input_duration = $('input#duration');
  const input_quotas = $('input#quotas');
  
  // validaciones
  if(!validInput(select_modality.val(), false, "Seleccione una modalidad")) return false;
  if(!validInput(select_event_type.val(), false, "Seleccione un tipo de evento")) return false;
  if(!validInput(input_event_name.val(), false, "Ingrese el nombre del evento")) return false;
  if(!validInput(textarea_event_detail.val(), false, "Ingrese el detalle del evento")) return false;
  if(!validInput(select_canton.val(), false, "Seleccione un cantón")) return false;
  if(!validInput(input_direction.val(), false, "Ingrese la direccion del evento")) return false;
  if(!validInput(input_dateTime.val(), false, "Ingrese una fecha y hora")) return false;
  if(!validInput(input_duration.val(), false, "Ingrese una duración")) return false;
  if(!validInput(input_quotas.val(), false, "Ingrese un cupo")) return false;

  // se agregan los servicios requeridos
  var serviceList = [];

  // se agregan los servicios
  $( "div#request-form-services input" ).each(function() {
    if(this.checked) {
      serviceList.push($(this).attr('id-service'));
    }
  });
  
  //  Ingreso de los datos en el formdata
  const requestEventFormData = new FormData();
  requestEventFormData.append('idModality', select_modality.val());
  requestEventFormData.append('idEventType', select_event_type.val());
  requestEventFormData.append('name', input_event_name.val());
  requestEventFormData.append('detail', textarea_event_detail.val());
  requestEventFormData.append('idCanton', select_canton.val());
  requestEventFormData.append('direction', input_direction.val());
  requestEventFormData.append('dateTime', input_dateTime.val());
  requestEventFormData.append('duration', input_duration.val());
  requestEventFormData.append('quotas', input_quotas.val());

  requestEventFormData.append('idServices', JSON.stringify(serviceList));

  requestEventFormData.append('ajaxMethod', "eventCreation");

  result = await ajaxRequest(requestEventFormData);
  showNotification(result.Message, result.Success, true);

  if(result.Success){
    setTimeout(()=>{
      window.location.href = URL_PATH + 'profile';
    }, 1500)
  }
  
}


// ///////////////// **********************  EVENT  ********************* /////////////////////
function loadDetailEvent(){
  const loadDetailEventFormData = new FormData();

  loadDetailEventFormData.append('id', $('div#event-detail-container').attr('id-event'));
  loadDetailEventFormData.append('ajaxMethod', "loadDetailEvent");
  
  ajaxHTMLRequest(loadDetailEventFormData, "div#event-detail-container");
}

async function editEventForm(e){
  e.preventDefault();

  // optienen los campos del formulario
  const select_modality = $('select#select-modality');
  const select_event_type = $('select#select-event-type');
  const input_event_name = $('input#event-name');
  const textarea_event_detail = $('textarea#event-detail');

  const select_canton = $('select#select-canton');
  const input_direction = $('input#direction');
  const input_dateTime = $('input#date-time');
  const input_duration = $('input#duration');
  const input_quotas = $('input#quotas');
  
  // validaciones
  if(!validInput(select_modality.val(), false, "Seleccione una modalidad")) return false;
  if(!validInput(select_event_type.val(), false, "Seleccione un tipo de evento")) return false;
  if(!validInput(input_event_name.val(), false, "Ingrese el nombre del evento")) return false;
  if(!validInput(textarea_event_detail.val(), false, "Ingrese el detalle del evento")) return false;
  if(!validInput(select_canton.val(), false, "Seleccione un cantón")) return false;
  if(!validInput(input_direction.val(), false, "Ingrese la direccion del evento")) return false;
  if(!validInput(input_dateTime.val(), false, "Ingrese una fecha y hora")) return false;
  if(!validInput(input_duration.val(), false, "Ingrese una duración")) return false;
  if(!validInput(input_quotas.val(), false, "Ingrese un cupo")) return false;

  // se agregan los servicios requeridos
  var serviceList = [];

  // se agregan los servicios
  $( "div#request-form-services input" ).each(function() {
    if(this.checked) {
      serviceList.push($(this).attr('id-service'));
    }
  });
  
  //  Ingreso de los datos en el formdata
  const editEventFormData = new FormData();
  editEventFormData.append('idEvent', $(this).attr('id-event'));
  editEventFormData.append('idModality', select_modality.val());
  editEventFormData.append('idEventType', select_event_type.val());
  editEventFormData.append('name', input_event_name.val());
  editEventFormData.append('detail', textarea_event_detail.val());
  editEventFormData.append('idCanton', select_canton.val());
  editEventFormData.append('direction', input_direction.val());
  editEventFormData.append('dateTime', input_dateTime.val());
  editEventFormData.append('duration', input_duration.val());
  editEventFormData.append('quotas', input_quotas.val());

  editEventFormData.append('idServices', JSON.stringify(serviceList));

  editEventFormData.append('ajaxMethod', "eventEdit");

  result = await ajaxRequest(editEventFormData);
  showNotification(result.Message, result.Success, true);

  if(result.Success){
    setTimeout(()=>{
      closeModal();
      loadDetailEvent(); // se recarga el evento
    }, 1500)
  }
}

async function deleteEvent(e){
  e.preventDefault();

  const deleteEventFormData = new FormData();
  deleteEventFormData.append('idEvent', $(this).attr('delete-event'));
  deleteEventFormData.append('ajaxMethod', "deleteEvent");

  result = await ajaxRequest(deleteEventFormData);
  showNotification(result.Message, result.Success, true);

  if(result.Success){
    setTimeout(()=>{
      window.location.href = URL_PATH + 'profile';
    }, 1500)
  }
}

///////////// ************************ AJAX BACKEND CONN ************************ ///////////////
// FUNCION QUE REALIZA LA CONECCION CON EL BACKEND
// Debe haber un campo en el form data indicando el metodo a utilizar en el ajax controller llamado 'ajaxMethod'
async function ajaxRequest(formData){
  return new Promise(resolve => {
    $.ajax({
      url: AJAX_URL,
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

///////////// ************************ CARGAR LOS SELECT ************************ ///////////////
async function loadSelectOptions(idSelect){

  const selectFormData = new FormData();
  selectFormData.append("idSelect", idSelect);
  selectFormData.append('ajaxMethod', "loadSelectOptions");

  ajaxHTMLRequest(selectFormData, "select#" + idSelect);
}


  