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

class CartoDB extends AbstractVariantTemplateBasedTileService
{
    /** @var string */
    const VARIANT_LIGHT = 'light_all';

    /** @var string */
    const VARIANT_DARK = 'dark_all';

    /** @var string */
    const VARIANT_VOYAGER = 'rastertiles/voyager';

    protected $TileTemplate = 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/{variant}/{z}/{x}/{y}{r}.png';

    protected $Subdomains = ['a', 'b', 'c', 'd'];

    public function __construct(string $variant = 'light_all')
    {
        parent::__construct($variant);
    }

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
        return 'OpenStreetMap © CartoDB';
    }
}
