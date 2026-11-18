<?php

declare(strict_types=1);

use PharmaGo\Repository\{EpsRepository, FarmaciaRepository, InventarioRepository, MedicamentoRepository, RolRepository, UsuarioRepository};

/*
 * Reemplaza a las parejas *View.js / *Controller.js de cada módulo.
 * Cada campo reproduce la definición de Vista, ajustada al esquema SQL real
 * (p. ej. codigo_cum, nombre/apellido, tipo_documento, idEPS).
 */
$soloLetras = '^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ ]+$';
$estado = [[1, 'Activo'], [0, 'Inactivo']];

return [
    'productos' => [
        'nombre' => 'Medicamentos', 'titulo' => 'Gestión de Medicamentos', 'repo' => MedicamentoRepository::class,
        'pacienteVe' => true,
        'unicos' => [[['codigo_cum'], 'codigo_cum', 'Ya existe un medicamento con ese código CUM.']],
        'campos' => [
            ['nombre' => 'nombre_comercial', 'etiqueta' => 'Nombre comercial', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 100, 'ejemplo' => 'Ej. Dolex'],
            ['nombre' => 'principio_activo', 'etiqueta' => 'Principio activo', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 100, 'ejemplo' => 'Ej. Acetaminofén'],
            ['nombre' => 'presentacion', 'etiqueta' => 'Presentación', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 50, 'ejemplo' => 'Ej. Tabletas x 10'],
            ['nombre' => 'concentracion', 'etiqueta' => 'Concentración', 'tipo' => 'text', 'requerido' => true, 'minimo' => 2, 'max' => 50, 'ejemplo' => 'Ej. 500 mg'],
            // En el JS era opcional, pero la columna es NOT NULL.
            ['nombre' => 'laboratorio', 'etiqueta' => 'Laboratorio', 'tipo' => 'text', 'requerido' => true, 'minimo' => 2, 'max' => 50, 'ejemplo' => 'Ej. GSK'],
            // Faltaban en el JS; la columna es NOT NULL UNIQUE.
            ['nombre' => 'codigo_cum', 'etiqueta' => 'Código CUM', 'tipo' => 'text', 'requerido' => true, 'max' => 30, 'patron' => '^[A-Za-z0-9\-]{3,30}$', 'mensajePatron' => 'Use de 3 a 30 letras, números o guiones.'],
            ['nombre' => 'registro_invima', 'etiqueta' => 'Registro INVIMA', 'tipo' => 'text', 'max' => 50],
            ['nombre' => 'requiere_receta', 'etiqueta' => 'Requiere receta', 'tipo' => 'checkbox'],
            ['nombre' => 'estado', 'etiqueta' => 'Estado', 'tipo' => 'select', 'requerido' => true, 'numero' => true, 'opciones' => $estado],
        ],
    ],
    'inventario' => [
        'nombre' => 'Inventario', 'titulo' => 'Control de Inventario', 'repo' => InventarioRepository::class,
        'unicos' => [[['idFarmacia', 'idMedicamento', 'lote'], 'lote', 'Ya existe este lote para esa farmacia y medicamento.']],
        'campos' => [
            ['nombre' => 'idFarmacia', 'etiqueta' => 'Farmacia sede', 'tipo' => 'select', 'requerido' => true, 'numero' => true,
                'origen' => ['repo' => FarmaciaRepository::class, 'valor' => 'idFarmacia', 'texto' => 'nombre_sede']],
            ['nombre' => 'idMedicamento', 'etiqueta' => 'Medicamento', 'tipo' => 'select', 'requerido' => true, 'numero' => true,
                'origen' => ['repo' => MedicamentoRepository::class, 'valor' => 'idMedicamento', 'texto' => 'nombre_comercial', 'extra' => 'concentracion']],
            ['nombre' => 'lote', 'etiqueta' => 'Lote', 'tipo' => 'text', 'requerido' => true, 'max' => 30, 'ejemplo' => 'Ej. LOT-2026-A',
                'patron' => '^[A-Za-z0-9\-]{3,30}$', 'mensajePatron' => 'Use de 3 a 30 letras, números o guiones.'],
            ['nombre' => 'fecha_vencimiento', 'etiqueta' => 'Fecha de vencimiento', 'tipo' => 'date', 'requerido' => true],
            ['nombre' => 'cantidad', 'etiqueta' => 'Cantidad', 'tipo' => 'number', 'requerido' => true, 'numero' => true, 'min' => 0, 'valor' => 0],
            ['nombre' => 'fecha_actualizacion', 'etiqueta' => 'Última actualización', 'tipo' => 'text', 'soloTabla' => true],
        ],
    ],
    'farmacias' => [
        'nombre' => 'Farmacias', 'titulo' => 'Gestión de Farmacias', 'repo' => FarmaciaRepository::class, 'pacienteVe' => true,
        'campos' => [
            ['nombre' => 'nombre_sede', 'etiqueta' => 'Nombre de la sede', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 100, 'ejemplo' => 'Ej. Sede Chapinero'],
            ['nombre' => 'direccion', 'etiqueta' => 'Dirección', 'tipo' => 'text', 'requerido' => true, 'minimo' => 5, 'max' => 120, 'ejemplo' => 'Ej. Calle 63 # 10-25'],
            ['nombre' => 'telefono', 'etiqueta' => 'Teléfono', 'tipo' => 'tel', 'max' => 15, 'ejemplo' => 'Ej. 6015550101',
                'patron' => '^[0-9]{7,10}$', 'mensajePatron' => 'Use entre 7 y 10 números, sin espacios.'],
            ['nombre' => 'ciudad', 'etiqueta' => 'Ciudad', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 50, 'valor' => 'Bogotá D.C.'],
            ['nombre' => 'localidad', 'etiqueta' => 'Localidad', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 50, 'ejemplo' => 'Ej. Suba'],
        ],
    ],
    'eps' => [
        'nombre' => 'EPS', 'titulo' => 'Gestión de EPS', 'repo' => EpsRepository::class,
        'unicos' => [[['nombre_eps'], 'nombre_eps', 'Esa EPS ya está registrada.']],
        'campos' => [
            ['nombre' => 'nombre_eps', 'etiqueta' => 'Nombre de la EPS', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 50, 'ejemplo' => 'Ej. Sanitas',
                'patron' => $soloLetras, 'mensajePatron' => 'Use solo letras y espacios.'],
            ['nombre' => 'estado', 'etiqueta' => 'Estado', 'tipo' => 'select', 'requerido' => true, 'numero' => true, 'opciones' => $estado],
        ],
    ],
    'usuarios' => [
        'nombre' => 'Usuarios', 'titulo' => 'Gestión de Usuarios', 'repo' => UsuarioRepository::class, 'soloAdmin' => true,
        'hash' => ['password'], 'noEliminarPropio' => true,
        'unicos' => [
            [['correo'], 'correo', 'Este correo ya está registrado.'],
            [['numero_documento'], 'numero_documento', 'Este documento ya está registrado.'],
        ],
        'campos' => [
            ['nombre' => 'tipo_documento', 'etiqueta' => 'Tipo de documento', 'tipo' => 'select', 'requerido' => true,
                'opciones' => [['CC', 'Cédula de ciudadanía'], ['CE', 'Cédula de extranjería'], ['PA', 'Pasaporte']]],
            ['nombre' => 'numero_documento', 'etiqueta' => 'Número de documento', 'tipo' => 'text', 'requerido' => true, 'max' => 20,
                'patron' => '^[A-Za-z0-9]{5,20}$', 'mensajePatron' => 'Use de 5 a 20 letras o números.'],
            ['nombre' => 'nombre', 'etiqueta' => 'Nombres', 'tipo' => 'text', 'requerido' => true, 'minimo' => 2, 'max' => 50, 'patron' => $soloLetras, 'mensajePatron' => 'Use solo letras y espacios.'],
            ['nombre' => 'apellido', 'etiqueta' => 'Apellidos', 'tipo' => 'text', 'requerido' => true, 'minimo' => 2, 'max' => 50, 'patron' => $soloLetras, 'mensajePatron' => 'Use solo letras y espacios.'],
            ['nombre' => 'correo', 'etiqueta' => 'Correo electrónico', 'tipo' => 'email', 'requerido' => true, 'max' => 100, 'ejemplo' => 'correo@ejemplo.com'],
            ['nombre' => 'password', 'etiqueta' => 'Contraseña (vacía = no cambiar)', 'tipo' => 'password', 'requerido' => true, 'opcionalAlEditar' => true, 'minimo' => 6, 'max' => 72],
            ['nombre' => 'telefono', 'etiqueta' => 'Teléfono', 'tipo' => 'tel', 'max' => 15, 'patron' => '^[0-9]{7,10}$', 'mensajePatron' => 'Use entre 7 y 10 números, sin espacios.'],
            ['nombre' => 'idRol', 'etiqueta' => 'Rol', 'tipo' => 'select', 'requerido' => true, 'numero' => true,
                'origen' => ['repo' => RolRepository::class, 'valor' => 'idRol', 'texto' => 'nombre_rol']],
            ['nombre' => 'idEPS', 'etiqueta' => 'EPS', 'tipo' => 'select', 'requerido' => true, 'numero' => true,
                'origen' => ['repo' => EpsRepository::class, 'valor' => 'idEPS', 'texto' => 'nombre_eps']],
        ],
    ],
    'roles' => [
        'nombre' => 'Roles', 'titulo' => 'Gestión de Roles', 'repo' => RolRepository::class, 'soloAdmin' => true,
        'unicos' => [[['nombre_rol'], 'nombre_rol', 'Ese rol ya existe.']],
        'campos' => [
            ['nombre' => 'nombre_rol', 'etiqueta' => 'Nombre del rol', 'tipo' => 'text', 'requerido' => true, 'minimo' => 3, 'max' => 30, 'ejemplo' => 'Ej. Administrador',
                'patron' => $soloLetras, 'mensajePatron' => 'Use solo letras y espacios.'],
        ],
    ],
];
