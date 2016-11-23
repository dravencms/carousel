<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Carousel\Repository;

use App\Model\BaseRepository;
use Dravencms\Model\Carousel\Entities\Carousel;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

/**
 * Class CarouselRepository
 * @package App\Model\Carousel\Repository
 */
class CarouselRepository extends BaseRepository implements ICmsComponentRepository
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
     * @param $name
     * @param Carousel|null $carouselIgnore
     * @return boolean
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, Carousel $carouselIgnore = null)
    {
        $qb = $this->carouselRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($carouselIgnore)
        {
            $qb->andWhere('c != :carouselIgnore')
                ->setParameter('carouselIgnore', $carouselIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
                $return = [];
                /** @var Carousel $carousel */
                foreach ($this->carouselRepository->findBy(['isActive' => true]) AS $carousel) {
                    $return[] = new CmsActionOption($carousel->getName(), ['id' => $carousel->getId()]);
                }
                break;

            default:
                return false;
                break;
        }


        return $return;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        /** @var Carousel $found */
        $found = $this->findTranslatedOneBy($this->carouselRepository, $locale, $parameters + ['isActive' => true]);

        if ($found)
        {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }
}