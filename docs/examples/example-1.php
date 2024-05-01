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
use Runalyze\StaticMaps\Cache\FilesystemCache;
use Runalyze\StaticMaps\Feature\CopyrightNotice;
use Runalyze\StaticMaps\Feature\TileMap;
use Runalyze\StaticMaps\Map\Map;
use Runalyze\StaticMaps\Renderer;
use Runalyze\StaticMaps\TileProvider;
use Runalyze\StaticMaps\TileService\OpenStreetMap;
use Runalyze\StaticMaps\Viewport\BoundingBox;
use Runalyze\StaticMaps\Viewport\Viewport;
use Intervention\Image\Drivers\Gd\Driver;
use League\Flysystem\Local\LocalFilesystemAdapter;


$imageManager = new ImageManager(new Driver());
$tileService = new \Runalyze\StaticMaps\TileService\OpenTopoMap();
$localeFsAdapter = new LocalFilesystemAdapter(__DIR__.'/cache/tiles');
$localFs = new League\Flysystem\Filesystem($localeFsAdapter);
$tileCache = new FilesystemCache($localFs, $imageManager);
$tileProvider = new TileProvider($tileService, $imageManager, $tileCache);

$viewport = new Viewport(500, 350, new BoundingBox(53.40, 53.75, 9.90, 10.10), $tileService);

$map = new Map($viewport);
$map->addFeature(new TileMap($tileProvider));
$map->addFeature(new CopyrightNotice($tileService->getAttributionText(), function (\Intervention\Image\Typography\FontFactory $font) {
    $font->file('../../resources/font/Roboto-Regular.ttf');
}));

$provider = new Renderer($imageManager);
$image = $provider->renderMap($map);

    //echo $image->encode(new \Intervention\Image\Encoders\PngEncoder());
$localFs->write('foo.png', $image->encode(new \Intervention\Image\Encoders\PngEncoder()));
