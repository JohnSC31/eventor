DROP PROCEDURE IF EXISTS sp_get_info_admin;
DELIMITER $$
CREATE PROCEDURE sp_get_info_admin(pAdminID TINYINT)
BEGIN
	SELECT a.id, a.nombre, a.correo, r.rol FROM administrador AS a
    JOIN rol AS r ON r.id = a.id_rol WHERE id = pAdminID;
END$$
DELIMITER ;