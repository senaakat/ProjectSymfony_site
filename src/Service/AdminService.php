<?php

namespace App\Service;

class AdminService
{
    public function getSidebarList(): array
    {
        return [
            ['icon' => 'bi bi-grid fs-3', 'title' => 'Kullanıcılar', 'submenu' => ['Kullanıcı İşlemleri']],
            ['icon' => 'bi bi-shop fs-3', 'title' => 'Ürünler', 'submenu' => ['Ürün Listesi', 'Ürün Ekle']],
            ['icon' => 'bi bi-layers fs-3', 'title' => 'Kategoriler', 'submenu' => ['Elbise', 'Etek']],
        ];
    }

}