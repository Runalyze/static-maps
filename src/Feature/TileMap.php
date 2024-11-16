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

use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use JetBrains\PhpStorm\NoReturn;
use Runalyze\StaticMaps\Tile\Tile;
use Runalyze\StaticMaps\TileProvider;
use Runalyze\StaticMaps\Viewport\BoundingBoxInterface;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class TileMap implements FeatureInterface
{
    protected TileProvider $TileProvider;
    protected Image $Image;
    protected int $Zoom = 0;
    protected int $StartX;
    protected int $StartY;
    protected int $EndX;
    protected int $EndY;
    protected bool $IsDebuggingEnabled = false;

    public function __construct(TileProvider $tileProvider)
    {
        $this->TileProvider = $tileProvider;
    }

    public function enableDebugging(bool $flag = true): self
    {
        $this->IsDebuggingEnabled = $flag;

        return $this;
    }

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport): void
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

    #[NoReturn]
    public function debugFullImage(): void
    {
        echo $this->Image->toPng();

        exit;
    }

    protected function readProjection(ViewportInterface $viewport): void
    {
        $this->Zoom = $viewport->getZoom();

        list($this->StartX, $this->EndX) = $viewport->getRangeX();
        list($this->StartY, $this->EndY) = $viewport->getRangeY();
    }

    protected function createBaseMap(ImageManager $imageManager, ViewportInterface $viewport): void
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

    protected function drawTileDebugInformation(int $x, int $y, int $destX, int $destY, int $tileSize): void
    {
        $this->Image->drawRectangle($destX, $destY, function (RectangleFactory $rectangle) use ($destX, $destY, $tileSize) {
            $rectangle->size($destX + $tileSize, $destY + $tileSize);
            $rectangle->border('#c00', 1);
        });

        $this->Image->text($x.' / '.$y, (int)round($destX + $tileSize / 2.0), (int)round($destY + $tileSize / 2.0), function ($draw) {
            $draw->file(5);
            $draw->align('center');
            $draw->valign('center');
        });
    }

    protected function drawBoundingBoxes(ViewportInterface $viewport): void
    {
        $this->drawBorderForBoundingBox($viewport, $viewport->getTileFittingBoundingBox(), function (RectangleFactory $draw) {
            $draw->border('#0cc', 2);
        });

        $this->drawBorderForBoundingBox($viewport, $viewport->getPromisedBoundingBox(), function (RectangleFactory $draw) {
            $draw->border('#0c0', 2);
        });

        $this->drawBorderForBoundingBox($viewport, $viewport->getBoundingBox(), function (RectangleFactory $draw) {
            $draw->border('#00c', 2);
        });
    }

    protected function cropImageToBoundaries(ViewportInterface $viewport): void
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

    protected function drawBorderForBoundingBox(ViewportInterface $viewport, BoundingBoxInterface $boundingBox, callable $callback, bool $drawMarkerForCenter = true): void
    {
        $tileSize = $this->TileProvider->getTileSize();
        $centerX = $viewport->longitudeToTileX($boundingBox->getCenterLongitude());
        $centerY = $viewport->latitudeToTileY($boundingBox->getCenterLatitude());
        list($upper, $right, $lower, $left) = $this->getExactBoundaries($viewport, $boundingBox);

        $this->Image->drawRectangle(
            (int)(($left - $this->StartX) * $tileSize),
            (int)(($upper - $this->StartY) * $tileSize),
            function (RectangleFactory $rectangle) use ($right, $lower, $tileSize, $callback) {
                $rectangle->size((int)(($right - $this->StartX) * $tileSize), (int)(($lower - $this->StartY) * $tileSize));
                $callback($rectangle);
            }
        );

        if ($drawMarkerForCenter) {
            $this->Image->drawRectangle(
                (int)(($centerX - $this->StartX) * $tileSize),
                (int)(($centerY - $this->StartY) * $tileSize),
                function (RectangleFactory $rectangle) use ($centerX, $centerY, $tileSize, $callback) {
                    $rectangle->size((int)(1 + ($centerX - $this->StartX) * $tileSize), (int)(1 + ($centerY - $this->StartY) * $tileSize));
                    $callback($rectangle);
                }
            );
        }
    }
}
