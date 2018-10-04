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

abstract class AbstractTemplateBasedTileService implements TileServiceInterface
{
    protected $TileTemplate = 'https://{s}.sub.domain.tld/dir/{z}/{x}/{y}{r}.png';

    protected $Subdomains = [];

    public function getTileUrl(TileInterface $tile, string $subDomain = '', string $r = ''): string
    {
        if (empty($subDomain) && !empty($this->Subdomains)) {
            $subDomain = $this->Subdomains[array_rand($this->Subdomains)];
        }

        return str_replace(
            ['{z}', '{x}', '{y}', '{r}', '{s}'],
            [$tile->getZoom(), $tile->getX(), $tile->getY(), $r, $subDomain],
            $this->TileTemplate
        );
    }

    public function getTileSize(): int
    {
        return 256;
    }

    public function getSlug(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
