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

namespace Runalyze\StaticMaps\Drawer;

class AntialiasDrawer
{
    /** @var bool */
    protected $IsNativeFunctionAvailable;

    /** @var bool */
    protected $UseNativeFunctionIfAvailable = false;

    /** @var float */
    protected $AntialiasThreshold;

    public function __construct(float $antialiasThreshold = 0.0)
    {
        $this->IsNativeFunctionAvailable = function_exists('imageantialias');
        $this->AntialiasThreshold = $antialiasThreshold;
    }

    public function enableNativeAntialiasingIfAvailable(bool $flag)
    {
        $this->UseNativeFunctionIfAvailable = $flag;
    }

    public function drawLine($resource, $x1, $y1, $x2, $y2, int $r, int $g, int $b, float $alpha = 100.0, int $lineWidth = 1)
    {
        if ($this->IsNativeFunctionAvailable && $this->UseNativeFunctionIfAvailable) {
            $this->drawNativeLine($resource, $x1, $y1, $x2, $y2, $r, $g, $b, $alpha, $lineWidth);
        } else {
            $this->drawAntialiasLine($resource, $x1, $y1, $x2, $y2, $r, $g, $b, $alpha, $lineWidth);
        }
    }

    protected function drawNativeLine($resource, $x1, $y1, $x2, $y2, int $r, int $g, int $b, float $alpha = 100.0, int $lineWidth = 1)
    {
        $x1 = (int)round($x1);
        $x2 = (int)round($x2);
        $y1 = (int)round($y1);
        $y2 = (int)round($y2);

        imagesetthickness($resource, $lineWidth);
        imagealphablending($resource, true);
        imageantialias($resource, true);
        imageline($resource, $x1, $y1, $x2, $y2, $this->allocateColor($resource, $r, $g, $b, $alpha));
    }

    protected function drawAntialiasLine($resource, $x1, $y1, $x2, $y2, int $r, int $g, int $b, float $alpha = 100.0, int $lineWidth = 1)
    {
        $distance = max(1.0, sqrt(($x2 - $x1) * ($x2 - $x1) + ($y2 - $y1) * ($y2 - $y1)));

        if (0.0 == $distance) {
            return;
        }

        if ($lineWidth > 1) {
            $angle = $this->getAngle($x1, $y1, $x2, $y2);
            $weights = range(0.5 - $lineWidth / 2, $lineWidth / 2 - 0.5, 1.0);

            foreach ($weights as $weight) {
                $this->drawLine(
                    $resource,
                    $x1 + $weight * cos(deg2rad($angle - 90.0)),
                    $y1 + $weight * sin(deg2rad($angle - 90.0)),
                    $x2 + $weight * cos(deg2rad($angle - 90.0)),
                    $y2 + $weight * sin(deg2rad($angle - 90.0)),
                    $r,
                    $g,
                    $b,
                    $alpha
                );
            }
        } else {
            $xStep = ($x2 - $x1) / $distance;
            $yStep = ($y2 - $y1) / $distance;

            for ($i = 0; $i <= $distance; ++$i) {
                $x = $i * $xStep + $x1;
                $y = $i * $yStep + $y1;

                $this->drawAntialiasPixel($resource, $x, $y, $r, $g, $b, $alpha);
            }
        }
    }

    public function drawAntialiasPixel($resource, float $x, float $y, int $r, int $g, int $b, float $alpha = 100.0)
    {
        $xi   = (int)floor($x);
        $yi   = (int)floor($y);
        
        if ($xi == $x && $yi == $y) {
            $this->drawAlphaPixel($resource, $xi, $yi, $r, $g, $b, $alpha);
        } else {
            $alpha1 = (((1.0 - ($x - floor($x))) * (1.0 - ($y - floor($y))) * 100.0) / 100.0) * $alpha;
            if ($alpha1 > $this->AntialiasThreshold) {
                $this->drawAlphaPixel($resource, $xi, $yi, $r, $g, $b, $alpha1);
            }
            
            $alpha2 = ((($x - floor($x)) * (1.0 - ($y - floor($y))) * 100.0) / 100.0) * $alpha;
            if ($alpha2 > $this->AntialiasThreshold) {
                $this->drawAlphaPixel($resource, $xi + 1, $yi, $r, $g, $b, $alpha2);
            }
            
            $alpha3 = (((1.0 - ($x - floor($x))) * ($y - floor($y)) * 100.0) / 100.0) * $alpha;
            if ($alpha3 > $this->AntialiasThreshold) {
                $this->drawAlphaPixel($resource, $xi, $yi + 1, $r, $g, $b, $alpha3);
            }
            
            $alpha4 = ((($x - floor($x)) * ($y - floor($y)) * 100.0) / 100.0) * $alpha;
            if ($alpha4 > $this->AntialiasThreshold) {
                $this->drawAlphaPixel($resource, $xi + 1, $yi + 1, $r, $g, $b, $alpha4);
            }
        }
    }

    protected function drawAlphaPixel($resource, int $x, int $y, int $r, int $g, int $b, float $alpha)
    {
        imagesetpixel($resource, $x, $y, $this->allocateColor($resource, $r, $g, $b, $alpha));
    }

    protected function allocateColor($resource, int $r, int $g, int $b, float $alpha = 100.0): int
    {
        return imagecolorallocatealpha($resource, $r, $g, $b, (int)(127.0 - $alpha * 127.0 / 100.0));
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
