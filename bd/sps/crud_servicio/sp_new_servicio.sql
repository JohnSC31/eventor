DROP PROCEDURE IF EXISTS sp_new_servicio;
DELIMITER $$
CREATE PROCEDURE sp_new_servicio(pServicio VARCHAR(15), pIcono VARCHAR(50), pPrecio DECIMAL(7,2), pDescripcion VARCHAR(255), OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE DUPLICATE_SERVICE_NAME INT DEFAULT(53000);
    DECLARE serviceNameExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: Ya existe un servicio con ese nombre');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO serviceNameExists FROM servicio WHERE servicio = pServicio;
    IF (serviceNameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_SERVICE_NAME;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO servicio (servicio, icono, precio, descripcion) VALUES (pServicio, pIcono, pPrecio, pDescripcion);
    COMMIT;
END$$
DELIMITER ;