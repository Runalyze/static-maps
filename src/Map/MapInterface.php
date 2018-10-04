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

namespace Runalyze\StaticMaps\Map;

use Runalyze\StaticMaps\Feature\FeatureInterface;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

interface MapInterface
{
    public function __construct(ViewportInterface $viewport);

    public function getProjection(): ViewportInterface;

    public function addFeature(FeatureInterface $feature);

    /**
     * @return FeatureInterface[]
     */
    public function getFeatures(): array;
}
