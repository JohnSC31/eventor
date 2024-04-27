DROP PROCEDURE IF EXISTS sp_get_admins;
DELIMITER $$
CREATE PROCEDURE sp_get_admins()
BEGIN
	SELECT nombre FROM administradores;
END$$
DELIMITER ;