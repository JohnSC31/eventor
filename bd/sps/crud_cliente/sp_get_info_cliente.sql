DROP PROCEDURE IF EXISTS sp_get_info_cliente;
DELIMITER $$
CREATE PROCEDURE sp_get_info_cliente(pEmail VARCHAR(40))
BEGIN
	SELECT c.nombre, c.telefono, c.correo, e.empresa, e.canton, e.detalle FROM cliente AS c
    JOIN empresa AS e ON e.id = c.id_empresa 
    JOIN canton AS cant ON cant.id = e.id_canton
    WHERE correo = pEmail;
END$$
DELIMITER ;