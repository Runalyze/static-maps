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
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(50.0, 51.0, 10.0, 10.1), $route->getBoundingBox());
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(49.9, 51.1, 9.99, 10.11), $route->getBoundingBox(10.0));
        $this->assertThatBoundingBoxIsEqual(new BoundingBox(50.0, 51.1, 9.95, 10.1), $route->getBoundingBox(10.0, 0.0, 0.0, 50.0));
    }

    protected function assertThatBoundingBoxIsEqual(BoundingBoxInterface $expected, BoundingBoxInterface $actual)
    {
        $this->assertEquals($expected->getMinLatitude(), $actual->getMinLatitude());
        $this->assertEquals($expected->getMaxLatitude(), $actual->getMaxLatitude());
        $this->assertEquals($expected->getMinLongitude(), $actual->getMinLongitude());
        $this->assertEquals($expected->getMaxLongitude(), $actual->getMaxLongitude());
    }
}
