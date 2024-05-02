DROP PROCEDURE IF EXISTS sp_new_rol;
DELIMITER $$
CREATE PROCEDURE sp_new_rol(pRol VARCHAR(30), OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE DUPLICATE_ROL_NAME INT DEFAULT(53000);
    DECLARE rolNameExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: Ya existe un rol con ese nombre');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO rolNameExists FROM rol WHERE rol = pRol;
    IF (rolNameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_ROL_NAME;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO rol (rol) VALUES (pRol);
    COMMIT;
END$$
DELIMITER ;