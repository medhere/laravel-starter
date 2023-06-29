<?php

namespace App\Http\Helpers;


class AuthHelper
{

    public function allowRoles(...$roles){

        $role = auth()->check() ? auth()->user()->role : null;

        if($role === 'admin') return true;

        if($role === null || $role === '' || $roles === []) return false;

        if(collect($roles)->contains($role)) return true;
    }


    public function denyRoles(...$roles){

        $role = auth()->check() ? auth()->user()->role : null;

        if($role === 'admin') return true;

        if($role === null || $role === '' || $roles === []) return true;

        if(collect($roles)->contains($role)) return false;

    }

    public function onlyRoles(...$roles){

        $role = auth()->check() ? auth()->user()->role : null;

        if($role === null || $role === '' || $roles === []) return false;

        if(collect($roles)->contains($role)) return true;
    }


}
