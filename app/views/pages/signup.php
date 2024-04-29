<div class="signup-container">
    <h1>Crea tu cuenta</h1>
    <p>Registra tu empresa en Eventor</p>


    <div class="form-container">
        <form id="signup-form" method="post" enctype="multipart/form-data">
            <div class="col_2 form-input-container">

                <div class="col">
                    <div class="field">
                        <input type="text" name="company-name" id="company-name" placeholder="Nombre de la empresa" required>
                    </div>
                    <div class="field">
                        <input type="text" name="company-detail" id="company-detail" placeholder="Detalle de la empresa" required>
                    </div>
                    <div class="field">
                        <select name="provincias" id="select-province" required>
                            <!-- se cargan las provincias de la base de datos -->
                            <option value="0" selected >Provincia</option>
                        </select>
                    </div>
                    <div class="field">
                        <select name="cantones" id="select-cantons" required>
                            <!-- se cargan los cantones de la base de datos -->
                            <option value="0" selected >Cantón</option>
                        </select>
                    </div>
                </div>

                <div class="col">
                    <div class="field">
                        <input type="text" name="nombre cliente" id="client-name" placeholder="Nombre del cliente" required>
                    </div>
                    <div class="field">
                        <input type="text" id="phone" name="phone" data-mask="0000-0000" placeholder="Número de teléfono" required />
                    </div>
                    <div class="field">
                        <input type="email" name="correo electronico" id="email" placeholder="Correo" required>
                    </div>
                    <div class="field">
                        <input type="password" name="contraseña" id="password" placeholder="Contraseña" required>
                    </div>
                </div>
            </div><!-- .form-input-container -->
            <div class="submit col_2">
            <a href="<?php echo URL_PATH; ?>login" class="btn btn_black">Iniciar Sesión</a>
                <input type="submit" class="btn btn_red" value="Registrarme">
            </div>
        </form>
    </div>
</div>
