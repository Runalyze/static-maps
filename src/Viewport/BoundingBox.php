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
}
