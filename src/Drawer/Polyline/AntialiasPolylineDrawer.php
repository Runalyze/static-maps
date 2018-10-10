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

        $previousAngle = $this->getAngle($points[0][0], $points[0][1], $points[1][0], $points[1][1]);
        $currentAngle = $previousAngle;

        for ($i = 0; $i < $numPoints - 1; ++$i) {
            $nextAngle = $i < $numPoints - 2 ? $this->getAngle($points[$i + 1][0], $points[$i + 1][1], $points[$i + 2][0], $points[$i + 2][1]) : $currentAngle;

            list($Ax, $Ay) = $points[$i];
            list($Bx, $By) = $points[$i + 1];

            $transitionAngleAtA = ($currentAngle + $previousAngle) / 2.0 - 90.0;
            $transitionAngleAtB = ($currentAngle + $nextAngle) / 2.0 - 90.0;
            $weightFactorAtA = 1.0 / cos(deg2rad(($previousAngle - $currentAngle) / 2.0));
            $weightFactorAtB = 1.0 / cos(deg2rad(($currentAngle - $nextAngle) / 2.0));

            $weights = range(0.5 - $this->LineWidth / 2, $this->LineWidth / 2 - 0.5, 1.0);

            foreach ($weights as $weight) {
                $this->drawAntialiasLineToBuffer(
                    $Ax + $weight * $weightFactorAtA * cos(deg2rad($transitionAngleAtA)),
                    $Ay + $weight * $weightFactorAtA * sin(deg2rad($transitionAngleAtA)),
                    $Bx + $weight * $weightFactorAtB * cos(deg2rad($transitionAngleAtB)),
                    $By + $weight * $weightFactorAtB * sin(deg2rad($transitionAngleAtB)),
                    1
                );
            }

            $previousAngle = $currentAngle;
            $currentAngle = $nextAngle;
        }
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

    protected function drawAntialiasLineToBuffer($x1, $y1, $x2, $y2, int $lineWidth = 1)
    {
        if (1 == $lineWidth) {
            $this->drawAntialiasLinePixelwiseToBuffer($x1, $y1, $x2, $y2);

            return;
        }

        $angle = deg2rad($this->getAngle($x1, $y1, $x2, $y2) - 90.0);
        $weights = range(0.5 - $lineWidth / 2, $lineWidth / 2 - 0.5, 1.0);

        foreach ($weights as $weight) {
            $this->drawAntialiasLineToBuffer(
                $x1 + $weight * cos($angle),
                $y1 + $weight * sin($angle),
                $x2 + $weight * cos($angle),
                $y2 + $weight * sin($angle)
            );
        }
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

    protected function drawAlphaPixelToBuffer(int $x, int $y, float $alpha)
    {
        if (isset($this->AlphaPixels[$x][$y])) {
            $this->AlphaPixels[$x][$y] = min($this->PainterAlpha, $this->AlphaPixels[$x][$y] + $alpha);
        } else {
            $this->AlphaPixels[$x][$y] = $alpha;
        }
    }

    protected function getAngle($x1, $y1, $x2, $y2): float
    {
        $angle = rad2deg(atan2($y2 - $y1, $x2 - $x1));

        if ($angle <= 0.0) {
            return 360.0 - abs($angle);
        }

        return $angle;
    }
}
