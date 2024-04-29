DROP PROCEDURE IF EXISTS sp_delete_cliente;
DELIMITER $$
CREATE PROCEDURE sp_delete_cliente(pClienteID INT)
BEGIN
	SET autocommit = 0;

	START TRANSACTION;
		DELETE FROM cliente WHERE id = pClienteID;
    COMMIT;
END$$
DELIMITER ;