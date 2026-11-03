<?php declare(strict_types=1);

namespace Dravencms\Carousel;

use Dravencms\User\Acl\{AclPermission, AclProviderInterface};

final class AclProvider implements AclProviderInterface
{
    public function getPermissions(): iterable
    {
        yield new AclPermission('carousel', 'edit', 'carousel.acl.permissions.edit.label', resourceLabel: 'carousel.acl.resource');
        yield new AclPermission('carousel', 'delete', 'carousel.acl.permissions.delete.label', resourceLabel: 'carousel.acl.resource');
    }
}
