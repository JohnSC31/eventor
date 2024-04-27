DROP PROCEDURE IF EXISTS sp_new_cliente;
DELIMITER $$
CREATE PROCEDURE sp_new_cliente(pCanton VARCHAR(20), pBusinessName VARCHAR(30), pDetail VARCHAR(255), pName VARCHAR(30), 
                                pPhone VARCHAR(8), pEmail VARCHAR(40), pPassword VARCHAR(30))
BEGIN
	DECLARE LARGE_DETAIL INT DEFAULT(53000);
    DECLARE LARGE_BUSINESS_NAME INT DEFAULT(53001);
    DECLARE LARGE_NAME INT DEFAULT(53002);
    DECLARE INVALID_PHONE INT DEFAULT(53003);
    DECLARE INVALID_EMAIL INT DEFAULT(53004);
    DECLARE LARGE_EMAIL INT DEFAULT(53005);
    DECLARE SHORT_PASSWORD INT DEFAULT(53006);
    DECLARE LARGE_PASSWORD INT DEFAULT(53007);
    DECLARE INVALID_PASSWORD INT DEFAULT(53008);
    DECLARE cantonID TINYINT DEFAULT 0;
    DECLARE empresaID INT DEFAULT 0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, @message = MESSAGE_TEXT;
        
        IF (ISNULL(@message)) THEN
			SET @message = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET @message = CONCAT('Error: El detalle es muy largo');
                WHEN @err_no = 53001 THEN
                    SET @message = CONCAT("Error: El nombre de la empresa es muy largo");
                WHEN @err_no = 53002 THEN
                    SET @message = CONCAT('Error: El nombre es muy largo');
                WHEN @err_no = 53003 THEN
                    SET @message = CONCAT('Error: El número de teléfono no es válido');
                WHEN @err_no = 53004 THEN
                    SET @message = CONCAT('Error: El correo no es válido');
                WHEN @err_no = 53005 THEN
                    SET @message = CONCAT('Error: El correo es muy largo');
                WHEN @err_no = 53006 THEN
                    SET @message = CONCAT('Error: La contraseña es muy corta');
                WHEN @err_no = 53007 THEN
                    SET @message = CONCAT('Error: La contraseña es muy larga');
                WHEN @err_no = 53008 THEN
                    SET @message = CONCAT('Error: La contraseña tiene que tener al menos una miníscula, una mayúscula y un número');
            END CASE;
        END IF;
        
        ROLLBACK;
        
        RESIGNAL SET MESSAGE_TEXT = @message;
	END;

    SELECT id INTO cantonID FROM canton WHERE nombre = pCanton;

    IF LENGTH(pDetail) > 255 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_DETAIL;
    END IF;

    IF LENGTH(pBusinessName) > 30 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_BUSINESS_NAME;
    END IF;

    IF LENGTH(pName) > 30 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_NAME;
    END IF;

    IF NOT (pPhone REGEXP '^[1-9][0-9]{7}$') THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_PHONE;
    END IF;

    IF pEmail NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_EMAIL;
    END IF;

    IF LENGTH(pEmail) > 40 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_EMAIL;
    END IF;

    IF LENGTH(pPassword) < 8 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = SHORT_PASSWORD;
    END IF;

    IF LENGTH(pPassword) > 30 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_PASSWORD;
    END IF;
    
    IF NOT (pPassword REGEXP '[a-z]' AND pPassword REGEXP '[A-Z]' AND pPassword REGEXP '[0-9]') THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_PASSWORD;
    END IF;

    SELECT id INTO empresaID FROM empresa WHERE nombre = pBusinessName;

	SET autocommit = 0;

	START TRANSACTION;
        IF (empresaID IS NULL) THEN 
            INSERT INTO empresa (id_canton, nombre, detalle) VALUES (cantonID, pBusinessName, pDetail);
            SELECT LAST_INSERT_ID() INTO empresaID;
        END IF;

        INSERT INTO cliente (id_empresa, nombre, telefono, correo, clave) VALUES (empresaID, pName, pPhone, pEmail, SHA2(pPassword, 256));
    COMMIT;
END$$
DELIMITER ;