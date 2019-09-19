<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:33 PM
 */
Ruta::post("buscarUser","ControlUsers@buscarUser");
Ruta::get("valoresGrafica","ControlUsers@ValoresGraficaBackupsCategoriasGastos");
Ruta::get("valoresGraficaGVSI","ControlUsers@ValoresGrficaGastosvsIngresos");