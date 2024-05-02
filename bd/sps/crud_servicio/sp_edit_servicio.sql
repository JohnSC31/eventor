DROP PROCEDURE IF EXISTS sp_edit_servicio;
DELIMITER $$
CREATE PROCEDURE sp_edit_servicio(pServicioID TINYINT, pServicio VARCHAR(25), pIcono VARCHAR(50), pPrecio DECIMAL(7,2), pDescripcion VARCHAR(255), OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_SERVICE INT DEFAULT(53000);
	DECLARE DUPLICATE_SERVICE_NAME INT DEFAULT(53001);
    DECLARE serviceExists TINYINT DEFAULT 0;
    DECLARE serviceNameExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El servicio no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: Ya existe un servicio con ese nombre');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO serviceExists FROM servicio WHERE id = pServicioID;
    IF (serviceExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_SERVICE;
    END IF;

    SELECT COUNT(id) INTO serviceNameExists FROM servicio WHERE servicio = pServicio AND id != pServicioID;
    IF (serviceNameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_SERVICE_NAME;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        UPDATE servicio SET servicio = pServicio, icono = pIcono, precio = pPrecio, descripcion = pDescripcion;
    COMMIT;
END$$
DELIMITER ;