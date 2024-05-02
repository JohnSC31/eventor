DROP PROCEDURE IF EXISTS sp_edit_modalidad;
DELIMITER $$
CREATE PROCEDURE sp_edit_modalidad(pModalidadID TINYINT, pModalidad VARCHAR(30), precio DECIMAL(7,2), OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_MODE INT DEFAULT(53000);
	DECLARE DUPLICATE_MODE_NAME INT DEFAULT(53001);
    DECLARE modeExists TINYINT DEFAULT 0;
    DECLARE modeNameExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: La modalidad no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: Ya existe una modalidad con ese nombre');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO modeExists FROM modalidad WHERE id = pModalidadID;
    IF (modeExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_MODE;
    END IF;

    SELECT COUNT(id) INTO modeNameExists FROM modalidad WHERE modalidad = pModalidad AND id != pModalidadID;
    IF (modeNameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_MODE_NAME;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        UPDATE modalidad SET modalidad = pModalidad WHERE id = pModalidadID;
    COMMIT;
END$$
DELIMITER ;