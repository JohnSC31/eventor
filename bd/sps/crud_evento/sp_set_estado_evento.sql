DROP PROCEDURE IF EXISTS sp_set_estado_evento;
DELIMITER $$
CREATE PROCEDURE sp_set_estado_evento(pEventoID INT, pEstadoID TINYINT, OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_EVENT INT DEFAULT(53000);
	DECLARE NON_EXISTENT_STATE INT DEFAULT(53001);
    DECLARE eventExists TINYINT DEFAULT 0;
	DECLARE stateExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: No se puede mostrar la información solicitada ya que el evento no existe');
				WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: No se puede mostrar la información solicitada ya que el estado no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO eventExists FROM evento WHERE id = pEventoID;
    IF (eventExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_EVENT;
    END IF;

	SELECT COUNT(id) INTO stateExists FROM estado_evento WHERE id = pEstadoID;
    IF (stateExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_STATE;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
		UPDATE evento SET id_estado = pEstadoID WHERE id = pEventoID;
    COMMIT;
END$$
DELIMITER ;