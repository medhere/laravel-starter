<?php

namespace App\Http\Middleware;

use App\Http\Helpers\AuthHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    //'admin', 'user', 'staff', 'medical'

    public function handle(Request $request, Closure $next, $role): Response
    {      
        
        $roles = new AuthHelper;
        
        switch ($role) {
            case 'all':
                if($roles->allowRoles('admin','staff','medical')) return $next($request);
                break;
            case 'admin':
                if($roles->allowRoles('admin')) return $next($request);            
                break;
            case 'staff':
                if($roles->allowRoles('staff')) return $next($request);                    
                break;
            case 'medical':
                if($roles->allowRoles('medical')) return $next($request);            
                break;
            case 'user':
                if($roles->allowRoles('user')) return $next($request);            
                break;
                                        
            default:
                return back();
                break;
        }

    }
}
