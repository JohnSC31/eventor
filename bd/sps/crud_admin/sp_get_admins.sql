DROP PROCEDURE IF EXISTS sp_get_admins;
DELIMITER $$
CREATE PROCEDURE sp_get_admins()
BEGIN
	SELECT a.id, a.nombre, a.correo, r.rol FROM administrador AS a
	JOIN rol AS r ON r.id = a.id_rol;
END$$
DELIMITER ;