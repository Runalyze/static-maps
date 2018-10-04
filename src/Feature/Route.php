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

use Intervention\Image\Gd\Color;
use Intervention\Image\Gd\Shapes\LineShape;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Runalyze\StaticMaps\Drawer\AntialiasDrawer;
use Runalyze\StaticMaps\Viewport\BoundingBox;
use Runalyze\StaticMaps\Viewport\BoundingBoxInterface;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class Route implements FeatureInterface
{
    /** @var array */
    protected $LineSegments;

    /** @var callable */
    protected $LineCallback;

    /** @var int */
    protected $LineWidth;

    /** @var array */
    protected $LineColor;

    /** @var bool */
    protected $Antialiasing;

    /** @var float [px] */
    protected $LineSimplificationTolerance = 0.0;

    /**
     * @param array $coordinates
     * @param string|array $color
     * @param int $lineWidth
     */
    public function __construct(array $coordinates, $lineColor = '#000', int $lineWidth = 1, bool $antialiasing = true)
    {
        $this->LineSegments = $this->getLineSegments($coordinates);
        $this->LineCallback = function (LineShape $draw) use ($lineColor) {
            $draw->color($lineColor);
        };
        $this->LineWidth = $lineWidth;
        $this->LineColor = $this->getLineColorArray($lineColor);
        $this->Antialiasing = $antialiasing;
        $this->AntialiasDrawer = new AntialiasDrawer();
    }

    public function enableAntialiasing(bool $flag)
    {
        $this->Antialiasing = $antialiasing;
    }

    public function enableLineSimplification(float $pixelTolerance = 10)
    {
        $this->LineSimplificationTolerance = $pixelTolerance;
    }

    public function disableLineSimplification()
    {
        $this->LineSimplificationTolerance = 0.0;
    }

    protected function getLineColorArray($lineColor): array
    {
        $color = new Color($lineColor);

        return [$color->r, $color->g, $color->b, 100.0 - $color->a / 1.27];
    }

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport)
    {
        foreach ($this->LineSegments as $segment) {
            $numPoints = count($segment);
            $lastPoint = 0;

            if (1 == $numPoints) {
                $numPoints = 2;
                $segment[1] = $segment[0];
            }

            for ($i = 1; $i < $numPoints; ++$i) {
                $x1 = $viewport->getRelativePositionForLongitude($segment[$lastPoint][1]);
                $y1 = $viewport->getRelativePositionForLatitude($segment[$lastPoint][0]);
                $x2 = $viewport->getRelativePositionForLongitude($segment[$i][1]);
                $y2 = $viewport->getRelativePositionForLatitude($segment[$i][0]);

                if ($this->LineSimplificationTolerance > 0.0 && $i != $numPoints - 1) {
                    if (sqrt(pow($x2 - $x1, 2.0) + pow($y2 - $y1, 2.0)) < $this->LineSimplificationTolerance) {
                        continue;
                    }
                }

                $this->truncateStartAndEndPointsBy(0.2, $x1, $y1, $x2, $y2);

                if ($this->Antialiasing && !function_exists('imageantialias')) {
                    $this->AntialiasDrawer->drawLine(
                        $image->getCore(),
                        $x1,
                        $y1,
                        $x2,
                        $y2,
                        $this->LineColor[0],
                        $this->LineColor[1],
                        $this->LineColor[2],
                        $this->LineColor[3],
                        $this->LineWidth
                    );
                } else {
                    $image->line($x1, $y1, $x2, $y2, $this->LineCallback);
                }

                $lastPoint = $i;
            }
        }
    }

    protected function truncateStartAndEndPointsBy(float $pixel, &$x1, &$y1, &$x2, &$y2)
    {
        if ($x1 == $x2) {
            if ($y2 > $y1) {
                $y1 += $pixel;
                $y2 -= $pixel;
            } else {
                $y1 -= $pixel;
                $y2 += $pixel;
            }
        } else {
            $gradient = ($y2 - $y1) / ($x2 - $x1);
            $deltaX = $pixel / sqrt(1.0 + $gradient * $gradient);
            $deltaY = $gradient * $deltaX;

            $x1 += $deltaX;
            $y1 += $deltaY;
            $x2 -= $deltaX;
            $y2 -= $deltaY;
        }
    }

    protected function getLineSegments(array $coordinates): array
    {
        if (empty($coordinates)) {
            return [];
        }

        if (2 == count($coordinates[0]) || !is_array($coordinates[0][0])) {
            return [$coordinates];
        }

        return $coordinates;
    }

    public function getBoundingBox(float $paddingInPercent = 0.0): BoundingBoxInterface
    {
        $minLatitude = 90.0;
        $maxLatitude = -90.0;
        $minLongitude = 180.0;
        $maxLongitude = -180.0;

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

        if ($paddingInPercent > 0.0) {
            $deltaLatitude = $maxLatitude - $minLatitude;
            $deltaLongitude = $maxLongitude - $minLongitude;

            $minLatitude -= $deltaLatitude * $paddingInPercent / 100.0;
            $maxLatitude += $deltaLatitude * $paddingInPercent / 100.0;
            $minLongitude -= $deltaLongitude * $paddingInPercent / 100.0;
            $maxLongitude += $deltaLongitude * $paddingInPercent / 100.0;
        }

        return new BoundingBox($minLatitude, $maxLatitude, $minLongitude, $maxLongitude);
    }

    public function isEmpty(): bool
    {
        return empty($this->LineSegments);
    }
}
