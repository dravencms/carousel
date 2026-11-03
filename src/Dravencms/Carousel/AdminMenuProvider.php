<?php declare(strict_types=1);

namespace Dravencms\Carousel;

use Dravencms\Admin\Menu\{AdminMenuItem, AdminMenuProviderInterface};

final class AdminMenuProvider implements AdminMenuProviderInterface
{
    public function getItems(): iterable
    {
        yield new AdminMenuItem('carousel.overview', 'carousel.menu.overview', destination: ':Admin:Carousel:Carousel:default', parent: 'content', icon: 'fa-exchange', order: 20, resource: 'carousel', privilege: 'edit', activeDestinations: [':Admin:Carousel:Item:*']);
    }
}
