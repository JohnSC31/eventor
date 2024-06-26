DROP PROCEDURE IF EXISTS sp_new_servicio_evento;
DELIMITER $$
CREATE PROCEDURE sp_new_servicio_evento(pEventoID INT, pServicioID TINYINT, OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_EVENT INT DEFAULT(53000);
    DECLARE NON_EXISTENT_SERVICE INT DEFAULT(53001);
    DECLARE eventExists TINYINT DEFAULT 0;
    DECLARE serviceExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El evento no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: El servicio no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO eventExists FROM evento WHERE id = pEventoID;
    IF (eventExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_EVENT;
    END IF;

    SELECT COUNT(id) INTO serviceExists FROM servicio WHERE id = pServicioID;
    IF (serviceExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_SERVICE;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO servicios_x_evento (id_evento, id_servicio) VALUES (pEventoID, pServicioID);

        UPDATE evento SET precio_total = precio_total + (SELECT precio FROM servicio WHERE id = pServicioID);
    COMMIT;
END$$
DELIMITER ;