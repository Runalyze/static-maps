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
        $this->assertEqualsWithDelta($expectedCenterLatitude, $boundingBox->getCenterLatitude(), 0.01);
        $this->assertEqualsWithDelta($expectedCenterLongitude, $boundingBox->getCenterLongitude(), 0.01);
    }

    public function getProviderForCenterCoordinates(): array
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

    protected function assertThatBoundingBoxEquals(array $expectedBoundaries, BoundingBox $boundingBox): void
    {
        $this->assertEqualsWithDelta($expectedBoundaries[0], $boundingBox->getMinLatitude(), 0.01);
        $this->assertEqualsWithDelta($expectedBoundaries[1], $boundingBox->getMaxLatitude(), 0.01);
        $this->assertEqualsWithDelta($expectedBoundaries[2], $boundingBox->getMinLongitude(), 0.01);
        $this->assertEqualsWithDelta($expectedBoundaries[3], $boundingBox->getMaxLongitude(), 0.01);
    }
}
