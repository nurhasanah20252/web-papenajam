<?php

namespace Database\Seeders;

use App\Enums\MenuLocation;
use App\Enums\UrlType;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Header Menu
        $headerMenu = Menu::firstOrCreate(
            ['location' => MenuLocation::Header],
            [
                'name' => 'Header Menu',
                'location' => MenuLocation::Header,
                'max_depth' => 3,
                'description' => 'Main navigation menu displayed in the header',
            ]
        );

        // Create Header Menu Items
        $this->createHeaderMenuItems($headerMenu);

        // Create Footer Menu
        $footerMenu = Menu::firstOrCreate(
            ['location' => MenuLocation::Footer],
            [
                'name' => 'Footer Menu',
                'location' => MenuLocation::Footer,
                'max_depth' => 2,
                'description' => 'Footer navigation menu',
            ]
        );

        // Create Footer Menu Items
        $this->createFooterMenuItems($footerMenu);

        // Create Mobile Menu
        $mobileMenu = Menu::firstOrCreate(
            ['location' => MenuLocation::Mobile],
            [
                'name' => 'Mobile Menu',
                'location' => MenuLocation::Mobile,
                'max_depth' => 3,
                'description' => 'Mobile navigation menu',
            ]
        );

        // Copy header items to mobile menu
        $this->copyMenuItems($headerMenu, $mobileMenu);
    }

    /**
     * Create header menu items.
     */
    protected function createHeaderMenuItems(Menu $menu): void
    {
        $items = [
            [
                'title' => 'Beranda',
                'url_type' => UrlType::Custom,
                'custom_url' => '/',
                'order' => 1,
            ],
            [
                'title' => 'Profil',
                'url_type' => UrlType::Custom,
                'custom_url' => '/about',
                'order' => 2,
                'children' => [
                    ['title' => 'Sejarah', 'custom_url' => '/about#sejarah', 'order' => 1],
                    ['title' => 'Visi & Misi', 'custom_url' => '/about#visi-misi', 'order' => 2],
                    ['title' => 'Struktur Organisasi', 'custom_url' => '/about#struktur', 'order' => 3],
                    ['title' => 'Kepaniteraan', 'custom_url' => '/about#kepaniteraan', 'order' => 4],
                    ['title' => 'Kesekretariatan', 'custom_url' => '/about#kesekretariatan', 'order' => 5],
                ],
            ],
            [
                'title' => 'Layanan',
                'url_type' => UrlType::Custom,
                'custom_url' => '/services',
                'order' => 3,
            ],
            [
                'title' => 'Berita',
                'url_type' => UrlType::Custom,
                'custom_url' => '/news',
                'order' => 4,
            ],
            [
                'title' => 'Jadwal Sidang',
                'url_type' => UrlType::Custom,
                'custom_url' => '/schedules',
                'order' => 5,
            ],
            [
                'title' => 'Dokumen',
                'url_type' => UrlType::Custom,
                'custom_url' => '/documents',
                'order' => 6,
            ],
            [
                'title' => 'Kontak',
                'url_type' => UrlType::Custom,
                'custom_url' => '/contact',
                'order' => 7,
            ],
        ];

        foreach ($items as $itemData) {
            $children = $itemData['children'] ?? null;
            unset($itemData['children']);

            $menuItem = MenuItem::firstOrCreate(
                [
                    'menu_id' => $menu->id,
                    'title' => $itemData['title'],
                ],
                array_merge($itemData, ['menu_id' => $menu->id, 'is_active' => true])
            );

            if ($children) {
                foreach ($children as $childData) {
                    MenuItem::firstOrCreate(
                        [
                            'menu_id' => $menu->id,
                            'parent_id' => $menuItem->id,
                            'title' => $childData['title'],
                        ],
                        array_merge($childData, [
                            'menu_id' => $menu->id,
                            'parent_id' => $menuItem->id,
                            'url_type' => UrlType::Custom,
                            'is_active' => true,
                        ])
                    );
                }
            }
        }
    }

    /**
     * Create footer menu items.
     */
    protected function createFooterMenuItems(Menu $menu): void
    {
        $items = [
            [
                'title' => 'Tentang Kami',
                'url_type' => UrlType::Custom,
                'custom_url' => '/about',
                'order' => 1,
            ],
            [
                'title' => 'Layanan',
                'url_type' => UrlType::Custom,
                'custom_url' => '/services',
                'order' => 2,
            ],
            [
                'title' => 'Berita',
                'url_type' => UrlType::Custom,
                'custom_url' => '/news',
                'order' => 3,
            ],
            [
                'title' => 'Kontak',
                'url_type' => UrlType::Custom,
                'custom_url' => '/contact',
                'order' => 4,
            ],
        ];

        foreach ($items as $itemData) {
            MenuItem::firstOrCreate(
                [
                    'menu_id' => $menu->id,
                    'title' => $itemData['title'],
                ],
                array_merge($itemData, ['menu_id' => $menu->id, 'is_active' => true])
            );
        }
    }

    /**
     * Copy menu items from one menu to another.
     */
    protected function copyMenuItems(Menu $source, Menu $target): void
    {
        $sourceItems = MenuItem::where('menu_id', $source->id)
            ->whereNull('parent_id')
            ->get();

        foreach ($sourceItems as $sourceItem) {
            $targetItem = MenuItem::firstOrCreate(
                [
                    'menu_id' => $target->id,
                    'title' => $sourceItem->title,
                ],
                [
                    'menu_id' => $target->id,
                    'title' => $sourceItem->title,
                    'url_type' => $sourceItem->url_type,
                    'route_name' => $sourceItem->route_name,
                    'page_id' => $sourceItem->page_id,
                    'custom_url' => $sourceItem->custom_url,
                    'icon' => $sourceItem->icon,
                    'order' => $sourceItem->order,
                    'target_blank' => $sourceItem->target_blank,
                    'is_active' => $sourceItem->is_active,
                ]
            );

            // Copy children
            foreach ($sourceItem->children as $sourceChild) {
                MenuItem::firstOrCreate(
                    [
                        'menu_id' => $target->id,
                        'parent_id' => $targetItem->id,
                        'title' => $sourceChild->title,
                    ],
                    [
                        'menu_id' => $target->id,
                        'parent_id' => $targetItem->id,
                        'title' => $sourceChild->title,
                        'url_type' => $sourceChild->url_type,
                        'route_name' => $sourceChild->route_name,
                        'page_id' => $sourceChild->page_id,
                        'custom_url' => $sourceChild->custom_url,
                        'icon' => $sourceChild->icon,
                        'order' => $sourceChild->order,
                        'target_blank' => $sourceChild->target_blank,
                        'is_active' => $sourceChild->is_active,
                    ]
                );
            }
        }
    }
}
