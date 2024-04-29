DROP PROCEDURE IF EXISTS sp_get_info_evento;
DELIMITER $$
CREATE PROCEDURE sp_get_info_evento(pEventoID INT)
BEGIN
	SELECT ev.id, c.nombre AS cliente, c.nombreEmpresa AS empresa, ev.nombre AS 'nombre del evento', m.modalidad, te.tipo_evento AS 'tipo de evento',
	ee.estado AS 'estado del evento', ev.fecha_hora AS 'fecha y hora', ev.detalles, ev.duracion, ev.cupos, 
	p.nombre AS provincia, cant.nombre AS canton, ev.direccion, ev.precio_total AS 'precio total'
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