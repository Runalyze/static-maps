<?php

/*
 * This file is part of the StaticMaps.
 *
 * (c) RUNALYZE <mail@runalyze.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Runalyze\StaticMaps\Tests\TileService;

use PHPUnit\Framework\TestCase;
use Runalyze\StaticMaps\Tile\Tile;
use Runalyze\StaticMaps\TileService\AbstractTemplateBasedTileService;

class AbstractTemplateBasedTileServiceTest extends TestCase
{
    protected function getDummyTile(): Tile
    {
        return new Tile(8544, 5598, 14);
    }

    public function testTileUrl()
    {
        /** @var AbstractTemplateBasedTileService $mock */
        $mock = $this->getMockForAbstractClass(AbstractTemplateBasedTileService::class);

        $this->assertEquals('https://b.sub.domain.tld/dir/14/8544/5598.png', $mock->getTileUrl($this->getDummyTile(), 'b'));
        $this->assertEquals('https://c.sub.domain.tld/dir/14/8544/5598@2x.png', $mock->getTileUrl($this->getDummyTile(), 'c', '@2x'));
    }

    public function testTileUrlWithRandomSubdomain()
    {
        $service = new FakeTileService();

        $this->assertRegExp('#^https://[abc].domain.tld/14/8544/5598.png$#', $service->getTileUrl($this->getDummyTile()));
    }
}
