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
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Runalyze\StaticMaps\Tile\TileImageInterface;

class FilesystemCache implements CacheInterface
{
    public function __construct(
        protected FilesystemOperator $Filesystem,
        protected ImageManager $ImageManager
    ) {
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
        return $this->Filesystem->fileExists($this->getTilePath($tile));
    }

    public function saveTile(TileImageInterface $tile): bool
    {
        if ($tile->hasImage()) {
            try {
                $this->Filesystem->write($this->getTilePath($tile), (string)$tile->getImage()->toPng());
            } catch (FilesystemException $e) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getTile(TileImageInterface $tile): Image
    {
        $contents = $this->Filesystem->read($this->getTilePath($tile));

        $tile->setImage($this->ImageManager->read($contents));

        if (!$tile->hasImage()) {
            throw new \RuntimeException(sprintf('Tile cannot be found at %s.', $this->getTilePath($tile)));
        }

        return $tile->getImage();
    }

    public function deleteTile(TileImageInterface $tile): bool
    {
        try {
            $this->Filesystem->delete($this->getTilePath($tile));
        } catch (FilesystemException $e) {
            return false;
        }

        return true;
    }
}
