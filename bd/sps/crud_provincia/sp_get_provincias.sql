DROP PROCEDURE IF EXISTS sp_get_provincias;
DELIMITER $$
CREATE PROCEDURE sp_get_provincias()
BEGIN
	SELECT id, nombre FROM provincia ORDER BY id;
END$$
DELIMITER ;