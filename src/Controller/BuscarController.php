<?php

declare(strict_types=1);

namespace PharmaGo\Controller;

use PharmaGo\Core\View;
use PharmaGo\Repository\DisponibilidadRepository;

/** Reemplaza a js/disponibilidad/disponibilidad{Controller,View}.js. */
final class BuscarController
{
    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $error = '';
        $resultados = [];
        $sel = max(0, (int) ($_GET['sel'] ?? 0));

        if (isset($_GET['q'])) {
            if ($q === '') {
                $error = 'Escriba el nombre de un medicamento.';
            } elseif (preg_match('/^[0-9\s]+$/', $q)) {
                $error = 'El nombre no puede ser solo números.';
            } elseif (mb_strlen($q) < 2) {
                $error = 'Escriba al menos 2 letras.';
            } else {
                $resultados = (new DisponibilidadRepository())->buscar($q);
            }
        }
        $sel = min($sel, max(0, count($resultados) - 1));
        View::render('buscar', compact('q', 'error', 'resultados', 'sel') + ['buscado' => isset($_GET['q'])]);
    }
}
