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

}