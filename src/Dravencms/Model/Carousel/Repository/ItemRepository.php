<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Carousel\Repository;

use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\Item;
use Kdyby\Doctrine\EntityManager;
use Nette;

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
     * @return null|Item
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
     * @param $identifier
     * @param Carousel $carousel
     * @param Item|null $itemIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree($identifier, Carousel $carousel, Item $itemIgnore = null)
    {
        $qb = $this->itemRepository->createQueryBuilder('i')
            ->select('i')
            ->where('i.identifier = :identifier')
            ->andWhere('i.carousel = :carousel')
            ->setParameters([
                'identifier' => $identifier,
                'carousel' => $carousel,
            ]);

        if ($itemIgnore)
        {
            $qb->andWhere('i != :itemIgnore')
                ->setParameter('itemIgnore', $itemIgnore);
        }

        $query = $qb->getQuery();

        return (is_null($query->getOneOrNullResult()));
    }

}