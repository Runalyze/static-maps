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

interface ViewportInterface
{
    public function getWidth(): int;

    public function getHeight(): int;

    public function getTileFittingBoundingBox(): BoundingBoxInterface;

    public function getPromisedBoundingBox(): BoundingBoxInterface;

    public function getBoundingBox(): BoundingBoxInterface;

    public function getZoom(): int;

    public function getRangeX(): array;

    public function getStartX(): int;

    public function getEndX(): int;

    public function getRangeY(): array;

    public function getStartY(): int;

    public function getEndY(): int;

    public function getTileOffsetX(): float;

    public function getTileOffsetY(): float;

    public function getTileFittingWidth(): int;

    public function getTileFittingHeight(): int;

    public function longitudeToTileX(float $longitude, int $zoom): float;

    public function latitudeToTileY(float $latitude, int $zoom): float;

    public function tileToLatLon(float $x, float $y, int $zoom): array;

    public function getRelativePositionForLatitude(float $latitude): int;

    public function getRelativePositionForLongitude(float $latitude): int;

    public function getRelativePositionForX(float $x): int;

    public function getRelativePositionForY(float $y): int;
}
