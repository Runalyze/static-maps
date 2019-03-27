<?php

namespace Runalyze\StaticMaps\Drawer\Polygon;

use Runalyze\StaticMaps\Drawer\PainterAwareTrait;

class NativePolygonDrawer
{
    use PainterAwareTrait;

    /** @var bool */
    protected $IsNativeFunctionAvailable;

    /** @var array [polyline_1, polyline_2, ...] with each polyline [[x, y], [x, y], ...] */
    protected $Polylines = [];

    public function __construct()
    {
        $this->IsNativeFunctionAvailable = function_exists('imageantialias');
    }

    public function addPolygon(array $points)
    {
        $this->Polylines[] = array_map(function ($point) {
            return [(int)round($point[0]), (int)round($point[1])];
        }, $points);
    }

    public function drawPolygons($image)
    {
        $this->prepareNativeDrawing($image->getCore());

        $color = $this->allocateColor($image->getCore());
        foreach ($this->Polylines as $points) {
            $numPoints = count($points);

            $flattened = collect($points)->flatten()->toArray();
            $image->polygon($flattened, function ($draw) use ($color) {
                $draw->border(1, $color);
                $draw->background($color);
            });
        }
    }

    protected function prepareNativeDrawing($resource)
    {
        imagesetthickness($resource, $this->LineWidth);
        imagealphablending($resource, true);

        if ($this->IsNativeFunctionAvailable) {
            imageantialias($resource, true);
        }
    }
}
