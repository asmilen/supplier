<?php

namespace App\Models;

use Route;

class Permission
{
    public function all()
    {
        $results = [];

        foreach (Route::getRoutes() as $route) {
            $results[] = $this->filterRoute($route);
        }

        return array_filter(array_sort($results, function ($value) {
            return $value['uri'];
        }));
    }

    protected function filterRoute($route)
    {
        if (! in_array('acl', array_values($route->middleware()))) {
            return;
        }

        $result = [
            'uri'    => $route->uri(),
            'name'   => $route->getName(),
            'action' => $route->getActionName(),
        ];

        if ($result['action'] == 'Closure' || is_null($result['name'])) {
            return;
        }

        return array_merge($result, ['controller' => explode('.', $result['name'])[0]]);
    }
}