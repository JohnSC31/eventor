<?php 

    $this->db->query("CALL sp_get_provincias()");
    $provinces = $this->db->results();

    $this->db->query("CALL sp_get_cantones_provincia(?, @variableMsgError)");
    $this->db->bind(1, $_SESSION['CLIENT']['PROVINCEID']);
    $cantons = $this->db->results();

    
?>

<div class="myModal modal-edit-client">
    <div class="modal_header">
        <button close-modal="" class="btn btn_red" aria-label="Cerrar cuadro de edición"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-content">
      
    <form id="edit-client-form" method="post" enctype="multipart/form-data">
            <div class="col_2 form-input-container">

                <div class="col">
                    <div class="field">
                        <input type="text" name="company-name" id="company-name" placeholder="Nombre de la empresa" required value="<?php echo $_SESSION['CLIENT']['COMPANY']; ?>">
                    </div>
                    <div class="field">
                        <input type="text" name="company-detail" id="company-detail" placeholder="Detalle de la empresa" required value="<?php echo $_SESSION['CLIENT']['DETAIL']; ?>">
                    </div>
                    <div class="field">
                        <select name="provincias" id="select-province" required>
                            <?php if(count($provinces) > 0){ ?>
                                <option value="">Provincias</option>
                                <?php foreach($provinces as $province) { ?>
                                    <option value="<?php echo $province->id ?>" <?php echo $_SESSION['CLIENT']['PROVINCEID'] ==  $province->id ? "selected" :""; ?> > <?php echo $province->nombre; ?> </option>
                                <?php }
                            }else{ ?>
                                <option value="">No hay provincias</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="field">
                        <select name="cantones" id="select-canton" required>
                            <?php if(count($cantons) > 0){ ?>
                                <option value="" selected >Cantones</option>
                                <?php foreach($cantons as $canton) { ?>
                                    <option value="<?php echo $canton->id ?>" <?php echo $_SESSION['CLIENT']['CANTONID'] ==  $canton->id ? "selected" :""; ?> > <?php echo $canton->nombre; ?> </option>
                                <?php }
                            }else{ ?>
                                <option value="">Cantón</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="col">
                    <div class="field">
                        <input type="text" id="phone" name="phone" data-mask="0000-0000" placeholder="Número de teléfono" required 
                        value="<?php echo $_SESSION['CLIENT']['PHONE']; ?>"/>
                    </div>
                    <div class="field">
                        <input type="email" name="correo electronico" id="email" placeholder="Correo" required value="<?php echo $_SESSION['CLIENT']['EMAIL']; ?>">
                    </div>
                    <!-- <div class="field">
                        <input type="password" name="contraseña" id="password" placeholder="Contraseña" required>
                    </div>
                    <div class="field">
                        <input type="password" name="confirmarContraseña" id="confirm-password" placeholder="Confirmar contraseña" required>
                    </div> -->
                </div>
            </div><!-- .form-input-container -->
            <div class="submit col_2">
                <input type="submit" class="btn btn_green" value="Guardar Cambios">
            </div>
        </form>

    </div><!-- .modal-content -->
</div>

<script src="<?php echo URL_PATH; ?>public/js/jquery.mask.js"></script>
<script>
    $("select#select-province").on("change", loadCantonsProvince);
</script>