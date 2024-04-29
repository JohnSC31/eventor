DROP PROCEDURE IF EXISTS sp_get_cantones_provincia;
DELIMITER $$
CREATE PROCEDURE sp_get_cantones_provincia(pProvinciaID TINYINT)
BEGIN
    SELECT id, nombre FROM canton WHERE id_provincia = pProvinciaID;
END$$
DELIMITER ;