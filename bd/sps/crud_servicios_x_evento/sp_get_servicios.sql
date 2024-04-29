DROP PROCEDURE IF EXISTS sp_get_servicios_evento;
DELIMITER $$
CREATE PROCEDURE sp_get_servicios_evento(pEventoID INT)
BEGIN
	SELECT s.id, s.servicio, s.precio, s.icono FROM servicio AS s
	JOIN servicios_x_evento AS sxe ON sxe.id_servicio = s.id
	WHERE sxe.id_evento = pEventoID;
END$$
DELIMITER ;