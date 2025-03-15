<?php

namespace App\model;

use Nette\Application\LinkGenerator;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Security\User;
use Nette\Utils\Html;

/**
 * Class User
 **/
class Route extends BaseFactory
{

    /**
     * UserClass constructor.
     * @param Explorer $database
     * @param Container $container
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'route';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function getSector() {
        /** @var Sector $sector */
        $sector = $this->container->createInstance('App\model\Sector');
        $sector->initId($this->get('sector'));
        return $sector;
    }

    public function getArea() {
        $sector = $this->getSector();
        /** @var Area $area */
        $area = $this->container->createInstance('App\model\Area');
        $area->initId($sector->get('id_area'));

        return $area;
    }

    public function getTitleCard() {
        $sector = $this->getSector();
        $area = $this->getArea();

        return $area->get('name') .' - '. $sector->get('name') .' - '. $this->get('toverName');
    }

    public function getName() {
        return $this->get('routeName');
    }

    public function getGrade() {
        return $this->get('grade');
    }

    public function getShow(): bool
    {
        return boolval($this->get('show'));
    }

    public function changeShow() {
        $this->set('show', !$this->getShow());
    }

    public function getGradeAsInt() {
        $array = array_flip(RouteModel::$gradeToInt);
        if (key_exists($this->getGrade(), $array)) {
            return $array[$this->getGrade()];
        }
        throw new \Exception("Špatně zadáná klasa: " . $this->getGrade());
    }

    /**
     * @return Image[]
     */
    public function getImages() {

        /** @var ImageModel $imageModel */
        $imageModel = $this->container->createService('ImageModel');
        return $imageModel->getAllForRoute($this->getId());
    }

    public function delete() :void {
        foreach ($this->getImages() as $image) {
            $image->delete();
        }
        parent::delete();
    }

    public function getStyleFormated() {
        $div = Html::el('div');
        foreach ($this->getStyle() as $item) {
            $div->addHtml(
                Html::el('span')
                    ->addAttributes(['class' => 'badge badge-warning'])
                    ->setText($item)
            )->addHtml(' ');
        }

        return $div;
    }

    public function getStyle() {
        $array = json_decode($this->get('style'));

        if (!is_array($array)) {
            return [];
        }
        $result = [];
        foreach ($array as $item) {
            $result[] = RouteModel::$routeStyle[$item];
        }

        return $result;
    }

    public function hasSameStyle($style) {

        $result = [];
        foreach ($style as $item) {
            $result[] = RouteModel::$routeStyle[$item];
        }
        $myStyle = $this->getStyle();

        if (count($myStyle) === 0) {
            return false;
        }

        foreach ($result as $item) {
            if (!in_array($item, $myStyle)) {
                return false;
            }
        }

        return true;
    }

    public function getX() {
        return $this->get('gpsX');
    }

    public function getY() {
        return $this->get('gpsY');
    }

}