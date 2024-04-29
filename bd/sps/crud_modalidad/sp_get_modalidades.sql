DROP PROCEDURE IF EXISTS sp_get_modalidades;
DELIMITER $$
CREATE PROCEDURE sp_get_modalidades()
BEGIN
	SELECT id, modalidad, precio FROM modalidad;
END$$
DELIMITER ;