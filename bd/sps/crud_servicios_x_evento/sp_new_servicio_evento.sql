DROP PROCEDURE IF EXISTS sp_new_servicio_evento;
DELIMITER $$
CREATE PROCEDURE sp_new_servicio_evento(pEventoID INT, pServicioID TINYINT)
BEGIN
	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO servicios_x_evento (id_evento, id_servicio) VALUES (pEventoID, pServicioID);
    COMMIT;
END$$
DELIMITER ;