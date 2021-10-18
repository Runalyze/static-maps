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
use Runalyze\StaticMaps\TileService\TileServiceInterface;
use Runalyze\StaticMaps\Viewport\BoundingBox;
use Runalyze\StaticMaps\Viewport\BoundingBoxInterface;
use Runalyze\StaticMaps\Viewport\Viewport;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class ViewportTest extends TestCase
{
    protected function getTileServiceMock()
    {
        $tileService = $this->createMock(TileServiceInterface::class);
        $tileService->method('getTileSize')->will($this->returnValue(256));
        $tileService->method('getMinZoom')->will($this->returnValue(0));
        $tileService->method('getMaxZoom')->will($this->returnValue(20));

        return $tileService;
    }

    public function testSimpleProjection()
    {
        $viewport = new Viewport(800, 600, $this->createMock(BoundingBoxInterface::class), $this->getTileServiceMock());

        $this->assertEquals(800, $viewport->getWidth());
        $this->assertEquals(600, $viewport->getHeight());
    }

    public function testKTownFittingTask()
    {
        $viewport = new Viewport(500, 350, new BoundingBox(49.42, 49.46, 7.66, 7.76), $this->getTileServiceMock());

        $this->assertEquals(12, $viewport->getZoom());
        $this->assertEquals(2134, $viewport->getStartX());
        $this->assertEquals(2136, $viewport->getEndX());
        $this->assertEquals(1398, $viewport->getStartY());
        $this->assertEquals(1399, $viewport->getEndY());

        $this->assertEqualsWithDelta(49.3826, $viewport->getTileFittingBoundingBox()->getMinLatitude(), 0.0001);
        $this->assertEqualsWithDelta(49.4967, $viewport->getTileFittingBoundingBox()->getMaxLatitude(), 0.0001);
        $this->assertEqualsWithDelta(7.5586, $viewport->getTileFittingBoundingBox()->getMinLongitude(), 0.0001);
        $this->assertEqualsWithDelta(7.8219, $viewport->getTileFittingBoundingBox()->getMaxLongitude(), 0.0001);

        $this->assertThatBoundingBoxesHaveTheSameCenter($viewport, $viewport->getBoundingBox(), $viewport->getPromisedBoundingBox());
        $this->assertThatTileBoundingBoxCoversOtherBoundingBox($viewport->getBoundingBox(), $viewport->getPromisedBoundingBox());
        $this->assertThatTileBoundingBoxCoversOtherBoundingBox($viewport->getTileFittingBoundingBox(), $viewport->getBoundingBox());
        $this->assertThatTileOffsetsAreInValidRange($viewport);
    }

    public function testGermanyFittingTask()
    {
        $tileService = $this->createMock(TileServiceInterface::class);
        $tileService->method('getTileSize')->will($this->returnValue(256));
        $tileService->method('getMinZoom')->will($this->returnValue(0));
        $tileService->method('getMaxZoom')->will($this->returnValue(20));

        $viewport = new Viewport(800, 1200, new BoundingBox(47.40724, 54.9079, 5.98815, 14.98853), $tileService);

        $this->assertEquals(6, $viewport->getZoom());
        $this->assertEquals(32, $viewport->getStartX());
        $this->assertEquals(35, $viewport->getEndX());
        $this->assertEquals(19, $viewport->getStartY());
        $this->assertEquals(23, $viewport->getEndY());

        $this->assertEqualsWithDelta(40.9965, $viewport->getTileFittingBoundingBox()->getMinLatitude(), 0.0001);
        $this->assertEqualsWithDelta(58.8137, $viewport->getTileFittingBoundingBox()->getMaxLatitude(), 0.0001);
        $this->assertEqualsWithDelta(0.0, $viewport->getTileFittingBoundingBox()->getMinLongitude(), 0.0001);
        $this->assertEqualsWithDelta(22.4780, $viewport->getTileFittingBoundingBox()->getMaxLongitude(), 0.0001);

        $this->assertThatBoundingBoxesHaveTheSameCenter($viewport, $viewport->getBoundingBox(), $viewport->getPromisedBoundingBox());
        $this->assertThatTileBoundingBoxCoversOtherBoundingBox($viewport->getBoundingBox(), $viewport->getPromisedBoundingBox());
        $this->assertThatTileBoundingBoxCoversOtherBoundingBox($viewport->getTileFittingBoundingBox(), $viewport->getBoundingBox());
        $this->assertThatTileOffsetsAreInValidRange($viewport);
    }

    protected function assertThatTileBoundingBoxCoversOtherBoundingBox(BoundingBoxInterface $outerBoundingBox, BoundingBoxInterface $innerBoundingBox)
    {
        $this->assertLessThanOrEqual($innerBoundingBox->getMinLatitude(), $outerBoundingBox->getMinLatitude());
        $this->assertGreaterThanOrEqual($innerBoundingBox->getMaxLatitude(), $outerBoundingBox->getMaxLatitude());
        $this->assertLessThanOrEqual($innerBoundingBox->getMinLongitude(), $outerBoundingBox->getMinLongitude());
        $this->assertGreaterThanOrEqual($innerBoundingBox->getMaxLongitude(), $outerBoundingBox->getMaxLongitude());
    }

    protected function assertThatBoundingBoxesHaveTheSameCenter(ViewportInterface $viewport, BoundingBoxInterface $outerBoundingBox, BoundingBoxInterface $innerBoundingBox)
    {
        $innerCenterX = ($viewport->longitudeToTileX($innerBoundingBox->getMinLongitude(), $viewport->getZoom()) + $viewport->longitudeToTileX($innerBoundingBox->getMaxLongitude(), $viewport->getZoom())) / 2.0;
        $innerCenterY = ($viewport->latitudeToTileY($innerBoundingBox->getMinLatitude(), $viewport->getZoom()) + $viewport->latitudeToTileY($innerBoundingBox->getMaxLatitude(), $viewport->getZoom())) / 2.0;
        $outerCenterX = ($viewport->longitudeToTileX($outerBoundingBox->getMinLongitude(), $viewport->getZoom()) + $viewport->longitudeToTileX($outerBoundingBox->getMaxLongitude(), $viewport->getZoom())) / 2.0;
        $outerCenterY = ($viewport->latitudeToTileY($outerBoundingBox->getMinLatitude(), $viewport->getZoom()) + $viewport->latitudeToTileY($outerBoundingBox->getMaxLatitude(), $viewport->getZoom())) / 2.0;

        $this->assertEqualsWithDelta($innerCenterX, $outerCenterX, 0.005 * ($innerCenterX + $outerCenterX) / 2.0);
        $this->assertEqualsWithDelta($innerCenterY, $outerCenterY, 0.005 * ($innerCenterY + $outerCenterY) / 2.0);
    }

    protected function assertThatTileOffsetsAreInValidRange($viewport)
    {
        $this->assertGreaterThanOrEqual(0, $viewport->getTileOffsetX());
        $this->assertLessThanOrEqual(256, $viewport->getTileOffsetX());
        $this->assertGreaterThanOrEqual(0, $viewport->getTileOffsetY());
        $this->assertLessThanOrEqual(256, $viewport->getTileOffsetY());
    }
}
