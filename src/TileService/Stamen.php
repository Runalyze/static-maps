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

class Stamen extends AbstractVariantTemplateBasedTileService
{
    /** @var string */
    const VARIANT_TONER = 'toner';

    /** @var string */
    const VARIANT_TONER_LITE = 'toner-lite';

    /** @var string */
    const VARIANT_TERRAIN = 'terrain';

    protected $TileTemplate = 'https://stamen-tiles-{s}.a.ssl.fastly.net/{variant}/{z}/{x}/{y}{r}.png';

    protected $Subdomains = ['a', 'b', 'c', 'd'];

    public function __construct(string $variant = 'toner-lite')
    {
        parent::__construct($variant);
    }

    public function getMinZoom(): int
    {
        return 0;
    }

    public function getMaxZoom(): int
    {
        return 20;
    }

    public function getAttributionText(): string
    {
        return '© Map tiles by Stamen Design, CC BY 3.0 - Map data OpenStreetMap';
    }
}
