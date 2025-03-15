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
class ImageModel extends BaseModel
{
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'image';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function newImage($files, $idRoute) {
        foreach ($files as $file) {
            if ($file->isOk()) {
                $image = Image::fromFile($file->getTemporaryFile());
                $fileName = Random::generate(10) . '.jpg';
                $filePath = './../www/photo/' . $fileName;
                FileSystem::write($filePath, $image->toString());
                $this->db->table($this->table)->insert(['id_route' => $idRoute,  'path' => $fileName]);
            }
        }
    }

    public function getAllForRoute($idRoute) {
        $data = $this->getTable()->select('*')->where('id_route = ?', $idRoute)->fetchAll();

        $results = [];
        foreach ($data as $item) {
            /** @var \App\model\Image $instance */
            $instance = $this->container->createService("Image");
            $results[] = $instance->initId($item->id);
        }
        return $results;
    }

}