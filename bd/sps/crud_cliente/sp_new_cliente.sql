DROP PROCEDURE IF EXISTS sp_new_cliente;
DELIMITER $$
CREATE PROCEDURE sp_new_cliente(pCantonID TINYINT, pBusinessName VARCHAR(30), pDetail VARCHAR(255), 
                                pPhone VARCHAR(8), pEmail VARCHAR(40), pPassword VARCHAR(30), OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_CANTON INT DEFAULT(53000);
    DECLARE LARGE_BUSINESS_NAME INT DEFAULT(53001);
    DECLARE DUPLICATE_NAME INT DEFAULT(53002);
    DECLARE LARGE_DETAIL INT DEFAULT(53003);
    DECLARE INVALID_PHONE INT DEFAULT(53004);
    DECLARE INVALID_EMAIL INT DEFAULT(53005);
    DECLARE LARGE_EMAIL INT DEFAULT(53006);
    DECLARE DUPLICATE_EMAIL INT DEFAULT(53007);
    DECLARE SHORT_PASSWORD INT DEFAULT(53008);
    DECLARE LARGE_PASSWORD INT DEFAULT(53009);
    DECLARE INVALID_PASSWORD INT DEFAULT(53010);
    DECLARE cantonExists TINYINT DEFAULT 0;
    DECLARE emailExistsAdmin TINYINT DEFAULT 0;
    DECLARE emailExistsClient TINYINT DEFAULT 0;
    DECLARE nameExists TINYINT DEFAULT 0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;
        
        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El cantón que desea agregar no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT("Error: El nombre de la empresa digitado es muy largo");
                WHEN @err_no = 53002 THEN
                    SET errorMessage = CONCAT('Error: La empresa ya existe');
                WHEN @err_no = 53003 THEN
                    SET errorMessage = CONCAT('Error: El detalle digitado es muy largo');
                WHEN @err_no = 53004 THEN
                    SET errorMessage = CONCAT('Error: El número de teléfono digitado no es válido');
                WHEN @err_no = 53005 THEN
                    SET errorMessage = CONCAT('Error: El correo digitado no es válido ya que no sigue la estructura de un correo');
                WHEN @err_no = 53006 THEN
                    SET errorMessage = CONCAT('Error: El correo digitado es muy largo');
                WHEN @err_no = 53007 THEN
                    SET errorMessage = CONCAT('Error: El correo digitado ya está en uso');
                WHEN @err_no = 53008 THEN
                    SET errorMessage = CONCAT('Error: La contraseña digitada es muy corta');
                WHEN @err_no = 53009 THEN
                    SET errorMessage = CONCAT('Error: La contraseña digitada es muy larga');
                WHEN @err_no = 53010 THEN
                    SET errorMessage = CONCAT('Error: La contraseña digitada tiene que tener al menos una miníscula, una mayúscula y un número');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO cantonExists FROM canton WHERE id = pCantonID;
    IF (cantonExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_CANTON;
    END IF;

    IF LENGTH(pBusinessName) > 30 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_BUSINESS_NAME;
    END IF;

    SELECT COUNT(id) INTO nameExists FROM cliente WHERE nombreEmpresa = pBusinessName;
    IF (nameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_NAME;
    END IF;

    IF LENGTH(pDetail) > 255 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_DETAIL;
    END IF;

    IF NOT (CAST(pPhone AS INT) REGEXP '^[1-9][0-9]{7}$') THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_PHONE;
    END IF;

    IF pEmail NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_EMAIL;
    END IF;

    IF LENGTH(pEmail) > 40 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_EMAIL;
    END IF;

    SELECT COUNT(id) INTO emailExistsAdmin FROM administrador WHERE correo = pEmail;
    SELECT COUNT(id) INTO emailExistsClient FROM cliente WHERE correo = pEmail;
    IF (emailExistsAdmin > 0 OR emailExistsClient > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_EMAIL;
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

	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO cliente (id_canton, nombreEmpresa, detalleEmpresa, telefono, correo, clave) 
        VALUES (pCantonID, pBusinessName, pDetail, pPhone, pEmail, SHA2(pPassword, 256));
    COMMIT;
END$$
DELIMITER ;