<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Carousel\Repository;

use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Structure\Bridge\CmsLocale\Locale;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\CmsActionOptionTranslation;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;

/**
 * Class CarouselRepository
 * @package App\Model\Carousel\Repository
 */
class CarouselCmsRepository implements ICmsComponentRepository
{
    /** @var CarouselRepository */
    private $carouselRepository;

    /** @var CarouselTranslationRepository */
    private $carouselTranslationRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /**
     * CarouselCmsRepository constructor.
     * @param CarouselRepository $carouselRepository
     * @param CarouselTranslationRepository $carouselTranslationRepository
     * @param CurrentLocale $currentLocale
     */
    public function __construct(
        CarouselRepository $carouselRepository,
        CarouselTranslationRepository $carouselTranslationRepository,
        CurrentLocale $currentLocale
    )
    {
        $this->carouselRepository = $carouselRepository;
        $this->carouselTranslationRepository = $carouselTranslationRepository;
        $this->currentLocale = $currentLocale;
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
                    $return[] = new CmsActionOption($carousel->getIdentifier(), ['id' => $carousel->getId()]);
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
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters)
    {
        /** @var Carousel $found */
        $found = $this->carouselRepository->getOneByParameters($parameters + ['isActive' => true]);

        if ($found)
        {
            $cmsActionOption = new CmsActionOption($found->getIdentifier(), $parameters);

            foreach($found->getTranslations() AS $carouselTranslation)
            {
                $cmsActionOption->addTranslation(new CmsActionOptionTranslation(
                    new Locale($carouselTranslation->getLocale()),
                    $carouselTranslation->getName(),
                    $carouselTranslation->getName(),
                    $carouselTranslation->getName(),
                    $carouselTranslation->getName()
                ));
            }

            return $cmsActionOption;
        }

        return null;
    }
}