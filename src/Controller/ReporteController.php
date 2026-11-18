<?php

declare(strict_types=1);

namespace PharmaGo\Controller;

use PharmaGo\Core\View;
use PharmaGo\Repository\ReporteRepository;

/** Reemplaza a js/reportes/reporte{Controller,Model,View}.js. */
final class ReporteController
{
    public function index(): void
    {
        View::render('reportes', ['d' => (new ReporteRepository())->resumen()]);
    }
}
