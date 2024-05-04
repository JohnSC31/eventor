DROP PROCEDURE IF EXISTS sp_get_info_cliente;
DELIMITER $$
CREATE PROCEDURE sp_get_info_cliente(IN pClienteID INT, OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE NON_EXISTENT_CLIENT INT DEFAULT(53000);
    DECLARE clientExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: El cliente no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO clientExists FROM cliente WHERE id = pClienteID;
    IF (clientExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_CLIENT;
    END IF;

	SELECT c.id, c.nombreEmpresa AS empresa, c.detalleEmpresa AS detalle, p.id as idProvincia, p.nombre AS provincia, cant.id as idCanton, cant.nombre AS canton, c.telefono, c.correo FROM cliente AS c
	JOIN canton AS cant ON cant.id = c.id_canton
	JOIN provincia AS p ON p.id = cant.id_provincia
    WHERE c.id = pClienteID;
END$$
DELIMITER ;