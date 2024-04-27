DROP PROCEDURE IF EXISTS sp_get_info_admin;
DELIMITER $$
CREATE PROCEDURE sp_get_info_admin(pEmail VARCHAR(40))
BEGIN
	SELECT a.nombre, a.correo, r.rol FROM administradores AS a
    JOIN rol AS r ON r.id = a.id_rol WHERE correo = pEmail;
END$$
DELIMITER ;