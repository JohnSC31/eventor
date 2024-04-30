DROP PROCEDURE IF EXISTS sp_edit_admin;
DELIMITER $$
CREATE PROCEDURE sp_edit_admin(pAdminID TINYINT, pRolID TINYINT, pName VARCHAR(30), pEmail VARCHAR(40), pPassword VARCHAR(30), OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_ADMIN INT DEFAULT(53000);
    DECLARE NON_EXISTENT_ROL INT DEFAULT(53001);
    DECLARE LARGE_NAME INT DEFAULT(53002);
    DECLARE INVALID_EMAIL INT DEFAULT(53003);
    DECLARE LARGE_EMAIL INT DEFAULT(53004);
    DECLARE DUPLICATE_EMAIL INT DEFAULT(53005);
    DECLARE SHORT_PASSWORD INT DEFAULT(53006);
    DECLARE LARGE_PASSWORD INT DEFAULT(53007);
    DECLARE INVALID_PASSWORD INT DEFAULT(53008);
    DECLARE rolExists TINYINT DEFAULT 0;
    DECLARE adminExists TINYINT DEFAULT 0;
    DECLARE emailExistsAdmin TINYINT DEFAULT 0;
    DECLARE emailExistsClient TINYINT DEFAULT 0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El admin no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: El rol no existe');
                WHEN @err_no = 53002 THEN
                    SET errorMessage = CONCAT('Error: El nombre es muy largo');
                WHEN @err_no = 53003 THEN
                    SET errorMessage = CONCAT('Error: El correo no es válido');
                WHEN @err_no = 53004 THEN
                    SET errorMessage = CONCAT('Error: El correo es muy largo');
                WHEN @err_no = 53005 THEN
                    SET errorMessage = CONCAT('Error: El correo ya existe');
                WHEN @err_no = 53006 THEN
                    SET errorMessage = CONCAT('Error: La contraseña es muy corta');
                WHEN @err_no = 53007 THEN
                    SET errorMessage = CONCAT('Error: La contraseña es muy larga');
                WHEN @err_no = 53008 THEN
                    SET errorMessage = CONCAT('Error: La contraseña tiene que tener al menos una miníscula, una mayúscula y un número');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO adminExists FROM administrador WHERE id = pAdminID;
    IF (adminExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_ADMIN;
    END IF;

    SELECT COUNT(id) INTO rolExists FROM rol WHERE id = pRolID;
    IF (rolExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_ROL;
    END IF;

    IF LENGTH(pName) > 30 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_NAME;
    END IF;

    IF pEmail NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_EMAIL;
    END IF;

    IF LENGTH(pEmail) > 40 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_EMAIL;
    END IF;

    SELECT COUNT(id) INTO emailExistsAdmin FROM administrador WHERE correo = pEmail AND id != pAdminID;
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
		UPDATE administrador SET id_rol = pRolID, nombre = pName, correo = pEmail, clave = SHA2(pPassword, 256) WHERE id = pAdminID;
    COMMIT;
END$$
DELIMITER ;