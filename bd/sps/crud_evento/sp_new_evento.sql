DROP PROCEDURE IF EXISTS sp_new_evento;
DELIMITER $$
CREATE PROCEDURE sp_new_evento(pClienteID INT, pModalidadID TINYINT, pCantonID TINYINT, pTypeID TINYINT, 
                                pName VARCHAR(40), pDateTime DATETIME, pDetails VARCHAR(255), pDuration TINYINT, 
                                pCapacity INT, pLocation VARCHAR(255))
BEGIN
    DECLARE LARGE_NAME INT DEFAULT(53000);
    DECLARE INVALID_DATETIME INT DEFAULT(53001);
	DECLARE LARGE_DETAILS INT DEFAULT(53002);
    DECLARE INVALID_DURATION INT DEFAULT(53003);
    DECLARE INVALID_CAPACITY INT DEFAULT(53004);
    DECLARE precioTotal DECIMAL(10,2) DEFAULT 0.0;
    DECLARE eventoID INT DEFAULT 0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, @message = MESSAGE_TEXT;
        
        IF (ISNULL(@message)) THEN
			SET @message = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET @message = CONCAT('Error: El nombre es muy largo');
                WHEN @err_no = 53001 THEN
                    SET @message = CONCAT("Error: La fecha es inválida");
                WHEN @err_no = 53002 THEN
                    SET @message = CONCAT('Error: El detalle es muy largo');
                WHEN @err_no = 53003 THEN
                    SET @message = CONCAT('Error: La duración se excede de los límites');
                WHEN @err_no = 53004 THEN
                    SET @message = CONCAT('Error: La capacidad se excede de los límites');
            END CASE;
        END IF;
        
        ROLLBACK;
        
        RESIGNAL SET MESSAGE_TEXT = @message;
	END;

    IF LENGTH(pName) > 40 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_NAME;
    END IF;

    IF (pDateTime <= NOW()) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_DATETIME;
    END IF;

    IF LENGTH(pDetail) > 255 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_DETAIL;
    END IF;

    IF (pDuration < 0 OR pDuration > 255) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_DURATION;
    END IF;

    IF (pCapacity < 0 OR pCapacity > 2147483647) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_CAPACITY;
    END IF;


    SELECT m.precio+te.precio INTO precioTotal FROM modalidad AS m
    JOIN tipo_evento AS te ON te.id = pTypeID
    WHERE m.id = pModalidadID

	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO evento (id_cliente, id_modalidad, id_canton, id_tipo_evento, id_estado, nombre, fecha_hora, detalles, duracion, cupos, direccion, precio_total) 
        VALUES (pClienteID, pModalidadID, pCantonID, pTypeID, 1, pName, pDateTime, pDetails, pDuration, pCapacity, pLocation, precioTotal);
        SELECT LAST_INSERT_ID() INTO eventoID;

        INSERT INTO servicio (id_evento, id_servicio) VALUES (eventoID, )
    COMMIT;
END$$
DELIMITER ;