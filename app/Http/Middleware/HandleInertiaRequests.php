<?php

namespace App\Http\Middleware;

use App\Enums\MenuLocation;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'menus' => [
                'header' => $this->getMenuTree(MenuLocation::Header, $user),
                'footer' => $this->getMenuTree(MenuLocation::Footer, $user),
                'mobile' => $this->getMenuTree(MenuLocation::Mobile, $user),
            ],
        ];
    }

    /**
     * Get menu tree for a specific location.
     */
    protected function getMenuTree(MenuLocation $location, ?\App\Models\User $user = null): array
    {
        $menu = Menu::byLocation($location)->first();

        if (! $menu) {
            return [];
        }

        return $menu->getTree(true, $user);
    }
}
