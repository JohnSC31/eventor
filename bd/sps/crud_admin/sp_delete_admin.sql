DROP PROCEDURE IF EXISTS sp_delete_admin;
DELIMITER $$
CREATE PROCEDURE sp_delete_admin(pAdminID TINYINT)
BEGIN
	SET autocommit = 0;

	START TRANSACTION;
		DELETE FROM administrador WHERE id = pAdminID;
    COMMIT;
END$$
DELIMITER ;