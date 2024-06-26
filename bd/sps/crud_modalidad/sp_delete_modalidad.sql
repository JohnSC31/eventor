DROP PROCEDURE IF EXISTS sp_delete_modalidad;
DELIMITER $$
CREATE PROCEDURE sp_delete_modalidad(pModalidadID TINYINT, OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_MODE INT DEFAULT(53000);
    DECLARE modeExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: La modalidad no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO modeExists FROM modalidad WHERE id = pModalidadID;
    IF (modeExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_MODE;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        DELETE FROM modalidad WHERE id = pModalidadID;
    COMMIT;
END$$
DELIMITER ;