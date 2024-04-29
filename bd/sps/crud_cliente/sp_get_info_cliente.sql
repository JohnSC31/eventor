DROP PROCEDURE IF EXISTS sp_get_info_cliente;
DELIMITER $$
CREATE PROCEDURE sp_get_info_cliente(pClienteID INT)
BEGIN
	SELECT c.id, c.nombre, c.telefono, c.correo, c.nombreEmpresa AS empresa, p.nombre AS provincia, cant.nombre AS canton, c.detalleEmpresa FROM cliente AS c
	JOIN canton AS cant ON cant.id = c.id_canton
	JOIN provincia AS p ON p.id = cant.id_provincia
    WHERE c.id = pClienteID;
END$$
DELIMITER ;