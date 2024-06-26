DROP PROCEDURE IF EXISTS sp_new_evento;
DELIMITER $$
CREATE PROCEDURE sp_new_evento(pClienteID INT, pModalidadID TINYINT, pCantonID TINYINT, pTypeID TINYINT, 
                                pName VARCHAR(40), pDateTime DATETIME, pDetails VARCHAR(255), pDuration TINYINT, 
                                pCapacity INT, pLocation VARCHAR(255), OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_CLIENT INT DEFAULT(53000);
    DECLARE NON_EXISTENT_MODE INT DEFAULT(53001);
    DECLARE NON_EXISTENT_CANTON INT DEFAULT(53002);
    DECLARE NON_EXISTENT_TYPE INT DEFAULT(53003);
    DECLARE LARGE_NAME INT DEFAULT(53004);
    DECLARE INVALID_DATETIME INT DEFAULT(53005);
	DECLARE LARGE_DETAILS INT DEFAULT(53006);
    DECLARE INVALID_DURATION INT DEFAULT(53007);
    DECLARE INVALID_CAPACITY INT DEFAULT(53008);
    DECLARE clientExists TINYINT DEFAULT 0;
    DECLARE modeExists TINYINT DEFAULT 0;
    DECLARE cantonExists TINYINT DEFAULT 0;
    DECLARE typeExists TINYINT DEFAULT 0;
    DECLARE precioTotal DECIMAL(10,2) DEFAULT 0.0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El cliente que desea agregar no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: La modalidad que desea agregar no existe');
                WHEN @err_no = 53002 THEN
                    SET errorMessage = CONCAT('Error: El cantón que desea agregar no existe');
                WHEN @err_no = 53003 THEN
                    SET errorMessage = CONCAT('Error: El tipo de evento que desea agregar no existe');
                WHEN @err_no = 53004 THEN
                    SET errorMessage = CONCAT('Error: El nombre digitado es muy largo');
                WHEN @err_no = 53005 THEN
                    SET errorMessage = CONCAT("Error: La fecha es inválida");
                WHEN @err_no = 53006 THEN
                    SET errorMessage = CONCAT('Error: El detalle digitado es muy largo');
                WHEN @err_no = 53007 THEN
                    SET errorMessage = CONCAT('Error: La duración se excede de los límites');
                WHEN @err_no = 53008 THEN
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
        INSERT INTO evento (id_cliente, id_modalidad, id_canton, id_tipo_evento, id_estado, nombre, fecha_hora, detalles, duracion, cupos, direccion, precio_total) 
        VALUES (pClienteID, pModalidadID, pCantonID, pTypeID, 1, pName, pDateTime, pDetails, pDuration, pCapacity, pLocation, precioTotal);
        SELECT LAST_INSERT_ID() AS id_evento;
    COMMIT;
END$$
DELIMITER ;