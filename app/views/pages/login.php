<div class="login-container">
    <h1>Iniciar Sesión</h1>
    <p>Ingresa a la plataforma como empresa</p>


    <div class="form-container">
        <form id="login-form" method="post" enctype="multipart/form-data">
            <div class="field">
                <input type="email" name="correo electronico" id="email" placeholder="Correo">
            </div>
            <div class="field">
                <input type="password" name="contraseña" id="pass" placeholder="Contraseña">
            </div>
            <div class="submit col_2">
                <a href="<?php echo URL_PATH; ?>recovery" class="btn btn_black">Recuperar contraseña</a>
                <input type="submit" class="btn btn_green" value="Ingresar">
            </div>
        </form>
    </div>
</div>
