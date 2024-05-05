DROP PROCEDURE IF EXISTS sp_get_cantones_provincia;
DELIMITER $$
CREATE PROCEDURE sp_get_cantones_provincia(pProvinciaID TINYINT, OUT errorMessage VARCHAR(255))
BEGIN
    DECLARE NON_EXISTENT_PROVINCIA INT DEFAULT(53000);
    DECLARE provinciaExists TINYINT DEFAULT 0;
    
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, errorMessage = MESSAGE_TEXT;

        IF (ISNULL(errorMessage)) THEN
			SET errorMessage = 'Error'; 
        ELSE
            CASE
                WHEN @err_no = 53000 THEN
                    SET errorMessage = CONCAT('Error: No se pueden desplegar los cantones ya que la provincia elegida no existe');
            END CASE;
        END IF;
        
        ROLLBACK;
	END;

    SELECT COUNT(id) INTO provinciaExists FROM provincia WHERE id = pProvinciaID;
    IF (provinciaExists = 0) THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = NON_EXISTENT_PROVINCIA;
    END IF;

    SELECT id, nombre FROM canton WHERE id_provincia = pProvinciaID;
END$$
DELIMITER ;