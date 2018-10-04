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

namespace Runalyze\StaticMaps\TileService;

use Runalyze\StaticMaps\Tile\TileInterface;

interface TileServiceInterface
{
    public function getTileUrl(TileInterface $tile, string $subDomain = ''): string;

    public function getTileSize(): int;

    public function getMinZoom(): int;

    public function getMaxZoom(): int;

    public function getAttributionText(): string;

    public function getSlug(): string;
}
