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

namespace Runalyze\StaticMaps\Cache;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemInterface;
use Runalyze\StaticMaps\Tile\TileImageInterface;

class FilesystemCache implements CacheInterface
{
    /** @var FilesystemInterface */
    protected $Filesystem;

    /** @var ImageManager */
    protected $ImageManager;

    public function __construct(FilesystemInterface $filesystem, ImageManager $imageManager)
    {
        $this->Filesystem = $filesystem;
        $this->ImageManager = $imageManager;
    }

    protected function getTilePath(TileImageInterface $tile): string
    {
        return sprintf(
            '%s/%u/%u/%u.png',
            $tile->getTileServiceSlug(),
            $tile->getTile()->getZoom(),
            $tile->getTile()->getX(),
            $tile->getTile()->getY()
        );
    }

    public function hasTile(TileImageInterface $tile): bool
    {
        return $this->Filesystem->has($this->getTilePath($tile));
    }

    public function saveTile(TileImageInterface $tile): bool
    {
        if ($tile->hasImage()) {
            return $this->Filesystem->put($this->getTilePath($tile), (string)$tile->getImage()->encode('png'));
        }

        return false;
    }

    public function getTile(TileImageInterface $tile): Image
    {
        $contents = $this->Filesystem->read($this->getTilePath($tile));

        $tile->setImage($this->ImageManager->make($contents));

        if (!$tile->hasImage()) {
            throw new \RuntimeException(sprintf('Tile cannot be found at %s.', $this->getTilePath($tile)));
        }

        return $tile->getImage();
    }

    public function deleteTile(TileImageInterface $tile): bool
    {
        return $this->Filesystem->delete($this->getTilePath($tile));
    }
}
