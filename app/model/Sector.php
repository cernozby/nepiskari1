<?php

namespace App\model;

use Nette\Application\LinkGenerator;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * Class User
 **/
class Sector extends BaseFactory
{

    /**
     * UserClass constructor.
     * @param Explorer $database
     * @param Container $container
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'sector';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function getArea(): Area {
        /** @var Area $area */
        $area = $this->container->createService('area');
        $area->initId($this->get('id_area'));
        return $area;
    }


}