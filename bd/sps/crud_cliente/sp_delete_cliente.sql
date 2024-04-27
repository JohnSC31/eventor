DROP PROCEDURE IF EXISTS sp_delete_cliente;
DELIMITER $$
CREATE PROCEDURE sp_delete_cliente(pEmail VARCHAR(40))
BEGIN
	DECLARE INVALID_CLIENT INT DEFAULT(53000);
    DECLARE NON_EXISTANT_CLIENT INT DEFAULT(53001);
    DECLARE clienteID INT DEFAULT 0;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, @message = MESSAGE_TEXT;
        
        IF (ISNULL(@message)) THEN
			SET @message = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET @message = CONCAT('Error: El cliente no es válido');
                WHEN @err_no = 53001 THEN
                    SET @message = CONCAT('Error: El cliente no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
        
        RESIGNAL SET MESSAGE_TEXT = @message;
	END;

    SELECT id INTO clienteID FROM cliente WHERE correo = pEmail;

    IF (clienteID IS NULL) THEN
		SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = INVALID_CLIENT;
    END IF;

    IF (clienteID=0) THEN
		SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTANT_CLIENT;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
		DELETE FROM cliente WHERE id = clienteID;
    COMMIT;
END$$
DELIMITER ;