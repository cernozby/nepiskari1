<?php

namespace App\model;
use Exception;
use Nette\Application\LinkGenerator;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\Random;

/**
 * Class UserModel
 * @package App\model
 */
class AreaModel extends BaseModel
{
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'area';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function getAllForSelect() {
        $result = [];
        foreach ($this->getAll() as $item) {
            $result[$item['id']] = $item['name'];
        }

        return $result;
    }
}