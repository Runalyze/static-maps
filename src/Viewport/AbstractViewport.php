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

abstract class AbstractViewport implements ViewportInterface
{
    /** @var int */
    const MAX_WIDTH = 2048;

    /** @var int */
    const MAX_HEIGHT = 2048;

    /** @var int [px] */
    protected $Width;

    /** @var int [px] */
    protected $Height;

    /** @var BoundingBoxInterface */
    protected $TileFittingBoundingBox;

    /** @var BoundingBoxInterface */
    protected $PromisedBoundingBox;

    /** @var BoundingBoxInterface */
    protected $BoundingBox;

    /** @var int */
    protected $Zoom;

    /** @var int[] */
    protected $RangeX;

    /** @var int[] */
    protected $RangeY;

    /** @var TileServiceInterface */
    protected $TileService;

    /** @var int */
    protected $TileOffsetX = 0;

    /** @var int */
    protected $TileOffsetY = 0;

    public function getWidth(): int
    {
        return $this->Width;
    }

    public function getHeight(): int
    {
        return $this->Height;
    }

    public function getTileFittingBoundingBox(): BoundingBoxInterface
    {
        return $this->TileFittingBoundingBox;
    }

    public function getPromisedBoundingBox(): BoundingBoxInterface
    {
        return $this->PromisedBoundingBox;
    }

    public function getBoundingBox(): BoundingBoxInterface
    {
        return $this->BoundingBox;
    }

    public function getZoom(): int
    {
        return $this->Zoom;
    }

    public function getRangeX(): array
    {
        return $this->RangeX;
    }

    public function getStartX(): int
    {
        return $this->RangeX[0];
    }

    public function getEndX(): int
    {
        return $this->RangeX[1];
    }

    public function getRangeY(): array
    {
        return $this->RangeY;
    }

    public function getStartY(): int
    {
        return $this->RangeY[0];
    }

    public function getEndY(): int
    {
        return $this->RangeY[1];
    }

    public function getTileOffsetX(): float
    {
        return $this->TileOffsetX;
    }

    public function getTileOffsetY(): float
    {
        return $this->TileOffsetY;
    }

    public function getTileFittingWidth(): int
    {
        return $this->TileService->getTileSize() * (1 + $this->RangeX[1] - $this->RangeX[0]);
    }

    public function getTileFittingHeight(): int
    {
        return $this->TileService->getTileSize() * (1 + $this->RangeY[1] - $this->RangeY[0]);
    }

    public function longitudeToTileX(float $longitude, int $zoom = null): float
    {
        $zoom = null === $zoom ? $this->Zoom : $zoom;

        return (($longitude + 180.0) / 360.0) * pow(2.0, $zoom);
    }

    public function latitudeToTileY(float $latitude, int $zoom = null): float
    {
        $zoom = null === $zoom ? $this->Zoom : $zoom;

        return (1.0 - log(tan($latitude * pi() / 180.0) + 1.0 / cos($latitude * pi() / 180.0)) / pi()) / 2.0 * pow(2.0, $zoom);
    }

    public function tileToLatLon(float $x, float $y, int $zoom = null): array
    {
        $zoom = null === $zoom ? $this->Zoom : $zoom;

        return [
            atan(sinh(pi() * (1.0 - 2.0 * $y / pow(2.0, $zoom)))) * 180.0 / pi(),
            $x / pow(2.0, $zoom) * 360.0 - 180.0
        ];
    }

    public function getRelativePositionForLatitude(float $latitude): int
    {
        return $this->getRelativePositionForY($this->latitudeToTileY($latitude));
    }

    public function getRelativePositionForLongitude(float $longitude): int
    {
        return $this->getRelativePositionForX($this->longitudeToTileX($longitude));
    }

    public function getRelativePositionForX(float $x): int
    {
        return (int)round(($x - $this->TileOffsetX - $this->RangeX[0]) * $this->TileService->getTileSize());
    }

    public function getRelativePositionForY(float $y): int
    {
        return (int)round(($y - $this->TileOffsetY - $this->RangeY[0]) * $this->TileService->getTileSize());
    }
}
