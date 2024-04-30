SET @pruebaMsg = '';

-- CRUD ADMIN
CALL sp_new_admin(1, "Valeria", "vsandi@estudiantec.cr", "Clave1234", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_login_admin("vsandi@estudiantec.cr", "Clave1234", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_edit_admin(1, 1, "Victoria", "vsandi@estudiantec.cr", "Clave1234", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_get_admins();
CALL sp_get_info_admin(1, @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_delete_admin(1, @error_msg);
SELECT @error_msg AS pruebaMsg;

-- CRUD CLIENTE
CALL sp_new_cliente(1, "Burger King", "Empresa de ventas de comida", "88445522", "burgerking@gmail.com", "Clave1234", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_login_cliente("burgerking@gmail.com", "Clave1234", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_edit_cliente(1, 1, "Taco Bell", "Empresa de ventas de comida", "88445522", "tacobell@gmail.com", "Clave1234", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_get_clientes();
CALL sp_get_info_cliente(1, @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_delete_cliente(1, @error_msg); -- ejecutar despues del crud de eventos
SELECT @error_msg AS pruebaMsg;

-- CRUD EVENTO
CALL sp_new_evento(1, 1, 1, 1, "Evento 1", "2024-06-24 10:00:00", "Detalles del evento", 6, 100, "Costa Rica", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_edit_evento(1, 1, 1, 1, 1, "Evento 1.2", "2024-06-20 10:00:00", "Detalles del evento", 3, 50, "Costa Rica", @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_get_eventos();
CALL sp_set_estado_evento(1, 2, @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_get_info_evento(1, @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_get_eventos_cliente(1, @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_get_eventos_estado(2, @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_delete_evento(1, @error_msg); -- ejecutar despues del crud de servicios x evento
SELECT @error_msg AS pruebaMsg;

-- CRUD SERVICIO
CALL sp_get_servicios();

-- CRUD SERVICIO X EVENTO
CALL sp_new_servicio_evento(1, 1, @error_msg);
SELECT @error_msg AS pruebaMsg;
CALL sp_get_servicios_evento(1, @error_msg);
SELECT @error_msg AS pruebaMsg;

-- CRUD PROVINCIA
CALL sp_get_provincias();

-- CRUD CANTON
CALL sp_get_cantones_provincia("Cartago", @error_msg);
SELECT @error_msg AS pruebaMsg;

-- CRUD MODALIDAD
CALL sp_get_modalidades();

-- CRUD ROL
CALL sp_get_roles();

-- CRUD TIPO EVENTO
CALL sp_get_tipos_evento();