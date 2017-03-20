<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Carousel\Repository;

use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\Item;
use Dravencms\Model\Carousel\Entities\ItemTranslation;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Dravencms\Model\Locale\Entities\ILocale;

/**
 * Class ItemTranslationRepository
 * @package App\Model\Carousel\Repository
 */
class ItemTranslationRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $itemTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * ItemRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->itemTranslationRepository = $entityManager->getRepository(ItemTranslation::class);
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
        $qb = $this->itemTranslationRepository->createQueryBuilder('it')
            ->select('it')
            ->join('it.item', 'i')
            ->where('it.name = :name')
            ->andWhere('i.carousel = :carousel')
            ->andWhere('it.locale = :locale')
            ->setParameters([
                'name' => $name,
                'carousel' => $carousel,
                'locale' => $locale
            ]);

        if ($itemIgnore)
        {
            $qb->andWhere('i != :itemIgnore')
                ->setParameter('itemIgnore', $itemIgnore);
        }

        $query = $qb->getQuery();

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param Item $item
     * @param ILocale $locale
     * @return null|ItemTranslation
     */
    public function getTranslation(Item $item, ILocale $locale)
    {
        return $this->itemTranslationRepository->findOneBy(['item' => $item, 'locale' => $locale]);
    }
}