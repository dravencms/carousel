<?php

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Carousel\ItemGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\Item;
use Dravencms\Model\Carousel\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\Html;
use Salamek\Files\ImagePipe;

/**
 * Description of ItemGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemGrid extends BaseControl
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var ItemRepository */
    private $itemRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var ImagePipe */
    private $imagePipe;

    /** @var Carousel */
    private $carousel;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * ItemGrid constructor.
     * @param Carousel $carousel
     * @param ItemRepository $itemRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param CurrentLocale $currentLocale
     * @param ImagePipe $imagePipe
     */
    public function __construct(
        Carousel $carousel,
        ItemRepository $itemRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        CurrentLocale $currentLocale,
        ImagePipe $imagePipe
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->itemRepository = $itemRepository;
        $this->entityManager = $entityManager;
        $this->currentLocale = $currentLocale;
        $this->imagePipe = $imagePipe;
        $this->carousel = $carousel;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->itemRepository->getItemQueryBuilder($this->carousel));

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'Name')
            ->setCustomRender(function ($row) use($grid){
                /** @var Item $row */
                if ($haveImage = $row->getStructureFile()) {
                    $img = Html::el('img');
                    $img->src = $this->imagePipe->request($haveImage->getFile(), '200x');
                } else {
                    $img = '';
                }

                return $img . Html::el('br') . $row->getIdentifier();
            })
            ->setFilterText()
            ->setSuggestion();

        $grid->getColumn('identifier')->cellPrototype->class[] = 'center';

        $grid->addColumnDate('updatedAt', 'Last edit', $this->currentLocale->getDateTimeFormat())
            ->setSortable()
            ->setFilterDate();
        $grid->getColumn('updatedAt')->cellPrototype->class[] = 'center';

        $grid->addColumnBoolean('isActive', 'Active');

        $grid->addColumnNumber('position', 'Position')
            ->setFilterNumber()
            ->setSuggestion();

        $grid->getColumn('position')->cellPrototype->class[] = 'center';

        if ($this->presenter->isAllowed('carousel', 'edit')) {
            $grid->addActionHref('editItem', 'Upravit')
                ->setCustomHref(function($row){
                    return $this->presenter->link('editItem', ['carouselId' => $row->getCarousel()->getId(), 'itemId' => $row->getId()]);
                })
                ->setIcon('pencil');
        }

        if ($this->presenter->isAllowed('carousel', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat carousel %s ?', $row->getIdentifier()];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i carousel ?');
        }
        $grid->setExport();

        return $grid;
    }

    /**
     * @param $action
     * @param $ids
     */
    public function gridOperationsHandler($action, $ids)
    {
        switch ($action)
        {
            case 'delete':
                $this->handleDelete($ids);
                break;
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $items = $this->itemRepository->getById($id);
        foreach ($items AS $item)
        {
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemGrid.latte');
        $template->render();
    }
}
