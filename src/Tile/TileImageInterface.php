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

namespace Runalyze\StaticMaps\Tile;

use Intervention\Image\Image;

interface TileImageInterface
{
    public function getTileUrl(): string;

    public function getTileServiceSlug(): string;

    public function getTile(): TileInterface;

    public function setImage(Image $image);

    public function getImage(): Image;

    public function hasImage(): bool;
}
