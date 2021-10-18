<?php

/*
 * This file is part of the StaticMaps.
 *
 * (c) RUNALYZE <mail@runalyze.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Runalyze\StaticMaps\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Runalyze\StaticMaps\Feature\Route;
use Runalyze\StaticMaps\Viewport\BoundingBox;
use Runalyze\StaticMaps\Viewport\BoundingBoxInterface;

class RouteTest extends TestCase
{
    public function testEmptyRoute()
    {
        $route = new Route([]);

        $this->assertTrue($route->isEmpty());
    }

    public function testSimpleBoundingBox()
    {
        $route = new Route([
            [53.57532, 10.01534],
            [52.520008, 13.404954],
            [48.13743, 11.57549]
        ]);

        $this->assertFalse($route->isEmpty());
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(48.13743, 53.57532, 10.01534, 13.404954), $route->getBoundingBox());
    }

    public function testBoundingBoxWithPadding()
    {
        $route = new Route([
            [50.0, 10.0],
            [51.0, 10.1]
        ]);

        $this->assertFalse($route->isEmpty());
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(50.000, 51.000, 10.000, 10.100), $route->getBoundingBox());
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(49.888, 51.110, 9.989, 10.111), $route->getBoundingBox(10.0));
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(50.000, 51.110, 9.900, 10.100), $route->getBoundingBox(10.0, 0.0, 0.0, 50.0));
    }

    protected function assertThatBoundingBoxIsEqual(BoundingBoxInterface $expected, BoundingBoxInterface $actual)
    {
        $this->assertEqualsWithDelta($expected->getMinLatitude(), $actual->getMinLatitude(), 0.001);
        $this->assertEqualsWithDelta($expected->getMaxLatitude(), $actual->getMaxLatitude(), 0.001);
        $this->assertEqualsWithDelta($expected->getMinLongitude(), $actual->getMinLongitude(), 0.001);
        $this->assertEqualsWithDelta($expected->getMaxLongitude(), $actual->getMaxLongitude(), 0.001);
    }

    /**
     * @see https://github.com/Runalyze/static-maps/issues/3
     */
    public function testExampleWithBreaksInCoordinates()
    {
        $route = new Route([
            [
                [51.034692, 13.791008],
                [51.034692, 13.791008]
            ],
            [
                [51.034708, 13.790972],
                [51.034720, 13.790943],
                [51.034733, 13.790913],
                [51.034747, 13.790882]
            ]
        ]);

        $this->assertFalse($route->isEmpty());
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(51.034, 51.035, 13.790, 13.791), $route->getBoundingBox());
    }
}
