// VARIABLES GLOBALES
const URL_PATH = $('body').attr('data-url').replace(/[\\]/gi,'/');
const AJAX_URL = URL_PATH + 'app/controllers/Ajax.php';

(function () {
    "use strict";
  
    document.addEventListener('DOMContentLoaded', function (){
      // Despues de cargar todo el DOM se ejecuta el codigo

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
      $("body").on("submit", "form#signup-form", clientSignupForm);
      
      //LOGIN FORM
      $("body").on("submit", "form#login-form", clientLoginForm);
        
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
  e.preventDefault();
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

function validPassword(input_value){

  if(input_value.length == 0){
    showNotification("Ingrese una contreseña", false);
    return false;

  }else if (input_value.length < 7) {
    showNotification("Contreseña muy corta", false);
    return false;

  }else if (input_value.length > 30) {
    showNotification("Contreseña muy larga", false);
    return false;

  }else if(!(/.*[a-z]/).test(input_value)){
    showNotification("Debe haber almenos una minuscula", false);
    return false;

  }else if(!(/.*[A-Z]/).test(input_value)){
    showNotification("Debe haber almenos una mayuscula", false);
    return false;

  }else if(!(/.*[0-9]/).test(input_value)){
    showNotification("Debe haber almenos un número", false);
    return false;
  }
  return true
}

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
  
  const input_client_name = $('input#client-name');
  const input_phone = $('input#phone');
  const input_email = $('input#email');
  const input_password = $('input#password');

  // validan los datos
  if(!validInput(input_company_name.val(), false, "Ingrese el nombre de empresa")) return false;
  if(!validInput(input_company_detail.val(), false, "Ingrese el detalle de empresa")) return false;
  if(!validInput(select_canton.val(), false, "Seleccione un canton")) return false;

  if(!validInput(input_client_name.val(), false, "Ingrese un nombre")) return false;
  if(!validInput(input_phone.val(), false, "Ingrese un telefono")) return false;
  if(!validEmail(input_email.val())) return false;
  if(!validPassword(input_password.val())) return false;

  const signupFormData = new FormData();
  signupFormData.append('companyName', input_company_name.val());
  signupFormData.append('companDetail', input_company_detail.val());
  signupFormData.append('idCanton', select_canton.val());

  signupFormData.append('clientName', input_client_name.val());
  signupFormData.append('phone', input_phone.val());
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
      window.location.href = URL_PATH + 'home';
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


///////////// ************************ AJAX BACKEND CONN ************************ ///////////////
// FUNCION QUE REALIZA LA CONECCION CON EL BACKEND
// Debe haber un campo en el form data indicando el metodo a utilizar en el ajax controller llamado 'ajaxMethod'
async function ajaxRequest(formData){
  return new Promise(resolve => {
    $.ajax({
      url:'app/controllers/Ajax.php',
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


  