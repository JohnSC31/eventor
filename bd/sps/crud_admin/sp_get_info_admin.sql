DROP PROCEDURE IF EXISTS sp_get_info_admin;
DELIMITER $$
CREATE PROCEDURE sp_get_info_admin(pAdminID TINYINT, OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_ADMIN INT DEFAULT(53000);
    DECLARE adminExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: No se pudo desplegar la información solicitada ya que el administrador elegido no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO adminExists FROM administrador WHERE id = pAdminID;
    IF (adminExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_ADMIN;
    END IF;

	SELECT a.id, a.nombre, a.correo, r.rol FROM administrador AS a
    JOIN rol AS r ON r.id = a.id_rol WHERE a.id = pAdminID;
END$$
DELIMITER ;