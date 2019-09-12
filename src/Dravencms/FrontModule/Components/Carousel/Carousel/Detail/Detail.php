<?php

namespace Dravencms\FrontModule\Components\Carousel\Carousel\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Carousel\Repository\CarouselRepository;
use Dravencms\Model\Carousel\Repository\ItemTranslationRepository;
use Salamek\Cms\ICmsActionOption;

/**
 * Class Detail
 * @package FrontModule\Components\Carousel\Carousel
 */
class Detail extends BaseControl
{
    /** @var CarouselRepository */
    public $carouselRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var ItemTranslationRepository */
    private $itemTranslationRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param CarouselRepository $carouselRepository
     * @param ItemTranslationRepository $itemTranslationRepository
     * @param CurrentLocale $currentLocale
     */
    public function __construct(
        ICmsActionOption $cmsActionOption,
        CarouselRepository $carouselRepository,
        ItemTranslationRepository $itemTranslationRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->carouselRepository = $carouselRepository;
        $this->itemTranslationRepository = $itemTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }

    public function render()
    {
        $template = $this->template;

        $carousel = $this->carouselRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));

        $template->carousel = $carousel;

        $translatedItems = [];

        foreach($carousel->getItems() AS $item)
        {
            $translatedItems[] = $this->itemTranslationRepository->getTranslation($item, $this->currentLocale);
        }

        $template->translatedItems = $translatedItems;

        $template->setFile($this->cmsActionOption->getTemplatePath(__DIR__ . '/detail.latte'));
        $template->render();
    }
}
