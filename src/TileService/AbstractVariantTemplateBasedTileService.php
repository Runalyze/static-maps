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

abstract class AbstractVariantTemplateBasedTileService extends AbstractTemplateBasedTileService
{
    /** @var string */
    protected $Variant;

    public function __construct(string $variant)
    {
        $this->Variant = $variant;
    }

    public function getTileUrl(TileInterface $tile, string $subDomain = '', string $r = ''): string
    {
        return str_replace(
            '{variant}',
            $this->Variant,
            parent::getTileUrl($tile, $subDomain, $r)
        );
    }

    public function getSlug(): string
    {
        return parent::getSlug().'.'.$this->Variant;
    }
}
