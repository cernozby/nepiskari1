<?php

namespace App\model;

use Nette\Application\LinkGenerator;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Security\User;
use Nette\Utils\FileSystem;

/**
 * Class User
 **/
class Image extends BaseFactory
{

    /**
     * UserClass constructor.
     * @param Explorer $database
     * @param Container $container
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'image';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function delete() : void{
        FileSystem::delete('./../www/photo/' . $this->get('path'));
        parent::delete();
    }


}