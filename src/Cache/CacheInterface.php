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

interface CacheInterface
{
    public function hasTile(TileImageInterface $tile): bool;

    public function saveTile(TileImageInterface $tile): bool;

    public function getTile(TileImageInterface $tile): Image;

    public function deleteTile(TileImageInterface $tile): bool;
}
