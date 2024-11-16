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

use Intervention\Image\Colors\Rgb\Color;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Runalyze\StaticMaps\Drawer\Polyline\AntialiasPolylineDrawer;
use Runalyze\StaticMaps\Viewport\BoundingBox;
use Runalyze\StaticMaps\Viewport\BoundingBoxInterface;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class Route implements FeatureInterface
{
    protected array $LineSegments;
    protected int $LineWidth;
    protected array $LineColor;

    /** @var float [px] */
    protected float $LineSimplificationTolerance = 0.0;

    /**
     * @param array $coordinates
     * @param string|array $lineColor
     * @param int $lineWidth
     */
    public function __construct(array $coordinates, string|array $lineColor = '#000', int $lineWidth = 1)
    {
        $this->LineSegments = $this->getLineSegments($coordinates);
        $this->LineWidth = $lineWidth;
        $this->LineColor = $this->getLineColorArray($lineColor);
    }

    public function enableLineSimplification(float $pixelTolerance = 10.0): void
    {
        $this->LineSimplificationTolerance = $pixelTolerance;
    }

    public function disableLineSimplification(): void
    {
        $this->LineSimplificationTolerance = 0.0;
    }

    protected function getLineColorArray($lineColor): array
    {
        /** @var Color $color */
        $color = Color::create($lineColor);

        return [$color->red()->value(), $color->green()->value(), $color->blue()->value(), 100.0 * $color->alpha()->value() / 255];
    }

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport): void
    {
        $drawer = new AntialiasPolylineDrawer();
        $drawer->setPainter($this->LineColor[0], $this->LineColor[1], $this->LineColor[2], $this->LineColor[3], $this->LineWidth);

        foreach ($this->LineSegments as $segment) {
            $points = [$this->getRelativePositionForPoint($viewport, $segment[0])];
            $numPoints = count($segment);
            $lastPoint = 0;

            if (1 == $numPoints) {
                $numPoints = 2;
                $segment[1] = $segment[0];
            }

            for ($i = 1; $i < $numPoints; ++$i) {
                list($x1, $y1) = $this->getRelativePositionForPoint($viewport, $segment[$lastPoint]);
                list($x2, $y2) = $this->getRelativePositionForPoint($viewport, $segment[$i]);

                if (0.0 < $this->LineSimplificationTolerance && $i !== $numPoints - 1 && sqrt(pow($x2 - $x1, 2.0) + pow($y2 - $y1, 2.0)) < $this->LineSimplificationTolerance) {
                    continue;
                }

                $points[] = [$x2, $y2];
                $lastPoint = $i;
            }

            $drawer->addPolyline($points);
        }

        $drawer->drawPolylines($image->core()->native());
    }

    protected function getRelativePositionForPoint(ViewportInterface $viewport, array $point): array
    {
        return [
            $viewport->getRelativePositionForLongitude($point[1]),
            $viewport->getRelativePositionForLatitude($point[0])
        ];
    }

    protected function getLineSegments(array $coordinates): array
    {
        if (empty($coordinates)) {
            return [];
        }

        if (2 == count($coordinates[0]) && !is_array($coordinates[0][0])) {
            return [$coordinates];
        }

        return $coordinates;
    }

    public function getBoundingBox(float $paddingInPercent = 0.0, float $paddingRight = null, float $paddingBottom = null, float $paddingLeft = null): BoundingBoxInterface
    {
        $minLatitude = 90.0;
        $maxLatitude = -90.0;
        $minLongitude = 180.0;
        $maxLongitude = -180.0;
        $paddingTop = $paddingInPercent;
        $paddingRight = null === $paddingRight ? $paddingInPercent : $paddingRight;
        $paddingBottom = null === $paddingBottom ? $paddingInPercent : $paddingBottom;
        $paddingLeft = null === $paddingLeft ? $paddingInPercent : $paddingLeft;

        foreach ($this->LineSegments as $segment) {
            foreach ($segment as $coordinates) {
                if ($coordinates[0] < $minLatitude) {
                    $minLatitude = $coordinates[0];
                }

                if ($coordinates[0] > $maxLatitude) {
                    $maxLatitude = $coordinates[0];
                }

                if ($coordinates[1] < $minLongitude) {
                    $minLongitude = $coordinates[1];
                }

                if ($coordinates[1] > $maxLongitude) {
                    $maxLongitude = $coordinates[1];
                }
            }
        }

        $boundingBox = new BoundingBox($minLatitude, $maxLatitude, $minLongitude, $maxLongitude);

        if (0.0 < max($paddingTop, $paddingRight, $paddingBottom, $paddingLeft)) {
            return $boundingBox->extendBy($paddingTop, $paddingRight, $paddingBottom, $paddingLeft);
        }

        return $boundingBox;
    }

    public function isEmpty(): bool
    {
        return empty($this->LineSegments);
    }
}
