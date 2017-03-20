<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Carousel\Repository;

use Dravencms\Model\Carousel\Entities\Carousel;
use Kdyby\Doctrine\EntityManager;
use Nette;

/**
 * Class CarouselRepository
 * @package App\Model\Carousel\Repository
 */
class CarouselRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $carouselRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * CarouselRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->carouselRepository = $entityManager->getRepository(Carousel::class);
    }

    /**
     * @param $id
     * @return mixed|null|Carousel
     */
    public function getOneById($id)
    {
        return $this->carouselRepository->find($id);
    }

    /**
     * @param integer $id
     * @param bool $isActive
     * @return mixed|null|Carousel
     */
    public function getOneByIdAndActive($id, $isActive = true)
    {
        return $this->carouselRepository->findOneBy(['id' => $id, 'isActive' => $isActive]);
    }

    /**
     * @param $id
     * @return Carousel[]
     */
    public function getById($id)
    {
        return $this->carouselRepository->findBy(['id' => $id]);
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getCarouselQueryBuilder()
    {
        $qb = $this->carouselRepository->createQueryBuilder('c')
            ->select('c');
        return $qb;
    }

    /**
     * @return Carousel[]
     */
    public function getActive()
    {
        return $this->carouselRepository->findBy(['isActive' => true]);
    }

    /**
     * @param $identifier
     * @param Carousel|null $carouselIgnore
     * @return boolean
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree($identifier, Carousel $carouselIgnore = null)
    {
        $qb = $this->carouselRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.identifier = :identifier')
            ->setParameters([
                'identifier' => $identifier
            ]);

        if ($carouselIgnore)
        {
            $qb->andWhere('c != :carouselIgnore')
                ->setParameter('carouselIgnore', $carouselIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param array $parameters
     * @return mixed|null|object
     */
    public function getOneByParameters(array $parameters)
    {
        return $this->carouselRepository->findOneBy($parameters);
    }
}