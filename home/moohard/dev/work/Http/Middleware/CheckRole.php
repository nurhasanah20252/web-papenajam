<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string|array $roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $roles = is_string($roles) ? explode('|', $roles) : $roles;

        $roleValues = array_map(function ($role) {
            return $role instanceof UserRole ? $role->value : $role;
        }, $roles);

        if (!$request->user()->hasAnyRole($roleValues)) {
            abort(403, 'Unauthorized action. You do not have the required role.');
        }

        return $next($request);
    }
}
