<?php

namespace App\components\formRoute;

class formRouteFactory {
    function create ($idRoute) : FormRoute {
        return new formRoute($idRoute);
    }
}