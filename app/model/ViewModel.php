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
 * Class ViewModel
 * @package App\model
 */
class ViewModel extends BaseModel
{
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'view';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function addView() {
        $this->getTable()->insert(['date' => time(), 'ip' => $_SERVER['REMOTE_ADDR']]);
    }

    public function getViewForDays($days = 3650) {
        return count($this->db->query('SELECT ip FROM view WHERE date > ? ', time() - ($days * 86400))->fetchAll());
    }

    public function getUniqIpsForDays($days = 3650) {
        return count($this->db->query('SELECT DISTINCT ip FROM view WHERE date > ? ', time() - ($days * 86400))->fetchAll());
    }

}