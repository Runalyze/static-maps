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
use Runalyze\StaticMaps\TileService\TileServiceInterface;

class TileImage implements TileImageInterface
{
    /** @var TileServiceInterface */
    protected $TileService;

    /** @var TileInterface */
    protected $Tile;

    /** @var Image|null */
    protected $Image = null;

    public function __construct(TileServiceInterface $service, TileInterface $tile, Image $image = null)
    {
        $this->TileService = $service;
        $this->Tile = $tile;
        $this->Image = $image;
    }

    public function getTileUrl(): string
    {
        return $this->TileService->getTileUrl($this->Tile);
    }

    public function getTileServiceSlug(): string
    {
        return $this->TileService->getSlug();
    }

    public function getTile(): TileInterface
    {
        return $this->Tile;
    }

    public function setImage(Image $image)
    {
        $this->Image = $image;
    }

    public function getImage(): Image
    {
        return $this->Image;
    }

    public function hasImage(): bool
    {
        return null !== $this->Image;
    }
}
