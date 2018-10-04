# StaticMaps

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Library to create static images from various map tile providers.
StaticMaps will fetch map tiles from a specified tile provider and combine them to a static image of specified size for any bounding box.

StaticMaps requires [Intervention Image][link-image] for drawing and [League\Flysystem][link-flysystem] for tile caching.


## Install

Via Composer

``` bash
$ composer require runalyze/static-maps
```

## Usage

For a full list of required use statements, see [example-1.php](docs/examples/example-1.php):

``` php
$imageManager = new ImageManager(['driver' => 'gd']);
$tileService = new OpenStreetMap();
$tileCache = new FilesystemCache(new Filesystem(new Local(__DIR__.'/cache/tiles')), $imageManager);
$tileProvider = new TileProvider($tileService, $imageManager, $tileCache);

$map = new Map(new Viewport(500, 350, new BoundingBox(53.40, 53.75, 9.90, 10.10), $tileService));
$map->addFeature(new TileMap($tileProvider));
$map->addFeature(new CopyrightNotice($tileService->getAttributionText(), function($font){
    $font->file('./resources/font/Roboto-Regular.ttf');
}));

$provider = new Renderer($imageManager);
$image = $provider->renderMap($map);

echo $image->response('png');
```

![Example for static map][link-example-1]

It's also possible to use a complete route as base for the image, see [example-2.php](docs/examples/example-2.php):
``` php
$route = new Route([[53.57532, 10.01534], [52.520008, 13.404954], [48.13743, 11.57549]], '#ff5500', 5);

$map = new Map(new Viewport(300, 200, $route->getBoundingBox(), $tileService));
$map->addFeature(new TileMap($tileProvider);
$map->addFeature($route);

// ...
```

![Example for static map with route][link-example-2]


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/runalyze/static-maps.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/runalyze/static-maps/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/runalyze/static-maps.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/runalyze/static-maps.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/runalyze/static-maps.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/runalyze/static-maps
[link-travis]: https://travis-ci.org/runalyze/static-maps
[link-scrutinizer]: https://scrutinizer-ci.com/g/runalyze/static-maps/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/runalyze/static-maps
[link-downloads]: https://packagist.org/packages/runalyze/static-maps

[link-image]: https://github.com/Intervention/image
[link-flysystem]: https://github.com/thephpleague/flysystem

[link-example-1]: docs/examples/example-1.png
[link-example-2]: docs/examples/example-2.png
