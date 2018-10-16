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

class AntialiasPolylineDrawer implements PolylineDrawerInterface
{
    use PainterAwareTrait;

    /** @var array [x][y] => alpha */
    protected $AlphaPixels = [];

    public function addPolyline(array $points)
    {
        $numPoints = count($points);

        if (1 == $numPoints) {
            $this->drawAntialiasPixelToBuffer($points[0][0], $points[0][1]);

            return;
        }

        for ($i = 0; $i < $numPoints - 1; ++$i) {
            $currentAngle = $this->getAngle($points[$i][0], $points[$i][1], $points[$i + 1][0], $points[$i + 1][1]);

            list($Ax, $Ay) = $points[$i];
            list($Bx, $By) = $points[$i + 1];

            $weights = range(0.5 - $this->LineWidth / 2, $this->LineWidth / 2 - 0.5, 1.0);

            foreach ($weights as $weight) {
                $this->drawAntialiasLinePixelwiseToBuffer(
                    $Ax + $weight * cos(deg2rad($currentAngle - 90.0)),
                    $Ay + $weight * sin(deg2rad($currentAngle - 90.0)),
                    $Bx + $weight * cos(deg2rad($currentAngle - 90.0)),
                    $By + $weight * sin(deg2rad($currentAngle - 90.0))
                );
            }

            $this->drawAntialiasCircleToBuffer($Ax, $Ay, $this->LineWidth);
        }

        $this->drawAntialiasCircleToBuffer($Bx, $By, $this->LineWidth);
    }

    public function drawPolylines($resource)
    {
        foreach ($this->AlphaPixels as $x => $yPixels) {
            foreach ($yPixels as $y => $alpha) {
                imagesetpixel($resource, $x, $y, $this->allocateColor($resource, $alpha));
            }
        }

        $this->AlphaPixels = [];
    }

    protected function drawAntialiasLinePixelwiseToBuffer($x1, $y1, $x2, $y2)
    {
        $distance = max(1.0, sqrt(($x2 - $x1) * ($x2 - $x1) + ($y2 - $y1) * ($y2 - $y1)));

        $xStep = ($x2 - $x1) / $distance;
        $yStep = ($y2 - $y1) / $distance;

        for ($i = 0; $i <= $distance; ++$i) {
            $this->drawAntialiasPixelToBuffer($i * $xStep + $x1, $i * $yStep + $y1);
        }
    }

    protected function drawAntialiasPixelToBuffer(float $x, float $y)
    {
        $xi = (int)floor($x);
        $yi = (int)floor($y);
        
        if ($xi == $x && $yi == $y) {
            $this->drawAlphaPixelToBuffer($xi, $yi, $this->PainterAlpha);
        } else {
            $deltaX = $x - (float)$xi;
            $deltaY = $y - (float)$yi;
            $alpha1 = (1.0 - $deltaX) * (1.0 - $deltaY) * $this->PainterAlpha;
            $alpha2 = $deltaX * (1.0 - $deltaY) * $this->PainterAlpha;
            $alpha3 = (1.0 - $deltaX) * $deltaY * $this->PainterAlpha;
            $alpha4 = $deltaX * $deltaY * $this->PainterAlpha;

            $this->drawAlphaPixelToBuffer($xi, $yi, $alpha1);
            $this->drawAlphaPixelToBuffer($xi + 1, $yi, $alpha2);
            $this->drawAlphaPixelToBuffer($xi, $yi + 1, $alpha3);
            $this->drawAlphaPixelToBuffer($xi + 1, $yi + 1, $alpha4);
        }
    }

    protected function drawAntialiasCircleToBuffer(float $x, float $y, int $diameter)
    {
        $radius = (float)($diameter / 2);
        $xRange = range(floor($x - $radius), ceil($x + $radius), 1.0);
        $yRange = range(floor($y - $radius), ceil($y + $radius), 1.0);

        foreach ($xRange as $xi) {
            foreach ($yRange as $yi) {
                $deltaX = $xi - $x;
                $deltaY = $yi - $y;
                $delta = sqrt($deltaX * $deltaX + $deltaY * $deltaY);
                $alpha = 100.0 * max(0.0, min(1.0, $radius - $delta));

                $this->drawAlphaPixelToBuffer((int)$xi, (int)$yi, $alpha, false);
            }
        }
    }

    protected function drawAlphaPixelToBuffer(int $x, int $y, float $alpha, bool $additiveAlpha = true)
    {
        if (isset($this->AlphaPixels[$x][$y])) {
            if ($additiveAlpha) {
                $this->AlphaPixels[$x][$y] = min($this->PainterAlpha, $this->AlphaPixels[$x][$y] + $alpha);
            } else {
                $this->AlphaPixels[$x][$y] = max($this->AlphaPixels[$x][$y], $alpha);
            }
        } else {
            $this->AlphaPixels[$x][$y] = $alpha;
        }
    }

    protected function getAngle($x1, $y1, $x2, $y2): float
    {
        return rad2deg(atan2($y2 - $y1, $x2 - $x1));
    }
}
