DROP PROCEDURE IF EXISTS sp_delete_tipo_evento;
DELIMITER $$
CREATE PROCEDURE sp_delete_tipo_evento(pTipoEventoID TINYINT, OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_TYPE INT DEFAULT(53000);
    DECLARE typeExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El tipo de evento no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO typeExists FROM tipo_evento WHERE id = pTipoEventoID;
    IF (typeExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_TYPE;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        DELETE FROM tipo_evento WHERE id = pTipoEventoID;
    COMMIT;
END$$
DELIMITER ;