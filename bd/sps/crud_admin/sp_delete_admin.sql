DROP PROCEDURE IF EXISTS sp_delete_admin;
DELIMITER $$
CREATE PROCEDURE sp_delete_admin(pEmail VARCHAR(40))
BEGIN
	  DECLARE INVALID_ADMIN INT DEFAULT(53000);
    DECLARE NON_EXISTANT_ADMIN INT DEFAULT(53001);
    DECLARE adminID TINYINT DEFAULT 0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, @message = MESSAGE_TEXT;
        
        IF (ISNULL(@message)) THEN
			SET @message = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET @message = CONCAT('Error: El administrador no es válido');
                WHEN @err_no = 53001 THEN
                    SET @message = CONCAT('Error: El administrador no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
        
        RESIGNAL SET MESSAGE_TEXT = @message;
	END;

    SELECT id INTO adminID FROM administrador WHERE correo = pEmail;

    IF (adminID IS NULL) THEN
		SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_ADMIN;
    END IF;

    IF (adminID=0) THEN
		SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTANT_ADMIN;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
		DELETE FROM administrador WHERE id = adminID;
    COMMIT;
END$$
DELIMITER ;