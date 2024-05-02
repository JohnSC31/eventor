DROP PROCEDURE IF EXISTS sp_edit_rol;
DELIMITER $$
CREATE PROCEDURE sp_edit_rol(pRolID TINYINT, pRol VARCHAR(30), OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_ROL INT DEFAULT(53000);
	DECLARE DUPLICATE_ROL_NAME INT DEFAULT(53001);
    DECLARE rolExists TINYINT DEFAULT 0;
    DECLARE rolNameExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El rol no existe');
                WHEN @err_no = 53001 THEN
                    SET errorMessage = CONCAT('Error: Ya existe un rol con ese nombre');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO rolExists FROM rol WHERE id = pRolID;
    IF (rolExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_ROL;
    END IF;

    SELECT COUNT(id) INTO rolNameExists FROM rol WHERE rol = pRol AND id != pRolID;
    IF (rolNameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_ROL_NAME;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        UPDATE rol SET rol = pRol;
    COMMIT;
END$$
DELIMITER ;