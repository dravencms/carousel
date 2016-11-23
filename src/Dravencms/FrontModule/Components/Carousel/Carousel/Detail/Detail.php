<?php

namespace Dravencms\FrontModule\Components\Carousel\Carousel\Detail;

use Dravencms\Components\BaseControl;
use Dravencms\Model\Carousel\Repository\CarouselRepository;
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

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param CarouselRepository $carouselRepository
     */
    public function __construct(ICmsActionOption $cmsActionOption, CarouselRepository $carouselRepository)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->carouselRepository = $carouselRepository;
    }

    public function render()
    {
        $template = $this->template;

        $carousel = $this->carouselRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));

        $template->carousel = $carousel;

        $template->setFile(__DIR__ . '/detail.latte');
        $template->render();
    }
}
