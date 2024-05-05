DROP PROCEDURE IF EXISTS sp_delete_evento;
DELIMITER $$
CREATE PROCEDURE sp_delete_evento(pEventoID INT, OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_EVENT INT DEFAULT(53000);
    DECLARE eventExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El evento que desea borrar no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO eventExists FROM evento WHERE id = pEventoID;
    IF (eventExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_EVENT;
    END IF;
	
	SET autocommit = 0;

	START TRANSACTION;
		DELETE FROM servicios_x_evento WHERE id_evento = pEventoID;
        DELETE FROM evento WHERE id = pEventoID;
    COMMIT;
END$$
DELIMITER ;