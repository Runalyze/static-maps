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

use Runalyze\StaticMaps\Drawer\PainterAwareTrait;

class NativePolylineDrawer implements PolylineDrawerInterface
{
    use PainterAwareTrait;

    /** @var bool */
    protected $IsNativeFunctionAvailable;

    /** @var array [polyline_1, polyline_2, ...] with each polyline [[x, y], [x, y], ...] */
    protected $Polylines = [];

    public function __construct()
    {
        $this->IsNativeFunctionAvailable = function_exists('imageantialias');
    }

    public function addPolyline(array $points)
    {
        $this->Polylines[] = array_map(function ($point) {
            return [(int)round($point[0]), (int)round($point[1])];
        }, $points);
    }

    public function drawPolylines($resource)
    {
        $this->prepareNativeDrawing($resource);

        $color = $this->allocateColor($resource);

        foreach ($this->Polylines as $points) {
            $numPoints = count($points);

            for ($i = 1; $i < $numPoints; ++$i) {
                imageline($resource, $points[$i - 1][0], $points[$i - 1][1], $points[$i][0], $points[$i][1], $color);
            }
        }
    }

    protected function prepareNativeDrawing($resource)
    {
        imagesetthickness($resource, $this->LineWidth);
        imagealphablending($resource, true);

        if ($this->IsNativeFunctionAvailable) {
            imageantialias($resource, true);
        }
    }
}
