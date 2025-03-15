<?php

namespace App\components\formPoint;

use App\components\BaseComponent;
use App\model\Constants;
use App\model\PointModel;
use Nette\Application\AbortException;
use \Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;


class formPoint extends \Nette\Application\UI\Control  {
    public function render() : void {
        $this->template->setFile(__DIR__ . '/formPoint.latte');
        $this->template->render();
    }

    /**
     * @return Form
     */
    public function createComponentPointForm() : Form {
        $form = new Form();
        $form->addText('latX', 'gps X');
        $form->addText('latY', 'gps Y');
        $form->addUpload("image");

        $form->addSubmit('submit', 'Odeslat');
        $form->onSuccess[] = [$this, 'pointFormSucceeded'];
        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws AbortException
     */
    public function pointFormSucceeded(Form $form, ArrayHash $values) : void {
        /** @var PointModel $pointModel */
        $pointModel = $this->getPresenter()->container->createService('PointModel');
        $pointModel->newPoint($values);
        $this->getPresenter()->redirect('this');
    }
}
