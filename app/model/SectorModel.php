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
class SectorModel extends BaseModel
{
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'sector';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function getAllForSelect($admin = false) {
        $result = [];
        foreach ($this->getAll() as $item) {
            if ($admin || $this->db->table('route')
                    ->select('*')
                    ->where('sector = ?', $item['id'])
                    ->where('show = 1')
                    ->count() > 0)
            {
                $result[$item['id']] = $item['name'];
            }
        }
        return $result;
    }
}