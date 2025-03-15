<?php

namespace App\PublicModule\Presenters;

use App\model\Constants;
use App\model\Image;
use App\model\Route;
use App\model\RouteModel;
use App\model\ViewModel;
use App\Presenters\BasePresenter;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

Class HomepagePresenter extends BasePresenter {

    /** @var RouteModel @inject */
    public $routeModel;

    /** @var ViewModel @inject */
    public $viewModel;

    public function actionDefault() {
        $this->viewModel->addView();
    }

    public function actionStarts() {}

    public function actionRoutes() {
        $this->template->routes = $this->routeModel->getAllForTemplate();
    }

    public function actionDetail($idRoute) {
        /** @var Route $instance */
        $instance = $this->container->createService("route");
        $instance->initId($idRoute);
        $this->template->route = $instance;
        $this->template->routeAround = $this->routeModel->getDistance($instance);

        $this->template->keywords = $instance->get('routeName') . '(' . $instance->get('toverName')
                                                                      . ' - '
                                                                      . $instance->getSector()->get('name')
                                                                      . ' - '
                                                                      . $instance->getArea()->get('name')
                                                                      . ')'
                                                                      . 'pískovcové lezení pro začátečníky - nepiskari.cz';
        $this->template->description = '';
    }

    public function actionMap() {
        $this->template->routeWithGps = $this->routeModel->getAllWithGps();
    }

  /* ================ handles =======================*/
  /**
   * @return Form
   */
  public function handleLogin(): void {
    $this->template->loginForm = true;
  }
  
  public function handleRegistration() : void {
    $this->template->registrationForm = true;
  }

  public function createComponentRoutesGrid() {
      $grid = new DataGrid();
      $grid->setDataSource($this->routeModel->getAllForTemplate());
      $grid->setColumnReset(false);
      $grid->setOuterFilterRendering();
      $grid->setAutoSubmit(false);
      $grid->setTemplateFile(__DIR__ . '/../templates/tableRoutes.latte');

      $grid->addColumnText('area', 'oblast')->setRenderer(function (Route $item) {
          return $item->getSector()->getArea()->get('name');
      })
          ->setSortable()
          ->setSortableCallback(function ($array, $order) {
              $direction = $order['area'] == 'ASC' ? 1 : -1;
              usort($array, function (Route $first, Route $second) use ($direction) {
                  return $direction * strcmp(
                      strtr($first->getArea()->get('name'), RouteModel::$remove_dia),
                      strtr($second->getArea()->get('name'), RouteModel::$remove_dia)
                      );
              });
              return $array;
          });
      $grid->addColumnText('sector', 'obvod')->setRenderer(function (Route $item) {
          return $item->getSector()->get('name');
      })
          ->setSortable()
          ->setSortableCallback(function ($array, $order) {
              $direction = $order['sector'] == 'ASC' ? 1 : -1;
              usort($array, function (Route $first, Route $second) use ($direction) {
                  return $direction * strcmp(
                      strtr($first->getSector()->get('name'), RouteModel::$remove_dia),
                      strtr($second->getSector()->get('name'), RouteModel::$remove_dia));
              });
              return $array;
          });
      $grid->addColumnText('toverName', 'Název věže')->setRenderer(function (Route $item) {
          return $item->get('toverName');
      })
          ->setSortable()
          ->setSortableCallback(function ($array, $order) {
              $direction = $order['toverName'] == 'ASC' ? 1 : -1;
              usort($array, function (Route $first, Route $second) use ($direction) {
                  return $direction * strcmp(
                      strtr($first->get('toverName'), RouteModel::$remove_dia),
                      strtr($second->get('toverName'), RouteModel::$remove_dia));
              });
              return $array;
          });

      $grid->addColumnText('routeName', 'Název cesty')->setRenderer(function (Route $item) {
          return $item->get('routeName');
      })
          ->setSortable()
          ->setSortableCallback(function ($array, $order) {
              $direction = $order['routeName'] == 'ASC' ? 1 : -1;
              usort($array, function (Route $first, Route $second) use ($direction) {
                  return $direction * strcasecmp(
                      strtr($first->get('routeName'), RouteModel::$remove_dia),
                      strtr($second->get('routeName'), RouteModel::$remove_dia)
                      );
              });
              return $array;
          });

      $grid->addColumnText('grade', 'Obtížnost')->setRenderer(function (Route $item) {
          return $item->get('grade');
      })
          ->setSortable()
          ->setSortableCallback(function ($array, $order) {
              $direction = $order['grade'] == 'ASC' ? 1 : -1;
              usort($array, function (Route $first, Route $second) use ($direction) {
                  return $direction * ($first->getGradeAsInt() > $second->getGradeAsInt() ? 1 : -1);
              });
              return $array;
          });

      $grid->addColumnText('style', 'Styl')->setRenderer(function (Route $item) {

          $div = Html::el('div');
          $data = json_decode($item->get('style'));

          if (!is_array($data)) {
              return '-';
          }
          foreach ($data as $item) {
              $div->addHtml(
                  Html::el('span')->addAttributes(['class' => 'badge badge-warning mr-1'])->setText(RouteModel::$routeStyle[$item]));
          }
          return $div;
      });

      $grid->addColumnText('action', 'akce')->setRenderer(function (Route $item) {
          $link = $this->link('Homepage:detail', ['idRoute' => $item->getId()]);
          $a = Html::el('a');
          $a->addAttributes(['class' => 'btn-sm btn-warning', 'href' => $link])->setText('detail');
          return $a;
      });

      $grid->addFilterSelect('area', 'oblast', [0 => '---'] + $this->areaModel->getAllForSelect())
           ->setCondition(function ($data, $filter) {
                /**
                 * @var  $key
                 * @var Route $item
                 */
                foreach ($data as $key => $item) {
                    if ($filter && $item->getArea()->getId() != $filter) {
                        unset($data[$key]);
                    }
                }
              return $data;
           });

      $grid->addFilterSelect('sector', 'obvod', [0 => '---'] + $this->sectorModel->getAllForSelect())
          ->setCondition(function ($data, $filter) {
              /**
               * @var  $key
               * @var Route $item
               */
              foreach ($data as $key => $item) {
                  if ($filter && $item->getSector()->getId() != $filter) {
                      unset($data[$key]);
                  }
              }
              return $data;
          });


      $grid->addFilterSelect('toverName', 'věž', [0 => '---'] + $this->routeModel->getTowerNameForSelect())
          ->setCondition(function ($data, $filter) {
              /**
               * @var  $key
               * @var Route $item
               */
              foreach ($data as $key => $item) {
                  if ($filter && $item->get('toverName') != $filter) {
                      unset($data[$key]);
                  }
              }
              return $data;
          });

      $grid->addFilterSelect('routeName', 'cesta', [0 => '---'] + $this->routeModel->getRouteNameForSelect())
          ->setCondition(function ($data, $filter) {
              /**
               * @var  $key
               * @var Route $item
               */
              foreach ($data as $key => $item) {
                  if ($filter && $item->getId() != $filter) {
                      unset($data[$key]);
                  }
              }
              return $data;
          });

      $grid->addFilterRange('grade', 'obtížnost')->setCondition(function ($data, $filter) {
          /**
           * @var int $key
           * @var  Route $item
           */
          foreach ($data as $key => $item) {
              if (
                  (!empty($filter['from']) && $item->getGradeAsInt() < array_flip(RouteModel::$gradeToInt)[$filter['from']]) ||
                  (!empty($filter['to']) && $item->getGradeAsInt() > array_flip(RouteModel::$gradeToInt)[$filter['to']])
              ) {
                  unset($data[$key]);
              };
          }
          return $data;
      });

      $grid->addFilterMultiSelect('style', 'style', [0 => '---'] +  RouteModel::$routeStyle)
          ->setCondition(function ($data, $filter) {
              /**
               * @var  $key
               * @var Route $item
               */
              foreach ($data as $key => $item) {
                  if ($filter && (count($filter) > 1 || $filter[0] != 0)  && !$item->hasSameStyle($filter)) {
                      unset($data[$key]);
                  }
              }
              return $data;
          });



      $translator = new SimpleTranslator([
          'ublaboo_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
          'ublaboo_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
          'ublaboo_datagrid.here' => 'zde',
          'ublaboo_datagrid.items' => 'Položky',
          'ublaboo_datagrid.all' => 'všechny',
          'ublaboo_datagrid.from' => 'z',
          'ublaboo_datagrid.reset_filter' => 'Resetovat filtr',
          'ublaboo_datagrid.group_actions' => 'Hromadné akce',
          'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
          'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
          'ublaboo_datagrid.action' => 'Akce',
          'ublaboo_datagrid.previous' => 'Předchozí',
          'ublaboo_datagrid.next' => 'Další',
          'ublaboo_datagrid.choose' => 'Vyberte',
          'ublaboo_datagrid.execute' => 'Provést',
          'ublaboo_datagrid.filter' => 'Filtrovat',
          'ublaboo_datagrid.change' => 'Změnit',
          'ublaboo_datagrid.show_filter' => 'Zobrazit filtr',

          'Name' => 'Jméno',
          'Inserted' => 'Vloženo'
      ]);

      $grid->setTranslator($translator);
      return $grid;
  }

}
