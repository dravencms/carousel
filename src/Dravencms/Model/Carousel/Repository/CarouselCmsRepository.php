<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Carousel\Repository;

use Dravencms\Locale\TLocalizedRepository;
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
class CarouselCmsRepository implements ICmsComponentRepository
{
    /** @var CarouselRepository */
    private $carouselRepository;

    /**
     * CarouselCmsRepository constructor.
     * @param CarouselRepository $carouselRepository
     */
    public function __construct(CarouselRepository $carouselRepository)
    {
        $this->carouselRepository = $carouselRepository;
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
                foreach ($this->carouselRepository->getActive() AS $carousel) {
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
        $found = $this->carouselRepository->findTranslatedOneBy($this->carouselRepository, $locale, $parameters + ['isActive' => true]);

        if ($found)
        {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }
}