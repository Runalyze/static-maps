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

interface BoundingBoxInterface
{
    public function __construct(float $minLatitude, float $maxLatitude, float $minLongitude, float $maxLongitude);

    public function getMinLatitude(): float;

    public function getMaxLatitude(): float;

    public function getMinLongitude(): float;

    public function getMaxLongitude(): float;

    public function getCenterLatitude(): float;

    public function getCenterLongitude(): float;
}
