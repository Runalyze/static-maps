<?php

/*
 * This file is part of the StaticMaps.
 *
 * (c) RUNALYZE <mail@runalyze.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once '../../vendor/autoload.php';

use Intervention\Image\ImageManager;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use Runalyze\StaticMaps\Cache\FilesystemCache;
use Runalyze\StaticMaps\Feature\CopyrightNotice;
use Runalyze\StaticMaps\Feature\Route;
use Runalyze\StaticMaps\Feature\TileMap;
use Runalyze\StaticMaps\Map\Map;
use Runalyze\StaticMaps\Renderer;
use Runalyze\StaticMaps\TileProvider;
use Runalyze\StaticMaps\TileService\OpenStreetMapDe;
use Runalyze\StaticMaps\Viewport\Viewport;

$imageManager = ImageManager::gd();
$tileService = new OpenStreetMapDe();
$tileCache = new FilesystemCache(new Filesystem(new LocalFilesystemAdapter(__DIR__.'/cache/tiles')), $imageManager);
$tileProvider = new TileProvider($tileService, $imageManager, new \GuzzleHttp\Client(), $tileCache);

$route = new Route([[53.57532, 10.01534], [52.520008, 13.404954], [48.13743, 11.57549]], '#ff5500', 5);
$viewport = new Viewport(500, 350, $route->getBoundingBox(), $tileService);

$map = new Map($viewport);
$map->addFeature(new TileMap($tileProvider));
$map->addFeature($route);
$map->addFeature(new CopyrightNotice($tileService->getAttributionText(), function (\Intervention\Image\Typography\FontFactory $font) {
    $font->file('../../resources/font/Roboto-Regular.ttf');
}));

$provider = new Renderer($imageManager);
$image = $provider->renderMap($map);

file_put_contents('example-2.png', $image->toPng());
