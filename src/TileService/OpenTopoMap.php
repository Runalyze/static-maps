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

class OpenTopoMap extends AbstractTemplateBasedTileService
{
    protected $TileTemplate = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';

    protected $Subdomains = ['a', 'b', 'c'];

    public function getMinZoom(): int
    {
        return 0;
    }

    public function getMaxZoom(): int
    {
        return 17;
    }

    public function getAttributionText(): string
    {
        return 'Map data: &copy; OpenStreetMap, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)';
    }
}
