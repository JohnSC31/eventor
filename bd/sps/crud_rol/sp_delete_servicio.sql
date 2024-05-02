DROP PROCEDURE IF EXISTS sp_delete_rol;
DELIMITER $$
CREATE PROCEDURE sp_delete_rol(pRolID TINYINT, OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_ROL INT DEFAULT(53000);
    DECLARE rolExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El rol no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO rolExists FROM rol WHERE id = pRolID;
    IF (rolExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_ROL;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        DELETE FROM rol WHERE id = pRolID;
    COMMIT;
END$$
DELIMITER ;