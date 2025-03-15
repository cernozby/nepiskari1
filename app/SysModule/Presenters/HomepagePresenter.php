<?php

namespace App\SysModule\Presenters;

use App\components\formRoute\formRouteFactory;
use App\model\ViewModel;
use App\Presenters\BasePresenter;
use App\components\formPoint\formPointFactory;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

Class HomepagePresenter extends BasePresenter
{
    /** @var formPointFactory @inject */
    public $formPointFactory;

    /**  @var formRouteFactory @inject*/
    public $formRouteFactory;

    /** @var ViewModel @inject */
    public $viewModel;

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
      $this->template->ipOneDay = $this->viewModel->getUniqIpsForDays(1);
      $this->template->ipThreeDay = $this->viewModel->getUniqIpsForDays(3);
      $this->template->ipWeekDay = $this->viewModel->getUniqIpsForDays(7);
      $this->template->ipMonthDay = $this->viewModel->getUniqIpsForDays(28);
      $this->template->ipAllDay = $this->viewModel->getUniqIpsForDays();

      $this->template->viewOneDay = $this->viewModel->getViewForDays(1);
      $this->template->viewThreeDay = $this->viewModel->getViewForDays(3);
      $this->template->viewWeekDay = $this->viewModel->getViewForDays(7);
      $this->template->viewMonthDay = $this->viewModel->getViewForDays(28);
      $this->template->viewAllDay = $this->viewModel->getViewForDays();





  }

  public function createComponentPointForm() {
      return $this->formPointFactory->create();
  }

    public function createComponentPointGrid()
    {
        $grid = new DataGrid();
        $grid->setDataSource($this->pointModel->getAll());
        $grid->addColumnText('latX', 'lat X');
        $grid->addColumnText('latY', 'lat Y');
        $grid->addColumnText('image', 'Obrazek');

        return $grid;
    }




    public function handleLogout() : void {
    $this->user->logout();
    $this->flashMessage('Byl jste úspešně odhlášen');
    $this->redirect(':Public:Homepage:default');
  }


}
