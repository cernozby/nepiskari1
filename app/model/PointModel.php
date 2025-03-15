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
class PointModel extends BaseModel
{
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'point';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function newPoint($data) : void {
        /** @var FileUpload $file */
        $file = $data['image'];
        $image = Image::fromFile($file->getTemporaryFile());

        $filePath ='./../www/photo/' . Random::generate(10) . '.jpg';
        FileSystem::write($filePath, $image->toString());

        $data['image'] = $filePath;
        $this->db->table($this->table)->insert($data);
    }
}