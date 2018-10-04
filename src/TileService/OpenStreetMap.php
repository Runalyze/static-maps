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

class OpenStreetMap extends AbstractTemplateBasedTileService
{
    protected $TileTemplate = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    protected $Subdomains = ['a', 'b', 'c'];

    public function getMinZoom(): int
    {
        return 0;
    }

    public function getMaxZoom(): int
    {
        return 19;
    }

    public function getAttributionText(): string
    {
        return '© OpenStreetMap';
    }
}
