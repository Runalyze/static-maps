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

class Thunderforest extends AbstractVariantTemplateBasedTileService
{
    /** @var string */
    const VARIANT_OUTDOORS = 'outdoors';

    /** @var string */
    const VARIANT_CYCLE = 'cycle';

    /** @var string */
    const VARIANT_TRANSPORT = 'transport';

    /** @var string */
    const VARIANT_TRANSPORT_DARK = 'transport-dark';

    /** @var string */
    const VARIANT_SPINAL_MAP = 'spinal-map';

    /** @var string */
    const VARIANT_LANDSCAPE = 'landscape';

    /** @var string */
    const VARIANT_PIONEER = 'pioneer';

    protected $TileTemplate = 'https://{s}.tile.thunderforest.com/{variant}/{z}/{x}/{y}.png';

    protected $Subdomains = ['a', 'b', 'c'];

    /** @var string */
    protected $ApiKey;

    public function __construct(string $apiKey, string $variant = 'outdoors')
    {
        parent::__construct($variant);

        $this->ApiKey = $apiKey;
        $this->Variant = $variant;
    }

    public function getTileUrl(TileInterface $tile, string $subDomain = '', string $r = ''): string
    {
        return parent::getTileUrl($tile, $subDomain, $r).'?apikey='.$this->ApiKey;
    }

    public function getMinZoom(): int
    {
        return 0;
    }

    public function getMaxZoom(): int
    {
        return 22;
    }

    public function getAttributionText(): string
    {
        return '&copy; Thunderforest, OpenStreetMap';
    }
}
