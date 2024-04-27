DROP PROCEDURE IF EXISTS sp_get_servicios;
DELIMITER $$
CREATE PROCEDURE sp_get_servicios()
BEGIN
	SELECT servicio, precio, icono FROM servicio;
END$$
DELIMITER ;