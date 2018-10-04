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

class Mapbox extends AbstractVariantTemplateBasedTileService
{
    protected $TileTemplate = 'https://api.tiles.mapbox.com/v4/{variant}/{z}/{x}/{y}{r}.png';

    /** @var string */
    protected $AccessToken;

    public function __construct(string $accessToken, string $mapId = 'mapbox.streets')
    {
        parent::__construct($mapId);

        $this->AccessToken = $accessToken;
    }

    public function getTileUrl(TileInterface $tile, string $subDomain = '', string $r = ''): string
    {
        return parent::getTileUrl($tile, $subDomain, $r).'?access_token='.$this->AccessToken;
    }

    public function getMinZoom(): int
    {
        return 0;
    }

    public function getMaxZoom(): int
    {
        return 18;
    }

    public function getAttributionText(): string
    {
        return '© Mapbox, OpenStreetMap';
    }
}
