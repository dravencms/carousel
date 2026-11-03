<?php declare(strict_types=1);
namespace Dravencms\Carousel\Access;
use Salamek\Files\Access\{IFileAccessProvider, FileAccess};
use Dravencms\Model\Carousel\Repository\ItemRepository;
use Nette\Security\User;

final class ItemFileProvider implements IFileAccessProvider
{
    public function __construct(private ItemRepository $repository, private User $user) {}
    public static function getId(): string { return 'carousel.item'; }
    public function resolve(string $reference): ?FileAccess
    {
        $id = filter_var($reference, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || !($entity = $this->repository->getOneById($id))) { return null; }
        $public = $entity->isActive() && $entity->getCarousel()->isActive();
        if (!$public && !$this->user->isAllowed('carousel', 'edit')) { return null; }
        $attachment = $entity->getStructureFile();
        return $attachment ? new FileAccess($attachment->getFile(), $public, $attachment->getBasename()) : null;
    }
}
