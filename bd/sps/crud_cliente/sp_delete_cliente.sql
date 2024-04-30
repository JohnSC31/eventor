DROP PROCEDURE IF EXISTS sp_delete_cliente;
DELIMITER $$
CREATE PROCEDURE sp_delete_cliente(pClienteID INT, OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_CLIENT INT DEFAULT(53000);
    DECLARE clientExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El cliente no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO clientExists FROM cliente WHERE id = pClienteID;
    IF (clientExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_CLIENT;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
		DELETE FROM cliente WHERE id = pClienteID;
    COMMIT;
END$$
DELIMITER ;