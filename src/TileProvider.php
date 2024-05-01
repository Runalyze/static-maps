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

namespace Runalyze\StaticMaps;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Runalyze\StaticMaps\Cache\CacheInterface;
use Runalyze\StaticMaps\Cache\NullCache;
use Runalyze\StaticMaps\Tile\TileImage;
use Runalyze\StaticMaps\Tile\TileImageInterface;
use Runalyze\StaticMaps\Tile\TileInterface;
use Runalyze\StaticMaps\TileService\TileServiceInterface;

class TileProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    protected CacheInterface|NullCache $Cache;

    public function __construct(
        protected TileServiceInterface $TileService,
        protected ImageManager $ImageManager,
        CacheInterface $tileCache = null,
        LoggerInterface $logger = null
    ) {
        $this->Cache = $tileCache ?? new NullCache();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getTileSize(): int
    {
        return $this->TileService->getTileSize();
    }

    public function getMinZoom(): int
    {
        return $this->TileService->getMinZoom();
    }

    public function getMaxZoom(): int
    {
        return $this->TileService->getMaxZoom();
    }

    public function fetchTile(TileInterface $tile): TileImageInterface
    {
        $tileImage = new TileImage($this->TileService, $tile);

        if ($this->Cache->hasTile($tileImage)) {
            $tileImage->setImage($this->Cache->getTile($tileImage));

            return $tileImage;
        }

        try {
            $content = file_get_contents($tileImage->getTileUrl());
            $tileImage->setImage($this->ImageManager->read($content));
        } catch (NotReadableException $e) {
            $this->logger->warning('Tile is not readable.', ['url' => $tileImage->getTileUrl()]);
        }

        if ($tileImage->hasImage()) {
            $this->Cache->saveTile($tileImage);
        } else {
            $tileImage->setImage($this->getErrorTileImage());
        }

        return $tileImage;
    }

    protected function getErrorTileImage(): Image
    {
        $tileSize = $this->TileService->getTileSize();
        $centerPosition = (int)floor($tileSize / 2);
        $randColor = 225 + rand(0, 20);

        $image = $this->ImageManager->create($tileSize, $tileSize, [$randColor, $randColor, $randColor]);
        $image->text('Tile missing', $centerPosition, $centerPosition, function (FontFactory $font) {
            $font->file(2);
            $font->color('#c00');
            $font->align('center');
        });

        return $image;
    }
}
