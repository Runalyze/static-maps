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

namespace Runalyze\StaticMaps\Viewport;

class BoundingBox implements BoundingBoxInterface
{
    /** @var float */
    protected $MinLatitude;

    /** @var float */
    protected $MaxLatitude;

    /** @var float */
    protected $MinLongitude;

    /** @var float */
    protected $MaxLongitude;

    public function __construct(float $minLatitude, float $maxLatitude, float $minLongitude, float $maxLongitude)
    {
        $this->MinLatitude = $minLatitude;
        $this->MaxLatitude = $maxLatitude;
        $this->MinLongitude = $minLongitude;
        $this->MaxLongitude = $maxLongitude;
    }

    public function getMinLatitude(): float
    {
        return $this->MinLatitude;
    }

    public function getMaxLatitude(): float
    {
        return $this->MaxLatitude;
    }

    public function getMinLongitude(): float
    {
        return $this->MinLongitude;
    }

    public function getMaxLongitude(): float
    {
        return $this->MaxLongitude;
    }

    public function getCenterLatitude(): float
    {
        return ($this->MinLatitude + $this->MaxLatitude) / 2.0;
    }

    public function getCenterLongitude(): float
    {
        $center = ($this->MinLongitude + $this->MaxLongitude) / 2.0;

        if ($this->MaxLongitude - $this->MinLongitude > 180.0) {
            if ($center < 0.0) {
                $center += 180.0;
            } else {
                $center -= 180.0;
            }
        }

        return $center;
    }

    public function extendBy(float $paddingInPercent = 0.0, float $paddingRight = null, float $paddingBottom = null, float $paddingLeft = null): BoundingBoxInterface
    {
        $paddingTop = $paddingInPercent / (100.0 - $paddingInPercent);
        $paddingRight = null === $paddingRight ? $paddingTop : $paddingRight / (100.0 - $paddingRight);
        $paddingBottom = null === $paddingBottom ? $paddingTop : $paddingBottom / (100.0 - $paddingBottom);
        $paddingLeft = null === $paddingLeft ? $paddingTop : $paddingLeft / (100.0 - $paddingLeft);

        $deltaLatitudeY = ($this->projectLatitudeToY($this->MaxLatitude) - $this->projectLatitudeToY($this->MinLatitude));
        $deltaLongitude = $this->MaxLongitude - $this->MinLongitude;

        $this->MinLatitude = $this->projectYToLatitude($this->projectLatitudeToY($this->MinLatitude) - $deltaLatitudeY * $paddingBottom);
        $this->MaxLatitude = $this->projectYToLatitude($this->projectLatitudeToY($this->MaxLatitude) + $deltaLatitudeY * $paddingTop);
        $this->MinLongitude -= $deltaLongitude * $paddingLeft;
        $this->MaxLongitude += $deltaLongitude * $paddingRight;

        return $this;
    }

    private function projectLatitudeToY(float $latitude): float
    {
        $zoom = 10;

        return (1.0 - log(tan($latitude * pi() / 180.0) + 1.0 / cos($latitude * pi() / 180.0)) / pi()) / 2.0 * pow(2.0, $zoom);
    }

    private function projectYToLatitude(float $y): float
    {
        $zoom = 10;

        return atan(sinh(pi() * (1.0 - 2.0 * $y / pow(2.0, $zoom)))) * 180.0 / pi();
    }
}
