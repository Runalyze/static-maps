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

namespace Runalyze\StaticMaps\Viewport;

use Runalyze\StaticMaps\TileService\TileServiceInterface;

class Viewport extends AbstractViewport
{
    public function __construct(int $width, int $height, BoundingBoxInterface $minimalBoundingBox, TileServiceInterface $tileService)
    {
        if ($width > self::MAX_WIDTH) {
            throw new \InvalidArgumentException(sprintf('Width must not exceed %u, %u given.', self::MAX_WIDTH, $width));
        }

        if ($height > self::MAX_HEIGHT) {
            throw new \InvalidArgumentException(sprintf('Height must not exceed %u, %u given.', self::MAX_HEIGHT, $height));
        }

        $this->Width = $width;
        $this->Height = $height;
        $this->TileService = $tileService;
        $this->PromisedBoundingBox = $minimalBoundingBox;
        $this->TileFittingBoundingBox = $this->extendBoundingBoxToFitTileDimensions($minimalBoundingBox);
        $this->BoundingBox = $this->extendBoundingBoxToFitImageDimensions($minimalBoundingBox);
    }

    protected function extendBoundingBoxToFitTileDimensions(BoundingBoxInterface $minimalBoundingBox): BoundingBoxInterface
    {
        $this->calculateZoomToFitBounds($minimalBoundingBox);

        return $this->extendBoundingBox($minimalBoundingBox);
    }

    protected function calculateZoomToFitBounds(BoundingBoxInterface $minimalBoundingBox)
    {
        $minZoom = $this->TileService->getMinZoom();
        $maxZoom = $this->TileService->getMaxZoom();
        $tileSize = $this->TileService->getTileSize();
        $centerLatitude = $minimalBoundingBox->getCenterLatitude();
        $centerLongitude = $minimalBoundingBox->getCenterLongitude();
        $minLatitude = $minimalBoundingBox->getMinLatitude();
        $maxLatitude = $minimalBoundingBox->getMaxLatitude();
        $minLongitude = $minimalBoundingBox->getMinLongitude();
        $maxLongitude = $minimalBoundingBox->getMaxLongitude();

        for ($zoom = $maxZoom; $zoom >= $minZoom; --$zoom) {
            $exactRangeX = [$this->longitudeToTileX($minLongitude, $zoom), $this->longitudeToTileX($maxLongitude, $zoom)];
            $exactRangeY = [$this->latitudeToTileY($maxLatitude, $zoom), $this->latitudeToTileY($minLatitude, $zoom)];
            $requiredWidth = ceil($tileSize * ($exactRangeX[1] - $exactRangeX[0]));
            $requiredHeight = ceil($tileSize * ($exactRangeY[1] - $exactRangeY[0]));

            if ($requiredWidth <= $this->Width && $requiredHeight <= $this->Height) {
                $centerX = $this->longitudeToTileX($centerLongitude, $zoom);
                $centerY = $this->latitudeToTileY($centerLatitude, $zoom);

                $this->Zoom = $zoom;
                $this->RangeX = [
                    (int)floor($centerX - ($this->Width / $tileSize) / 2.0),
                    (int)floor($centerX + ($this->Width / $tileSize) / 2.0)
                ];
                $this->RangeY = [
                    (int)floor($centerY - ($this->Height / $tileSize) / 2.0),
                    (int)floor($centerY + ($this->Height / $tileSize) / 2.0)
                ];

                return;
            }
        }

        throw new \RuntimeException('Zoom couldn\'t be calculated.');
    }

    public function extendBoundingBox()
    {
        $tileSize = $this->TileService->getTileSize();
        $relativeTileSizeMinusOnePixel = ($tileSize - 1) / $tileSize;

        list($maxLatitude, $minLongitude) = $this->tileToLatLon($this->RangeX[0], $this->RangeY[0], $this->Zoom);
        list($minLatitude, $maxLongitude) = $this->tileToLatLon($this->RangeX[1] + $relativeTileSizeMinusOnePixel, $this->RangeY[1] + $relativeTileSizeMinusOnePixel, $this->Zoom);

        return new BoundingBox($minLatitude, $maxLatitude, $minLongitude, $maxLongitude);
    }

    protected function extendBoundingBoxToFitImageDimensions(BoundingBoxInterface $minimalBoundingBox): BoundingBoxInterface
    {
        $tileSize = $this->TileService->getTileSize();
        $centerX = $this->longitudeToTileX($minimalBoundingBox->getCenterLongitude(), $this->Zoom);
        $centerY = $this->latitudeToTileY($minimalBoundingBox->getCenterLatitude(), $this->Zoom);
        list($minX, $maxX) = [
            ($centerX - ($this->Width / $tileSize) / 2.0),
            ($centerX + ($this->Width / $tileSize) / 2.0)
        ];
        list($minY, $maxY) = [
            ($centerY - ($this->Height / $tileSize) / 2.0),
            ($centerY + ($this->Height / $tileSize) / 2.0)
        ];

        list($maxLatitude, $minLongitude) = $this->tileToLatLon($minX, $minY, $this->Zoom);
        list($minLatitude, $maxLongitude) = $this->tileToLatLon($maxX, $maxY, $this->Zoom);

        $this->TileOffsetX = $minX - (float)$this->RangeX[0];
        $this->TileOffsetY = $minY - (float)$this->RangeY[0];

        return new BoundingBox($minLatitude, $maxLatitude, $minLongitude, $maxLongitude);
    }
}
