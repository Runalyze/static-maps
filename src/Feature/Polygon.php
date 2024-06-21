<?php

namespace Runalyze\StaticMaps\Feature;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Runalyze\StaticMaps\Viewport\ViewportInterface;
use Runalyze\StaticMaps\Drawer\Polygon\NativePolygonDrawer;
use Runalyze\StaticMaps\Drawer\Polyline\NativePolylineDrawer;

class Polygon extends \Runalyze\StaticMaps\Feature\Route
{
    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport)
    {
        $drawer = new NativePolygonDrawer();
        $drawer->setPainter(
            $this->LineColor[0],
            $this->LineColor[1],
            $this->LineColor[2],
            $this->LineColor[3],
            $this->LineWidth
        );

        foreach ($this->LineSegments as $segment) {
            $points = $this->getPoints($viewport, $segment);

            $drawer->addPolygon($points);
        }

        $drawer->drawPolygons($image);
    }

    /**
     * Converts coordinates into relative x,y pixel points for drawing as a polygon
     *
     * @param  ViewportInterface $viewport
     * @param  array $segment
     * @return array
     */
    private function getPoints(ViewportInterface $viewport, iterable $segment)
    {
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

            if (0.0 < $this->LineSimplificationTolerance
                && $i !== $numPoints - 1
                && sqrt(pow($x2 - $x1, 2.0) + pow($y2 - $y1, 2.0)) < $this->LineSimplificationTolerance) {
                continue;
            }

            $points[] = [$x2, $y2];
            $lastPoint = $i;
        }

        return $points;
    }
}
