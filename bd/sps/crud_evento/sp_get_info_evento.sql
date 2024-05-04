DROP PROCEDURE IF EXISTS sp_get_info_evento;
DELIMITER $$
CREATE PROCEDURE sp_get_info_evento(IN pEventoID INT, OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_EVENT INT DEFAULT(53000);
    DECLARE eventExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El evento no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO eventExists FROM evento WHERE id = pEventoID;
    IF (eventExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_EVENT;
    END IF;

	SELECT ev.id, c.nombreEmpresa AS empresa, ev.nombre AS 'nombre del evento', m.modalidad, te.tipo_evento AS 'tipo de evento', te.icono as 'icono',
	ee.estado AS 'estado del evento', ev.fecha_hora AS 'fecha y hora', ev.detalles, ev.duracion, ev.cupos, 
	p.id as idProvincia, p.nombre AS provincia, cant.id as idCanton, cant.nombre AS canton, ev.direccion, ev.precio_total AS 'precio total'
	FROM evento AS ev
	JOIN cliente AS c ON c.id = ev.id_cliente
	JOIN modalidad AS m ON m.id = ev.id_modalidad
	JOIN canton AS cant ON cant.id = ev.id_canton
	JOIN provincia AS p ON p.id = cant.id_provincia
	JOIN tipo_evento AS te ON te.id = ev.id_tipo_evento
	JOIN estado_evento AS ee ON ee.id = ev.id_estado
    WHERE ev.id = pEventoID;
END$$
DELIMITER ;