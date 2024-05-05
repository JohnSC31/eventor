DROP PROCEDURE IF EXISTS sp_login_cliente;
DELIMITER $$
CREATE PROCEDURE sp_login_cliente(pEmail VARCHAR(40), pPassword VARCHAR(30), OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_EMAIL INT DEFAULT(53000);
    DECLARE WRONG_PASSWORD INT DEFAULT(53001);
    DECLARE tmpPassword VARCHAR(30);
    DECLARE idClient TINYINT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El correo digitado no está vinculado a una cuenta');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: La constraseña digitada es incorrecta');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;
 
    SELECT id INTO idClient FROM cliente WHERE correo = pEmail;
    IF (idClient = 0 OR idClient = NULL) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_EMAIL;
    END IF;

    SELECT clave INTO tmpPassword FROM cliente WHERE id = idClient;
    IF (tmpPassword != LEFT(SHA2(pPassword, 256), 30)) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = WRONG_PASSWORD;
    ELSE
        CALL sp_get_info_cliente(idClient, errorMessage);
    END IF;
END$$
DELIMITER ;