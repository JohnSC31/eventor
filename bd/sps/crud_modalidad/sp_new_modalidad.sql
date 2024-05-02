DROP PROCEDURE IF EXISTS sp_new_modalidad;
DELIMITER $$
CREATE PROCEDURE sp_new_modalidad(pModalidad VARCHAR(10), pPrecio DECIMAL(7,2), OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE DUPLICATE_MODE_NAME INT DEFAULT(53000);
    DECLARE modeNameExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: Ya existe una modalidad con ese nombre');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO modeNameExists FROM modalidad WHERE modalidad = pModalidad;
    IF (modeNameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_MODE_NAME;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO modalidad (modalidad, precio) VALUES (pModalidad, pPrecio);
    COMMIT;
END$$
DELIMITER ;