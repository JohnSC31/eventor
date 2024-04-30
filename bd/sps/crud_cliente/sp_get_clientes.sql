DROP PROCEDURE IF EXISTS sp_get_clientes;
DELIMITER $$
CREATE PROCEDURE sp_get_clientes()
BEGIN
	SELECT c.id, c.nombreEmpresa AS empresa, c.detalleEmpresa AS detalle, p.nombre AS provincia, cant.nombre AS canton, c.telefono, c.correo FROM cliente AS c
	JOIN canton AS cant ON cant.id = c.id_canton
	JOIN provincia AS p ON p.id = cant.id_provincia;
END$$
DELIMITER ;