DROP PROCEDURE IF EXISTS sp_delete_evento;
DELIMITER $$
CREATE PROCEDURE sp_delete_evento(pEventoID INT)
BEGIN
	SET autocommit = 0;

	START TRANSACTION;
		DELETE FROM evento WHERE id = pEventoID;
    COMMIT;
END$$
DELIMITER ;