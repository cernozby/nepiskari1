<?php

namespace App\model;
use Exception;
use Nette\Application\LinkGenerator;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\Random;
use function Symfony\Component\String\b;

/**
 * Class UserModel
 * @package App\model
 */
class RouteModel extends BaseModel
{

    public static $remove_dia= array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'č' => 'c','Č' => 'C' );

    public static $gradeToInt = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
        5 => 'V', 6 => 'VI', 7 => 'VIIa', 8 => 'VIIb', 9 => 'VIIc',
        10 => 'VIIIa', 11 => 'VIIIb', 12 => "VIIIc"];

    public static $routeStyle = [1 => 'ruční spára', 2 => 'širočina', 3 => 'komín', 4 => 'rajbas', 5 => 'stěnovka'];

    public function __construct(Explorer $database, Container $container, LinkGenerator $linkGenerator) {
        $this->table = 'route';
        parent::__construct($database, $container, $linkGenerator);
    }

    public function getAllForTemplate(): ?array
    {
        $result = [];
        /** @var ActiveRow $item */
        foreach (parent::getAll() as $item) {
            /** @var Route $instance */
            $instance = $this->container->createService("route");
            $instance->initData($item);
            if ($instance->getShow()) {
                $result[] = $instance;
            }
        }

        return $result;
    }

    public function getRouteNameForSelect() {
        $select = $this->getTable()->select('id, toverName, routeName')->where('show = 1')->order('routeName')->fetchAll();
        $result = [];
        foreach ($select as $item) {
            $result[$item->id] = $item->routeName . ' - ' . $item->toverName;
        }
        return $result;
    }

    public function getAreaForSelect() {

        $select = $this->db->query('SELECT s.id, s.name as sector_name, a.name as area_name FROM sector as s
                              JOIN area as a 
                              ON s.id_area = a.id')->fetchAll();
        $result = [];
        foreach ($select as $item) {
            $result[$item->id] = $item->area_name . ' - ' . $item->sector_name;
        }
        return $result;
    }

    public function getTowerNameForSelect() {
        return $this->getTable()->select('toverName')->where('show = 1')->order('toverName')->group('toverName')->fetchPairs('toverName', 'toverName');
    }

    public function getAllWithGps() {
        $result = [];
        foreach ($this->getAll() as $item) {
            if ($item->gpsY != '0' || $item->gpsX != '0') {
                $instance = $this->container->createService("route");
                $instance->initData($item);
                $result[$instance->get('toverName')][] = $instance;
            }
        }

        return $result;
    }

    public function updateRoute($data) {
        /** @var Route $instance */
        $instance = $this->container->createService("route");
        $instance->initId($data['id']);
        $data['style'] = json_encode($data['style']);
        unset($data['image']);
        unset($data['id']);
        bdump($data);
        $instance->update($data);
    }

    public function newRoute($data) : void {
        /** @var FileUpload[] $files */
        $files = $data['image'];
        unset($data['image']);
        $data['style'] = json_encode($data['style']);
        $row = $this->getTable()->insert($data);
        /** @var ImageModel $instance */
        $instance = $this->container->createService("imageModel");
        $instance->newImage($files, $row->id);
    }

    public function getDistance($route) {
        $result = [];
        /** @var Route $instance */
        foreach ($this->getAllForTemplate() as $instance) {
            $result[$this->gps_distance($route->getX(), $route->getY(), $instance->getX(), $instance->getY()) * 1000][] = $instance  ;
        }

        ksort($result);

        return array_slice(array_merge(...$result), 1, 5, true);
    }

    /** Vzdálenost dvou zeměpisných bodů
     * @param float zeměpisná šířka prvního bodu ve stupních
     * @param float zeměpisná délka prvního bodu ve stupních
     * @param float zeměpisná šířka druhého bodu ve stupních
     * @param float zeměpisná délka druhého bodu ve stupních
     * @return float nejmenší vzdálenost bodů v kilometrech
     */
    private function gps_distance($lat1, $lng1, $lat2, $lng2) {
        static $great_circle_radius = 6372.795;
        return acos(
                cos(deg2rad($lat1))*cos(deg2rad($lng1))*cos(deg2rad($lat2))*cos(deg2rad($lng2))
                + cos(deg2rad($lat1))*sin(deg2rad($lng1))*cos(deg2rad($lat2))*sin(deg2rad($lng2))
                + sin(deg2rad($lat1))*sin(deg2rad($lat2))
            ) * $great_circle_radius;
    }
}