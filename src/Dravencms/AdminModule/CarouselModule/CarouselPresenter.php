<?php declare(strict_types=1);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\CarouselModule;

use Dravencms\AdminModule\Components\Carousel\CarouselForm\CarouselFormFactory;
use Dravencms\AdminModule\Components\Carousel\CarouselGrid\CarouselGridFactory;
use Dravencms\AdminModule\Components\Carousel\ItemForm\ItemFormFactory;
use Dravencms\AdminModule\Components\Carousel\ItemGrid\ItemGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\Item;
use Dravencms\Model\Carousel\Repository\CarouselRepository;
use Dravencms\Model\Carousel\Repository\ItemRepository;

/**
 * Description of GalleryPresenter
 *
 * @author sadam
 */
#[\Dravencms\User\Attributes\IsAllowed('carousel', 'edit')]
class CarouselPresenter extends SecuredPresenter
{
    /** @var CarouselRepository @inject */
    public $carouselRepository;

    /** @var ItemRepository @inject */
    public $itemRepository;

    /** @var CarouselFormFactory @inject */
    public $carouselFormFactory;

    /** @var CarouselGridFactory @inject */
    public $carouselGridFactory;

    /** @var ItemFormFactory @inject */
    public $itemFormFactory;

    /** @var ItemGridFactory @inject */
    public $itemGridFactory;

    /** @var null|Carousel */
    private $carousel = null;

    /** @var null|Item */
    private $item = null;

    #[\Dravencms\User\Attributes\IsAllowed('carousel', 'edit')]
    public function renderDefault()
    {
        $this->template->h1 = $this->translator->translate('carousel.admin.title.carousels');
    }

    #[\Dravencms\User\Attributes\IsAllowed('carousel', 'edit')]
    public function actionEdit($id)
    {
        if ($id) {
            $this->template->h1 = $this->translator->translate('carousel.admin.title.editCarousel');
            $carousel = $this->carouselRepository->getOneById($id);
            if (!$carousel) {
                $this->error();
            }

            $this->carousel = $carousel;
        } else {
            $this->template->h1 = $this->translator->translate('carousel.admin.title.newCarousel');
        }
    }

    /**
     * @param $id
     */
    #[\Dravencms\User\Attributes\IsAllowed('carousel', 'edit')]
    public function actionItems($id)
    {
        $this->carousel = $this->carouselRepository->getOneById($id);
        $this->template->carousel = $this->carousel;
        $this->template->h1 = $this->translator->translate('carousel.admin.title.items');
    }

    /**
     * @param $carouselId
     * @param null $itemId
     * @throws \Nette\Application\BadRequestException
     */
    #[\Dravencms\User\Attributes\IsAllowed('carousel', 'edit')]
    public function actionEditItem($carouselId, $itemId = null)
    {
        $this->carousel = $this->carouselRepository->getOneById($carouselId);
        if ($itemId)
        {
            $item = $this->itemRepository->getOneById($itemId);
            if (!$item) {
                $this->error();
            }

            $this->item = $item;
            $this->template->h1 = $this->translator->translate('carousel.admin.title.editItem');
        }
        else
        {
            $this->template->h1 = $this->translator->translate('carousel.admin.title.newItem');
        }
    }

    /**
     * @return \Dravencms\AdminModule\Components\Carousel\CarouselForm\CarouselForm
     */
    public function createComponentFormCarousel()
    {
        $control = $this->carouselFormFactory->create($this->carousel);
        $control->onSuccess[] = function()
        {
            $this->flashMessage($this->translator->translate('carousel.admin.flash.carouselSaved'), 'alert-success');
            $this->redirect('Carousel:');
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Carousel\ItemForm\ItemForm
     */
    public function createComponentFormItem()
    {
        $control = $this->itemFormFactory->create($this->carousel, $this->item);
        $control->onSuccess[] = function($item)
        {
            $this->flashMessage($this->translator->translate('carousel.admin.flash.itemSaved'), 'alert-success');
            $this->redirect('Carousel:items', $item->getCarousel()->getId());
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Carousel\CarouselGrid\CarouselGrid
     */
    public function createComponentGridCarousel()
    {
        $control = $this->carouselGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage($this->translator->translate('carousel.admin.flash.carouselDeleted'), 'alert-success');
            $this->redirect('Carousel:');
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Carousel\ItemGrid\ItemGrid
     */
    public function createComponentGridItem()
    {
        $control = $this->itemGridFactory->create($this->carousel);
        $control->onDelete[] = function()
        {
            $this->flashMessage($this->translator->translate('carousel.admin.flash.itemDeleted'), 'alert-success');
            $this->redirect('Carousel:items', $this->carousel->getId());
        };
        return $control;
    }
}
