<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Carousel\Repository;

use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\CarouselTranslation;
use Dravencms\Model\Locale\Entities\ILocale;
use Kdyby\Doctrine\EntityManager;
use Nette;

/**
 * Class CarouselTranslationRepository
 * @package App\Model\Carousel\Repository
 */
class CarouselTranslationRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $carouselTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * CarouselRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->carouselTranslationRepository = $entityManager->getRepository(CarouselTranslation::class);
    }

    /**
     * @param $identifier
     * @param ILocale $locale
     * @param Carousel|null $carouselIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($identifier, ILocale $locale, Carousel $carouselIgnore = null)
    {
        $qb = $this->carouselTranslationRepository->createQueryBuilder('ct')
            ->select('ct')
            ->where('ct.identifier = :identifier')
            ->andWhere('ct.locale = :locale')
            ->join('ct.carousel', 'c')
            ->setParameters([
                'identifier' => $identifier,
                'locale' => $locale
            ]);

        if ($carouselIgnore)
        {
            $qb->andWhere('c != :carouselIgnore')
                ->setParameter('carouselIgnore', $carouselIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param Carousel $carousel
     * @param ILocale $locale
     * @return mixed|null|object
     */
    public function getTranslation(Carousel $carousel, ILocale $locale)
    {
        return $this->carouselTranslationRepository->findOneBy(['carousel' => $carousel, 'locale' => $locale]);
    }
}