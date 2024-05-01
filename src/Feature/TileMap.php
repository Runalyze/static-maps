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

namespace Runalyze\StaticMaps\Feature;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Runalyze\StaticMaps\Cache\CacheInterface;
use Runalyze\StaticMaps\Tile\Tile;
use Runalyze\StaticMaps\Tile\TileImage;
use Runalyze\StaticMaps\Tile\TileImageInterface;
use Runalyze\StaticMaps\Tile\TileInterface;
use Runalyze\StaticMaps\TileProvider;
use Runalyze\StaticMaps\Viewport\BoundingBoxInterface;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class TileMap implements FeatureInterface
{
    /** @var TileProvider */
    protected $TileProvider;

    /** @var Image */
    protected $Image;

    /** @var int */
    protected $Zoom = 0;

    /** @var int */
    protected $StartX;

    /** @var int */
    protected $StartY;

    /** @var int */
    protected $EndX;

    /** @var int */
    protected $EndY;

    /** @var bool */
    protected $IsDebuggingEnabled = false;

    public function __construct(TileProvider $tileProvider)
    {
        $this->TileProvider = $tileProvider;
    }

    public function enableDebugging(bool $flag = true): self
    {
        $this->IsDebuggingEnabled = $flag;

        return $this;
    }

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport)
    {
        $this->Image = $imageManager->create($viewport->getTileFittingWidth(), $viewport->getTileFittingHeight());

        $this->readProjection($viewport);
        $this->createBaseMap($imageManager, $viewport);
        $this->cropImageToBoundaries($viewport);

        $image->place($this->Image);
    }

    public function getFullImage(): Image
    {
        return $this->Image;
    }

    public function debugFullImage()
    {
        echo $this->Image->response('png');

        exit;
    }

    protected function readProjection(ViewportInterface $viewport)
    {
        $this->Zoom = $viewport->getZoom();

        list($this->StartX, $this->EndX) = $viewport->getRangeX();
        list($this->StartY, $this->EndY) = $viewport->getRangeY();
    }

    protected function createBaseMap(ImageManager $imageManager, ViewportInterface $viewport)
    {
        $tileSize = $this->TileProvider->getTileSize();

        for ($x = $this->StartX; $x <= $this->EndX + 1; ++$x) {
            for ($y = $this->StartY; $y <= $this->EndY + 1; ++$y) {
                $tileImage = $this->TileProvider->fetchTile(new Tile($x, $y, $this->Zoom))->getImage();

                $destX = ($x - $this->StartX) * $tileSize;
                $destY = ($y - $this->StartY) * $tileSize;

                $this->Image->place($tileImage, 'top-left', $destX, $destY);

                if ($this->IsDebuggingEnabled) {
                    $this->drawTileDebugInformation($x, $y, $destX, $destY, $tileSize);
                }
            }
        }

        if ($this->IsDebuggingEnabled) {
            $this->drawBoundingBoxes($viewport);
        }
    }

    protected function drawTileDebugInformation(int $x, int $y, int $destX, int $destY, int $tileSize)
    {
        $this->Image->rectangle($destX, $destY, $destX + $tileSize, $destY + $tileSize, function ($draw) {
            $draw->border(1, '#c00');
        });

        $this->Image->text($x.' / '.$y, $destX + $tileSize / 2.0, $destY + $tileSize / 2.0, function ($draw) {
            $draw->file(5);
            $draw->align('center');
            $draw->valign('center');
        });
    }

    protected function drawBoundingBoxes(ViewportInterface $viewport)
    {
        $this->drawBorderForBoundingBox($viewport, $viewport->getTileFittingBoundingBox(), function ($draw) {
            $draw->border(2, '#0cc');
        });

        $this->drawBorderForBoundingBox($viewport, $viewport->getPromisedBoundingBox(), function ($draw) {
            $draw->border(2, '#0c0');
        });

        $this->drawBorderForBoundingBox($viewport, $viewport->getBoundingBox(), function ($draw) {
            $draw->border(2, '#00c');
        });
    }

    protected function cropImageToBoundaries(ViewportInterface $viewport)
    {
        $tileSize = $this->TileProvider->getTileSize();
        list($upper, $right, $lower, $left) = $this->getExactBoundaries($viewport, $viewport->getBoundingBox());

        $this->Image->crop(
            (int)(($right - $left) * $tileSize),
            (int)(($lower - $upper) * $tileSize),
            (int)(($left - $this->StartX) * $tileSize),
            (int)(($upper - $this->StartY) * $tileSize)
        );
    }

    protected function getExactBoundaries(ViewportInterface $viewport, BoundingBoxInterface $boundingBox): array
    {
        return [ // upper, right, lower, left
            $viewport->latitudeToTileY($boundingBox->getMaxLatitude()),
            $viewport->longitudeToTileX($boundingBox->getMaxLongitude()),
            $viewport->latitudeToTileY($boundingBox->getMinLatitude()),
            $viewport->longitudeToTileX($boundingBox->getMinLongitude()),
        ];
    }

    protected function drawBorderForBoundingBox(ViewportInterface $viewport, BoundingBoxInterface $boundingBox, callable $callback, bool $drawMarkerForCenter = true)
    {
        $tileSize = $this->TileProvider->getTileSize();
        $centerX = $viewport->longitudeToTileX($boundingBox->getCenterLongitude());
        $centerY = $viewport->latitudeToTileY($boundingBox->getCenterLatitude());
        list($upper, $right, $lower, $left) = $this->getExactBoundaries($viewport, $boundingBox);

        $this->Image->rectangle(
            (int)(($left - $this->StartX) * $tileSize),
            (int)(($upper - $this->StartY) * $tileSize),
            (int)(($right - $this->StartX) * $tileSize),
            (int)(($lower - $this->StartY) * $tileSize),
            $callback
        );

        if ($drawMarkerForCenter) {
            $this->Image->rectangle(
                (int)(($centerX - $this->StartX) * $tileSize),
                (int)(($centerY - $this->StartY) * $tileSize),
                (int)(1 + ($centerX - $this->StartX) * $tileSize),
                (int)(1 + ($centerY - $this->StartY) * $tileSize),
                $callback
            );
        }
    }
}
