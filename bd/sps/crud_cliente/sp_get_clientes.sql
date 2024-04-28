DROP PROCEDURE IF EXISTS sp_get_clientes;
DELIMITER $$
CREATE PROCEDURE sp_get_clientes()
BEGIN
	SELECT c.id, c.nombre, c.telefono, c.correo, e.nombre AS empresa, p.nombre AS provincia, cant.nombre AS canton, e.detalle FROM cliente AS c
	JOIN empresa AS e ON e.id = c.id_empresa
	JOIN canton AS cant ON cant.id = e.id_canton
	JOIN provincia AS p ON p.id = c.id_provincia;
END$$
DELIMITER ;