<?php

namespace App\Twig;

use App\Service\AdminService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_sidebar_list', [$this, 'getSidebarList']),
        ];
    }

    public function getSidebarList(): array
    {
        return $this->adminService->getSidebarList();
    }
}