DROP PROCEDURE IF EXISTS sp_new_tipo_evento;
DELIMITER $$
CREATE PROCEDURE sp_new_tipo_evento(pTipoEvento VARCHAR(25), pIcono VARCHAR(50), pPrecio DECIMAL(7,2), pDescripcion VARCHAR(255), OUT errorMessage VARCHAR(255))
BEGIN
	DECLARE DUPLICATE_TYPE_NAME INT DEFAULT(53000);
    DECLARE typeNameExists TINYINT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: Ya existe un tipo de evento con ese nombre');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO typeNameExists FROM tipo_evento WHERE tipo_evento = pTipoEvento;
    IF (typeNameExists > 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = DUPLICATE_TYPE_NAME;
    END IF;

	SET autocommit = 0;

	START TRANSACTION;
        INSERT INTO tipo_evento (tipo_evento, icono, precio, descripcion) VALUES (pTipoEvento, pIcono, pPrecio, pDescripcion);
    COMMIT;
END$$
DELIMITER ;