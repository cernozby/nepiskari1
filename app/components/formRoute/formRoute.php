<?php

namespace App\components\formRoute;

use App\components\BaseComponent;
use App\model\AreaModel;
use App\model\Constants;
use App\model\PointModel;
use App\model\Route;
use App\model\RouteModel;
use App\model\SectorModel;
use Nette\Application\AbortException;
use \Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;


class formRoute extends \Nette\Application\UI\Control  {

    private $idRoute;
    public function render() : void {
        $this->template->setFile(__DIR__ . '/formRoute.latte');
        $this->template->render();
    }

    public function __construct($idRoute) {
        $this->idRoute = $idRoute;
    }

    /**
     * @return Form
     */
    public function createComponentForm() : Form {
        /**
         * @var AreaModel $areaModel
         */
        $areaModel = $this->getPresenter()->container->createService('AreaModel');


        /**
         * @var SectorModel $sectorModel
         */
        $sectorModel = $this->getPresenter()->container->createService('SectorModel');

        $form = new Form();
        $form->addHidden('id', $this->idRoute);
        $form->addSelect('sector', 'obvod', $sectorModel->getAllForSelect(true));
        $form->addText('toverName', 'věž');
        $form->addText('routeName', 'cesta');
        $form->addText('grade', 'obtížnost');
        $form->addText('gpsX', 'gpsX');
        $form->addText('gpsY', 'gpsY');
        $form->addMultiSelect('style', 'styl', RouteModel::$routeStyle);
        $form->addTextArea('description', 'popis', 100, 10);
        $form->addMultiUpload('image');
        $form->addSubmit('submit', 'Odeslat');

        if ($this->idRoute) {
            /** @var Route $route */
            $route = $this->getPresenter()->container->createService('Route');
            $route->initId($this->idRoute);
            $data = $route->getData()->toArray();
            $data['style'] = json_decode($data['style']);
            $form->setDefaults($data);
        }
        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws AbortException
     */
    public function formSucceeded(Form $form, ArrayHash $values) : void {
        /** @var RouteModel $routeModel */
        $routeModel = $this->getPresenter()->container->createService('RouteModel');
        if ($values->offsetGet('id')) {
         $routeModel->updateRoute($values);
        } else {
            $routeModel->newRoute($values);
        }
        $this->getPresenter()->redirect('this');
    }
}
