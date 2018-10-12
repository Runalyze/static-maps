<?php

/*
 * This file is part of the StaticMaps.
 *
 * (c) RUNALYZE <mail@runalyze.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Runalyze\StaticMaps\Tests\Viewport;

use PHPUnit\Framework\TestCase;
use Runalyze\StaticMaps\Viewport\BoundingBox;

class BoundingBoxTest extends TestCase
{
    /**
     * @dataProvider getProviderForCenterCoordinates
     */
    public function testCenterCoordinates(float $minLatitude, float $minLongitude, float $maxLatitude, float $maxLongitude, float $expectedCenterLatitude, float $expectedCenterLongitude)
    {
        $boundingBox = new BoundingBox($minLatitude, $maxLatitude, $minLongitude, $maxLongitude);

        $this->assertThatBoundingBoxEquals([$minLatitude, $maxLatitude, $minLongitude, $maxLongitude], $boundingBox);
        $this->assertEquals($expectedCenterLatitude, $boundingBox->getCenterLatitude());
        $this->assertEquals($expectedCenterLongitude, $boundingBox->getCenterLongitude());
    }

    public function getProviderForCenterCoordinates()
    {
        return [
            [49.6, 7.6, 49.7, 7.7, 49.65, 7.65],
            [-90.0, -160.0, 90.0, 160.0, 0.0, -180.0],
            [-45.0, -160.0, -25.0, 60.0, -35.0, 130.0],
            [15.0, -160.0, 19.0, -60.0, 17.0, -110.0]
        ];
    }

    public function testExtendByZero()
    {
        $boundingBox = new BoundingBox(49.6, 7.6, 49.7, 7.7);

        $this->assertThatBoundingBoxEquals([49.6, 7.6, 49.7, 7.7], $boundingBox);
        $this->assertThatBoundingBoxEquals([49.6, 7.6, 49.7, 7.7], $boundingBox->extendBy(0.0));
        $this->assertThatBoundingBoxEquals([49.6, 7.6, 49.7, 7.7], $boundingBox->extendBy(0.0, 0.0, 0.0, 0.0));
    }

    protected function assertThatBoundingBoxEquals(array $expectedBoundaries, BoundingBox $boundingBox)
    {
        $this->assertEquals($expectedBoundaries[0], $boundingBox->getMinLatitude());
        $this->assertEquals($expectedBoundaries[1], $boundingBox->getMaxLatitude());
        $this->assertEquals($expectedBoundaries[2], $boundingBox->getMinLongitude());
        $this->assertEquals($expectedBoundaries[3], $boundingBox->getMaxLongitude());
    }
}
