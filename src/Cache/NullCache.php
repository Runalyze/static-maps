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

namespace Runalyze\StaticMaps\Cache;

use Intervention\Image\Image;
use Runalyze\StaticMaps\Tile\TileImageInterface;

class NullCache implements CacheInterface
{
    public function hasTile(TileImageInterface $tile): bool
    {
        return false;
    }

    public function saveTile(TileImageInterface $tile): bool
    {
        return false;
    }

    public function getTile(TileImageInterface $tile): Image
    {
        return $tile->getImage();
    }

    public function deleteTile(TileImageInterface $tile): bool
    {
        return false;
    }
}
