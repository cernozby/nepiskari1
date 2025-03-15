<?php

namespace App\SysModule\Presenters;

use App\components\formRoute\formRouteFactory;
use App\model\Image;
use App\model\ImageModel;
use App\model\Route;
use App\model\RouteModel;
use App\Presenters\BasePresenter;
use App\components\formPoint\formPointFactory;
use Latte\Helpers;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

Class RoutePresenter extends BasePresenter
{

    /**  @var formRouteFactory @inject*/
    public $formRouteFactory;

    /** @var Route @inject */
    public $route;

    public $idRoute = null;


    public function startup() : void {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('Nedostatečná oprávnění');
            $this->redirect(':Public:Homepage:default');
        }

        parent::startup();
    }

    public function actionDefault($idRoute = null) {
        $this->idRoute = $idRoute;
        $this->template->idImage= null;
        $this->template->textImage= '';
        $this->template->showAddImage = false;
        $this->template->showImageText = false;

        $this->template->idRoute = -1;
    }

    public function handleShowAddImage($id) {
        $this->template->showAddImage = true;
        $this->template->idRoute = $id;
    }

    public function handleShowImageText($id, $text) {
        $this->template->showImageText = true;
        $this->template->idImage= $id;
        $this->template->textImage= $text;
    }

    public function handleChangeVisibility($id) {
        /** @var Route $route */
        $route = $this->container->createService('Route');
        $route->initId($id);
        $route->changeShow();
    }


    public function createComponentRouteForm() {
        return $this->formRouteFactory->create($this->idRoute);
    }

    public function createComponentAddImageForm($idRoute) {
        $form = new Form();
        $form->addHidden("idRoute");
        $form->addMultiUpload('image');
        $form->addSubmit('send','uložit');
        $form->onSuccess[] = function ($form, array $values) {
            /** @var ImageModel $imageModel */
            $imageModel = $this->container->createService("ImageModel");
            $imageModel->newImage($values['image'], $values['idRoute']);
        };

        return $form;
    }

    public function createComponentTextImageForm() {
        $form = new Form();
        $form->addHidden("idImage");
        $form->addText('text', 'popis obrazku');
        $form->addSubmit('send', 'uložit');

        $form->onSuccess[] = function ($form, array $values) {
            /** @var Image $image */
            $image = $this->container->createService("Image");
            $image->initId($values['idImage']);
            $image->update(['text' => $values['text']]);
        };

        return $form;



    }


    public function createComponentRouteGrid()
    {
        $grid = new DataGrid();
        $grid->setDataSource($this->routeModel->getAll());
        $grid->setOuterFilterRendering();
        $grid->setAutoSubmit(false);
        $grid->setTemplateFile(__DIR__ . '/../templates/tableRoutes.latte');


        $grid->addColumnText('action', 'akce')->setRenderer(function ($row) {


            $addPictureLink = Html::el('a')
                ->addAttributes(['href' => $this->link('showAddImage!', ['id' => $row->id]), 'class' => 'ajax'])
                ->addText('přidat_foto');

            $deleteLink = Html::el('a')
                ->addAttributes(['href' => $this->link('deleteItem!', ['type' => 'route', 'id' =>  $row->id])])
                ->addText('smazat_cestu');

            $editLink = Html::el('a')
                ->addAttributes(['href' => $this->link('Route:default', $row->id)])
                ->addText('editovat_cestu');

            $showLink = Html::el('a')
                ->addAttributes(['href' => $this->link('changeVisibility!', $row->id)])
                ->addText('zmenit_viditelnost');

            return Html::el('span')
                ->addHtml($addPictureLink)
                ->addHtml(Html::el('br'))
                ->addHtml($deleteLink)
                ->addHtml(Html::el('br'))
                ->addHtml($editLink)
                ->addHtml(Html::el('br'))
                ->addHtml($showLink);
        });

        $grid->addColumnText('show', 'zveřejněno')->setRenderer(function ($row) {
            /** @var Route $route */
            $route = $this->container->createService('Route');
            $route->initData($row);

            if ($route->getShow()) {
                return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->setText('ANO');
            }

            return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->setText('NE');
        });
        $grid->addColumnText('sector', 'Sektor')->setRenderer(function ($row) {
            /** @var Route $route */
            $route = $this->container->createService('Route');
            $route->initData($row);

            return $route->getArea()->get('name') . ' - ' .$route->getSector()->get('name');
        });
        $grid->addColumnText('toverName', 'Název věže');
        $grid->addColumnText('routeName', 'název cesty');
        $grid->addColumnText('grade', 'Obtížnost');
        $grid->addColumnText('style', 'Styl')->setRenderer(function ($row) {

            $div = Html::el('div');
            $data = json_decode($row->style);

            if (!is_array($data)) {
                return '-';
            }
            foreach ($data as $item) {
                $div->addHtml(
                    Html::el('span')->addAttributes(['class' => 'badge badge-primary'])->setText(RouteModel::$routeStyle[$item]));
            }
            return $div;
        });
        $grid->addColumnText('description', 'popis')->setRenderer(function ($row) {
            return $row->description;
        });
        $grid->addColumnText('image', 'Obrazek')->setRenderer(function ($row) {
            $divMain = Html::el('div');
            foreach ($this->imageModel->getAllForRoute($row->id) as $item) {
                $divInside = Html::el('div');
                $img = Html::el('img');
                $img->addAttributes([
                    'src' => "../photo/{$item->get('path')}",
                    'width' => '100px',
                    'title' => $item->get('text') ? $item->get('text') : "--"
                ]);

                $deleteLink = $this->link('deleteItem!', ['type' => 'image', 'id' =>  $item->id]);
                $link = Html::el('a');
                $link->addText("smazat");
                $link->addAttributes(['href' => $deleteLink]);


                $textLink = $this->link('showImageText!', ['id' => $item->id, 'text' => $item->get('text')]);
                $link2 = Html::el('a');
                $link2->addText("přidat text");
                $link2->addAttributes(['href' => $textLink]);

                $divInside->addHtml($img);
                $divInside->addHtml($link);
                $divInside->addHtml($link2);
                $divMain->addHtml($divInside);
            }

            return $divMain;
        });

;


        $grid->addFilterSelect('show', 'zveřejněno', [-1 => '---', 1 => 'Ne', 0 => 'Ano'])
            ->setCondition(function ($data, $filter) {
                foreach ($data as $key => $item) {
                    if ($item->show == $filter) {
                        unset($data[$key]);
                    }
                }
                return $data;
            });
        $grid->addFilterSelect('area', 'oblast', [0 => '---'] + $this->areaModel->getAllForSelect())
            ->setCondition(function ($data, $filter) {
                /**
                 * @var  $key
                 * @var Route $item
                 */

                foreach ($data as $key => $item) {
                    $route = $this->route->initId($item->id);
                    if ($filter && $route->getArea()->get('id') != $filter) {
                        unset($data[$key]);
                    }
                }
                return $data;
            });
        return $grid;

    }


    public function handleLogout() : void {
        $this->user->logout();
        $this->flashMessage('Byl jste úspešně odhlášen');
        $this->redirect(':Public:Homepage:default');
    }


}
