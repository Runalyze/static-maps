<?php

declare(strict_types=1);

/*
 * This file is part of the StaticMaps.
 *
 * (c) RUNALYZE <mail@runalyze.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Runalyze\StaticMaps\Drawer\Polyline;

use Runalyze\StaticMaps\Drawer\PainterAwareInterface;

interface PolylineDrawerInterface extends PainterAwareInterface
{
    /**
     * Add polyline to stack
     *
     * Multiple polylines can be added before drawing.
     * This ensures that polylines are drawn at once and the painter's alpha value is not exceeded.
     *
     * When polylines are allowed to generate a larger alpha value by overlapping, `drawPolylines()` has to be called in between.
     *
     * @param array $points [[x, y], [x, y], ...]
     */
    public function addPolyline(array $points);

    /**
     * Draw polylines from stack
     *
     * @param resource $resource
     */
    public function drawPolylines($resource);
}
