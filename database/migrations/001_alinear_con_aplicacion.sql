-- =========================================================================
-- Migración NO destructiva: alinea pharmago_db con lo que la aplicación necesita.
-- Ejecutar sobre la base ya creada con el script original (sin DROP DATABASE).
-- Cada punto corresponde a una incongruencia del informe (ver ID entre corchetes).
-- =========================================================================
USE pharmago_db;

-- [INC-04] telefono INT(10) no admite 6015550101 (> 2.147.483.647) ni ceros a la izquierda.
ALTER TABLE Farmacia
    MODIFY telefono VARCHAR(15) NULL,
    ADD COLUMN localidad VARCHAR(50) NOT NULL DEFAULT '' AFTER ciudad;      -- [INC-03]

-- [INC-05] lote y vencimiento existen en el JS pero no en la tabla.
ALTER TABLE Inventario
    ADD COLUMN lote VARCHAR(30) NOT NULL DEFAULT 'SIN-LOTE' AFTER idMedicamento,
    ADD COLUMN fecha_vencimiento DATE NULL AFTER lote,
    ADD CONSTRAINT chk_inventario_cantidad CHECK (cantidad >= 0),
    ADD UNIQUE KEY uq_inventario_lote (idFarmacia, idMedicamento, lote);    -- [INC-06]

ALTER TABLE EPS ADD UNIQUE KEY uq_eps_nombre (nombre_eps);                  -- [INC-12]
ALTER TABLE Rol ADD UNIQUE KEY uq_rol_nombre (nombre_rol);                  -- [INC-12]

-- [INC-09] La vista no exponía principio_activo, lote, vencimiento ni localidad,
-- y no excluía lotes vencidos (el JS sí lo hacía).
CREATE OR REPLACE VIEW v_buscar_medicamento_sede AS
SELECT
    F.nombre_sede AS Farmacia, F.direccion AS Direccion, F.localidad AS Localidad,
    F.ciudad AS Ciudad, F.telefono AS Telefono,
    M.codigo_cum AS CodigoCUM, M.nombre_comercial AS Medicamento, M.principio_activo AS PrincipioActivo,
    M.concentracion AS Concentracion, M.presentacion AS Presentacion, M.laboratorio AS Laboratorio,
    M.requiere_receta AS RequiereReceta,
    I.lote AS Lote, I.fecha_vencimiento AS FechaVencimiento, I.cantidad AS StockDisponible,
    I.idInventario, I.idFarmacia, I.idMedicamento
FROM Inventario I
INNER JOIN Farmacia F ON I.idFarmacia = F.idFarmacia
INNER JOIN Medicamento M ON I.idMedicamento = M.idMedicamento
WHERE M.estado = 1 AND I.cantidad > 0
  AND (I.fecha_vencimiento IS NULL OR I.fecha_vencimiento >= CURDATE());

-- [INC-07] SELECT ... INTO falla (error 1172) cuando hay varios lotes para la misma sede+medicamento.
-- Ahora se dispensa sobre un idInventario concreto, con validaciones.
DROP PROCEDURE IF EXISTS sp_registrar_dispensacion;
DELIMITER //
CREATE PROCEDURE sp_registrar_dispensacion(
    IN p_idUsuario INT, IN p_idInventario INT, IN p_cantidad INT)
BEGIN
    DECLARE v_stock INT;
    DECLARE v_venc DATE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;

    IF p_cantidad IS NULL OR p_cantidad <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La cantidad debe ser mayor que cero.';
    END IF;

    START TRANSACTION;
    SELECT cantidad, fecha_vencimiento INTO v_stock, v_venc
    FROM Inventario WHERE idInventario = p_idInventario FOR UPDATE;

    IF v_stock IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No existe ese registro de inventario.';
    ELSEIF v_venc IS NOT NULL AND v_venc < CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El lote está vencido.';
    ELSEIF v_stock < p_cantidad THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente.';
    END IF;

    UPDATE Inventario SET cantidad = cantidad - p_cantidad WHERE idInventario = p_idInventario;
    INSERT INTO Dispensacion (idUsuario, idInventario, cantidad_entregada) VALUES (p_idUsuario, p_idInventario, p_cantidad);
    COMMIT;
END //
DELIMITER ;

-- [INC-16] La función lee tablas: no puede ser DETERMINISTIC.
DROP FUNCTION IF EXISTS fn_consultar_stock;
DELIMITER //
CREATE FUNCTION fn_consultar_stock(p_idMedicamento INT) RETURNS INT
NOT DETERMINISTIC READS SQL DATA
BEGIN
    RETURN (SELECT IFNULL(SUM(cantidad), 0) FROM Inventario
            WHERE idMedicamento = p_idMedicamento AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE()));
END //
DELIMITER ;

-- Usuario de aplicación con privilegios mínimos (cambiar la contraseña):
-- CREATE USER 'pharmago_app'@'localhost' IDENTIFIED BY '...';
-- GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON pharmago_db.* TO 'pharmago_app'@'localhost';
