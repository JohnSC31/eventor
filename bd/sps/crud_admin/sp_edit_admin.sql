DROP PROCEDURE IF EXISTS sp_edit_admin;
DELIMITER $$
CREATE PROCEDURE sp_edit_admin(pAdminID TINYINT, pRolID TINYINT, pName VARCHAR(30), pEmail VARCHAR(40), pPassword VARCHAR(30))
BEGIN
    DECLARE LARGE_NAME INT DEFAULT(53000);
    DECLARE INVALID_EMAIL INT DEFAULT(53001);
    DECLARE LARGE_EMAIL INT DEFAULT(53002);
    DECLARE SHORT_PASSWORD INT DEFAULT(53003);
    DECLARE LARGE_PASSWORD INT DEFAULT(53004);
    DECLARE INVALID_PASSWORD INT DEFAULT(53005);

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
                    SET @message = CONCAT('Error: El correo no es válido');
                WHEN @err_no = 53002 THEN
                    SET @message = CONCAT('Error: El correo es muy largo');
                WHEN @err_no = 53003 THEN
                    SET @message = CONCAT('Error: La contraseña es muy corta');
                WHEN @err_no = 53004 THEN
                    SET @message = CONCAT('Error: La contraseña es muy larga');
                WHEN @err_no = 53005 THEN
                    SET @message = CONCAT('Error: La contraseña tiene que tener al menos una miníscula, una mayúscula y un número');
            END CASE;
        END IF;
        
        ROLLBACK;
        
        RESIGNAL SET MESSAGE_TEXT = @message;
	END;

    IF LENGTH(pName) > 30 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = LARGE_NAME;
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

	SET autocommit = 0;

	START TRANSACTION;
		UPDATE administrador SET id_rol = pRolID, nombre = pName, correo = pEmail, clave = SHA2(pPassword, 256) WHERE id = pAdminID;
    COMMIT;
END$$
DELIMITER ;