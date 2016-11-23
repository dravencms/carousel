<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace App\Model\Carousel\Repository;

use App\Model\Carousel\Entities\Carousel;
use App\Model\Carousel\Entities\Item;
use Gedmo\Translatable\TranslatableListener;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\Models\ILocale;

/**
 * Class ItemRepository
 * @package App\Model\Carousel\Repository
 */
class ItemRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $itemRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * ItemRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->itemRepository = $entityManager->getRepository(Item::class);
    }

    /**
     * @param $id
     * @return mixed|null|Item
     */
    public function getOneById($id)
    {
        return $this->itemRepository->find($id);
    }

    /**
     * @param $id
     * @return Item[]
     */
    public function getById($id)
    {
        return $this->itemRepository->findBy(['id' => $id]);
    }

    /**
     * @param Carousel $carousel
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getItemQueryBuilder(Carousel $carousel)
    {
        $qb = $this->itemRepository->createQueryBuilder('i')
            ->select('i')
            ->where('i.carousel = :carousel')
            ->setParameter('carousel', $carousel);
        return $qb;
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param Carousel $carousel
     * @param Item|null $itemIgnore
     * @return boolean
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Carousel $carousel, Item $itemIgnore = null)
    {
        $qb = $this->itemRepository->createQueryBuilder('i')
            ->select('i')
            ->where('i.name = :name')
            ->andWhere('i.carousel = :carousel')
            ->setParameters([
                'name' => $name,
                'carousel' => $carousel,
            ]);

        if ($itemIgnore)
        {
            $qb->andWhere('i != :itemIgnore')
                ->setParameter('itemIgnore', $itemIgnore);
        }

        $query = $qb->getQuery();

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());

        return (is_null($query->getOneOrNullResult()));
    }

}