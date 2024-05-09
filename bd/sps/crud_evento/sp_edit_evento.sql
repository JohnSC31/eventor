DROP PROCEDURE IF EXISTS sp_edit_evento;
DELIMITER $$
CREATE PROCEDURE sp_edit_evento(pEventoID INT, pClienteID INT, pModalidadID TINYINT, pCantonID TINYINT, pTypeID TINYINT, 
                                pName VARCHAR(40), pDateTime DATETIME, pDetails VARCHAR(255), pDuration TINYINT, 
                                pCapacity INT, pLocation VARCHAR(255), OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_CLIENT INT DEFAULT(53000);
    DECLARE NON_EXISTENT_MODE INT DEFAULT(53001);
    DECLARE NON_EXISTENT_CANTON INT DEFAULT(53002);
    DECLARE NON_EXISTENT_TYPE INT DEFAULT(53003);
    DECLARE LARGE_NAME INT DEFAULT(53004);
    DECLARE DUPLICATE_NAME INT DEFAULT(53005);
    DECLARE INVALID_DATETIME INT DEFAULT(53006);
	DECLARE LARGE_DETAILS INT DEFAULT(53007);
    DECLARE INVALID_DURATION INT DEFAULT(53008);
    DECLARE INVALID_CAPACITY INT DEFAULT(53009);
    DECLARE clientExists TINYINT DEFAULT 0;
    DECLARE modeExists TINYINT DEFAULT 0;
    DECLARE cantonExists TINYINT DEFAULT 0;
    DECLARE typeExists TINYINT DEFAULT 0;
    DECLARE nameExists TINYINT DEFAULT 0;
    DECLARE precioTotal DECIMAL(10,2) DEFAULT 0.0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El cliente que desea editar no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: La modalidad que desea agregar no existe');
                WHEN @err_no = 53002 THEN
                    SET errorMessage = CONCAT('Error: El cantón que desea agregar no existe');
                WHEN @err_no = 53003 THEN
                    SET errorMessage = CONCAT('Error: El tipo de evento que desea agregar no existe');
                WHEN @err_no = 53004 THEN
                    SET errorMessage = CONCAT('Error: El nombre digitado es muy largo');
                WHEN @err_no = 53005 THEN
                    SET errorMessage = CONCAT('Error: Ya existe un evento con el nombre digitado');
                WHEN @err_no = 53006 THEN
                    SET errorMessage = CONCAT("Error: La fecha es inválida");
                WHEN @err_no = 53007 THEN
                    SET errorMessage = CONCAT('Error: El detalle digitado es muy largo');
                WHEN @err_no = 53008 THEN
                    SET errorMessage = CONCAT('Error: La duración se excede de los límites');
                WHEN @err_no = 53009 THEN
                    SET errorMessage = CONCAT('Error: La capacidad se excede de los límites');
            END CASE;
        END IF;
        
        ROLLBACK;
        
        RESIGNAL SET MESSAGE_TEXT = errorMessage;
	END;

    SELECT COUNT(id) INTO clientExists FROM cliente WHERE id = pClienteID;
    IF (clientExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_CLIENT;
    END IF;

    SELECT COUNT(id) INTO modeExists FROM modalidad WHERE id = pModalidadID;
    IF (modeExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_MODE;
    END IF;

    SELECT COUNT(id) INTO cantonExists FROM canton WHERE id = pCantonID;
    IF (cantonExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_CANTON;
    END IF;

    SELECT COUNT(id) INTO typeExists FROM tipo_evento WHERE id = pTypeID;
    IF (typeExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_TYPE;
    END IF;

    IF LENGTH(pName) > 40 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_NAME;
    END IF;

    SELECT COUNT(id) INTO nameExists FROM evento WHERE nombre = pName AND id != pEventoID;
    IF (nameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_NAME;
    END IF;

    IF (pDateTime <= NOW()) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_DATETIME;
    END IF;

    IF LENGTH(pDetails) > 255 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_DETAILS;
    END IF;

    IF (pDuration < 0 OR pDuration > 255) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_DURATION;
    END IF;

    IF (pCapacity < 0 OR pCapacity > 2147483647) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_CAPACITY;
    END IF;

    SELECT m.precio+te.precio INTO precioTotal FROM modalidad AS m
    JOIN tipo_evento AS te ON te.id = pTypeID
    WHERE m.id = pModalidadID;

	SET autocommit = 0;

	START TRANSACTION;
		UPDATE evento SET id_cliente = pClienteID, id_modalidad = pModalidadID, id_canton = pCantonID, id_tipo_evento = pTypeID,
        nombre = pName, fecha_hora = pDateTime, detalles = pDetails, duracion = pDuration, cupos = pCapacity, direccion = pLocation, precio_total = precioTotal
        WHERE id = pEventoID;

        DELETE FROM servicios_x_evento WHERE id_evento = pEventoID;
    COMMIT;
END$$
DELIMITER ;