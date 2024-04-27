DROP PROCEDURE IF EXISTS sp_get_cantones_provincia;
DELIMITER $$
CREATE PROCEDURE sp_get_cantones_provincia(pProvincia VARCHAR(10))
BEGIN
	DECLARE provinciaID TINYINT;
	SELECT id INTO provinciaID FROM provincia WHERE nombre = pProvincia;
    SELECT nombre FROM canton WHERE id_provincia = provinciaID;
END$$
DELIMITER ;