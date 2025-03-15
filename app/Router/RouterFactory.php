<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->withModule('Sys')
               ->addRoute('administrace', 'Homepage:default')
               ->addRoute('administrace/cesty[/<idRoute>]', 'Route:default');
        $router->withModule('Public')
               ->addRoute('registrace', 'Registration:registration')
               ->addRoute('prihlaseni', 'Registration:login')
               ->addRoute('zacatky', 'Homepage:starts')
               ->addRoute('cesty', 'Homepage:routes')
               ->addRoute('detail/<idRoute>', 'Homepage:detail')
               ->addRoute('mapa', 'Homepage:map')
               ->addRoute('admin', 'Homepage:admin')
               ->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
