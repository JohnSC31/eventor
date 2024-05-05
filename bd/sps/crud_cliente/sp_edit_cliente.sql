DROP PROCEDURE IF EXISTS sp_edit_cliente;
DELIMITER $$
CREATE PROCEDURE sp_edit_cliente(pClienteID INT, pCantonID TINYINT, pBusinessName VARCHAR(30), pDetail VARCHAR(255), 
                                 pPhone VARCHAR(8), pEmail VARCHAR(40), OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_CLIENT INT DEFAULT(53000);
    DECLARE NON_EXISTENT_CANTON INT DEFAULT(53001);
    DECLARE LARGE_BUSINESS_NAME INT DEFAULT(53002);
    DECLARE DUPLICATE_NAME INT DEFAULT(53003);
    DECLARE LARGE_DETAIL INT DEFAULT(53004);
    DECLARE INVALID_PHONE INT DEFAULT(53005);
    DECLARE INVALID_EMAIL INT DEFAULT(53006);
    DECLARE LARGE_EMAIL INT DEFAULT(53007);
    DECLARE DUPLICATE_EMAIL INT DEFAULT(53008);
    DECLARE clientExists TINYINT DEFAULT 0;
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
                    SET errorMessage = CONCAT('Error: El cliente no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: El cantón no existe');
                WHEN @err_no = 53002 THEN
                    SET errorMessage = CONCAT("Error: El nombre de la empresa es muy largo");
                WHEN @err_no = 53003 THEN
                    SET errorMessage = CONCAT('Error: La empresa ya existe');
                WHEN @err_no = 53004 THEN
                    SET errorMessage = CONCAT('Error: El detalle es muy largo');
                WHEN @err_no = 53005 THEN
                    SET errorMessage = CONCAT('Error: El número de teléfono no es válido');
                WHEN @err_no = 53006 THEN
                    SET errorMessage = CONCAT('Error: El correo no es válido');
                WHEN @err_no = 53007 THEN
                    SET errorMessage = CONCAT('Error: El correo es muy largo');
                WHEN @err_no = 53008 THEN
                    SET errorMessage = CONCAT('Error: El correo ya existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO clientExists FROM cliente WHERE id = pClienteID;
    IF (clientExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_CLIENT;
    END IF;

    SELECT COUNT(id) INTO cantonExists FROM canton WHERE id = pCantonID;
    IF (cantonExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_CANTON;
    END IF;

    IF LENGTH(pBusinessName) > 30 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_BUSINESS_NAME;
    END IF;

    SELECT COUNT(id) INTO nameExists FROM cliente WHERE nombreEmpresa = pBusinessName AND id != pClienteID;
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
    SELECT COUNT(id) INTO emailExistsClient FROM cliente WHERE correo = pEmail  AND id != pClienteID;
    IF (emailExistsAdmin > 0 OR emailExistsClient > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_EMAIL;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
		UPDATE cliente SET id_canton = pCantonID, nombreEmpresa = pBusinessName, detalleEmpresa = pDetail, 
        telefono = pPhone, correo = pEmail WHERE id = pClienteID;
    COMMIT;
END$$
DELIMITER ;