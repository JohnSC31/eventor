DROP PROCEDURE IF EXISTS sp_get_tipos_evento;
DELIMITER $$
CREATE PROCEDURE sp_get_tipos_evento()
BEGIN
	SELECT id, tipo_evento, precio, icono, descripcion FROM tipo_evento;
END$$
DELIMITER ;