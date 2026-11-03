<?php declare(strict_types=1);

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
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\Item;
use Dravencms\Model\Carousel\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
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
        CurrentLocaleResolver $currentLocaleResolver,
        ImagePipe $imagePipe
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->itemRepository = $itemRepository;
        $this->entityManager = $entityManager;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
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
        $grid->setWritePermission('carousel', 'edit');

        $grid->setDataSource($this->itemRepository->getItemQueryBuilder($this->carousel));

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'carousel.admin.field.name')
            ->setAlign('center')
            ->setRenderer(function ($row) use($grid){
                /** @var Item $row */
                if ($haveImage = $row->getStructureFile()) {
                    $img = Html::el('img');
                    $img->src = $this->imagePipe->request(\Salamek\Files\Access\FileReference::attachment('carousel.item', $row->getId(), $haveImage), '200x');
                } else {
                    $img = '';
                }

                $container = Html::el('div');
                $container->addHtml($img);
                $container->addHtml(Html::el('br'));
                $container->addHtml($row->getIdentifier());

                return $container;
            })
            ->setFilterText();

        $grid->addColumnDateTime('updatedAt', 'carousel.admin.field.updated')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnBoolean('isActive', 'carousel.admin.field.active');

        $grid->addColumnPosition('position', 'carousel.admin.field.position', 'up!', 'down!');

        if ($this->getPresenter()->isAllowed('carousel', 'edit')) {
            $grid->addAction('editItem', 'carousel.admin.action.edit', 'editItem', ['carouselId' => 'carousel.id', 'itemId' => 'id'])
                ->setIcon('pencil')
                ->setTitle('carousel.admin.action.edit')
                ->setClass('btn btn-sm btn-primary');
        }

        if ($this->getPresenter()->isAllowed('carousel', 'delete')) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('carousel.admin.action.delete')
                ->setClass('btn btn-sm btn-danger ajax')
                ->setConfirmation(new \Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation('carousel.admin.confirm.delete', 'identifier'));
            $grid->addGroupAction('carousel.admin.action.delete')->onSelect[] = [$this, 'handleDelete'];
        }
        $grid->addExportCsvFiltered('carousel.admin.action.exportFiltered', 'carousel_filtered.csv')
            ->setTitle('carousel.admin.action.exportFiltered');
        $grid->addExportCsv('carousel.admin.action.exportAll', 'carousel_all.csv')
            ->setTitle('carousel.admin.action.exportAll');

        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    #[\Dravencms\User\Attributes\IsAllowed('carousel', 'delete')]
    #[\Dravencms\Security\Mutation]
    public function handleDelete($id)
    {
        $this->requirePermission('carousel', 'delete');
        $items = $this->itemRepository->getById($id);
        foreach ($items AS $item)
        {
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    /**
     * @param $id
     */
    #[\Dravencms\User\Attributes\IsAllowed('carousel', 'edit')]
    #[\Dravencms\Security\Mutation]
    public function handleUp($id)
    {
        $this->requirePermission('carousel', 'edit');
        $articleItem = $this->itemRepository->getOneById($id);
        $this->itemRepository->moveUp($articleItem, 1);
    }

    /**
     * @param $id
     */
    #[\Dravencms\User\Attributes\IsAllowed('carousel', 'edit')]
    #[\Dravencms\Security\Mutation]
    public function handleDown($id)
    {
        $this->requirePermission('carousel', 'edit');
        $articleItem = $this->itemRepository->getOneById($id);
        $this->itemRepository->moveDown($articleItem, 1);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemGrid.latte');
        $template->render();
    }
}
