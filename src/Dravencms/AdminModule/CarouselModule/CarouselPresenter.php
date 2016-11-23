<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\CarouselModule;

use Dravencms\AdminModule\Components\Carousel\CarouselFormFactory;
use Dravencms\AdminModule\Components\Carousel\CarouselGridFactory;
use Dravencms\AdminModule\Components\Carousel\ItemFormFactory;
use Dravencms\AdminModule\Components\Carousel\ItemGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use App\Model\Carousel\Entities\Carousel;
use App\Model\Carousel\Entities\Item;
use App\Model\Carousel\Repository\CarouselRepository;
use App\Model\Carousel\Repository\ItemRepository;

/**
 * Description of GalleryPresenter
 *
 * @author sadam
 */
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

    public function renderDefault()
    {
        $this->template->h1 = 'Carousels';
    }

    public function actionEdit($id)
    {
        if ($id) {
            $this->template->h1 = 'Edit carousel';
            $carousel = $this->carouselRepository->getOneById($id);
            if (!$carousel) {
                $this->error();
            }

            $this->carousel = $carousel;
        } else {
            $this->template->h1 = 'New carousel';
        }
    }

    /**
     * @param $id
     */
    public function actionItems($id)
    {
        $this->carousel = $this->carouselRepository->getOneById($id);
        $this->template->carousel = $this->carousel;
        $this->template->h1 = 'Carousel items';
    }

    /**
     * @param $carouselId
     * @param null $itemId
     * @throws \Nette\Application\BadRequestException
     */
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
            $this->template->h1 = 'Edit carousel item';
        }
        else
        {
            $this->template->h1 = 'New carousel item';
        }
    }

    /**
     * @return \AdminModule\Components\Carousel\CarouselForm
     */
    public function createComponentFormCarousel()
    {
        $control = $this->carouselFormFactory->create($this->carousel);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Carousel has been successfully saved', 'alert-success');
            $this->redirect('Carousel:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Carousel\ItemForm
     */
    public function createComponentFormItem()
    {
        $control = $this->itemFormFactory->create($this->carousel, $this->item);
        $control->onSuccess[] = function($item)
        {
            $this->flashMessage('Carousel item has been successfully saved', 'alert-success');
            $this->redirect('Carousel:items', $item->getCarousel()->getId());
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Carousel\CarouselGrid
     */
    public function createComponentGridCarousel()
    {
        $control = $this->carouselGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Carousel has been successfully deleted', 'alert-success');
            $this->redirect('Carousel:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Carousel\ItemGrid
     */
    public function createComponentGridItem()
    {
        $control = $this->itemGridFactory->create($this->carousel);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Carousel item has been successfully deleted', 'alert-success');
            $this->redirect('Carousel:items', $this->carousel->getId());
        };
        return $control;
    }
}
