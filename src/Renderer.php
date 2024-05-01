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

namespace Runalyze\StaticMaps;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Runalyze\StaticMaps\Cache\CacheInterface;
use Runalyze\StaticMaps\Map\MapInterface;
use Runalyze\StaticMaps\TileService\TileServiceInterface;

class Renderer
{
    /** @var ImageManager */
    protected $ImageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->ImageManager = $imageManager;
    }

    public function renderMap(MapInterface $map): Image
    {
        $projection = $map->getProjection();
        $image = $this->ImageManager->create($projection->getWidth(), $projection->getHeight(), '#000');

        foreach ($map->getFeatures() as $feature) {
            $feature->render($this->ImageManager, $image, $projection);
        }

        return $image;
    }
}
