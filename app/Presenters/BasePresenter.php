<?php

namespace App\Presenters;
use App\components\formLogin\formLogin;
use App\components\formRegistration\formRegistration;
use App\model\AreaModel;
use App\model\ImageModel;
use App\model\PointModel;
use App\model\RouteModel;
use App\model\SectorModel;
use App\model\UserModel;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

abstract class BasePresenter extends Presenter {
  
  
  /**
   * @var UserModel
   */
  
  /**
   * @var Container
   */
  public $container;
  
  /**
   * @var UserModel
   */
  public $userModel;

  /** @var PointModel */
  public $pointModel;

  /** @var RouteModel */
  public $routeModel;

  /** @var ImageModel */
  public $imageModel;

  /** @var AreaModel */
  public $areaModel;

    /** @var SectorModel */
    public $sectorModel;
  
  
  public function __construct(
      Container $container,
      UserModel $userModel,
      PointModel $pointModel,
      RouteModel $routeModel,
      ImageModel $imageModel,
      AreaModel $areaModel,
      SectorModel $sectorModel
  ) {
    $this->container = $container;
    $this->userModel = $userModel;
    $this->pointModel = $pointModel;
    $this->routeModel = $routeModel;
    $this->imageModel = $imageModel;
    $this->areaModel = $areaModel;
    $this->sectorModel = $sectorModel;
    parent::__construct();
  }

  
  public function handleDeleteItem($type, $id) {

    try {
      $instance = $this->container->createService($type);
      $instance->initId($id);
      $instance->delete();
    } catch (Exception $e) {
      $this->flashMessage('Něco se pokazilo. Zkuste obnovit stránku', 'danger');
    }
    $this->flashMessage('Úspěšně smazáno', 'success');
    $this->redirect('this');
  }
  
  public function createComponentFormLoginControl(): formLogin {
    return new formLogin($this->presenter, $this->container, $this->user);
  }

  public function createComponentFormRegistrationControl(): formRegistration {
    return new formRegistration($this->presenter, $this->container, $this->user);
  }
}
