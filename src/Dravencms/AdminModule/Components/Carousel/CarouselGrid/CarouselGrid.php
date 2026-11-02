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

namespace Dravencms\AdminModule\Components\Carousel\CarouselGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Carousel\Repository\CarouselRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;

/**
 * Description of CarouselGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class CarouselGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var CarouselRepository */
    private $carouselRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var CurrentLocale */
    private $currentLocale;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * CarouselGrid constructor.
     * @param CarouselRepository $carouselRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param CurrentLocale $currentLocale
     */
    public function __construct(
        CarouselRepository $carouselRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->carouselRepository = $carouselRepository;
        $this->entityManager = $entityManager;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->carouselRepository->getCarouselQueryBuilder());

        $grid->addColumnText('identifier', 'carousel.admin.field.identifier')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnDateTime('updatedAt', 'carousel.admin.field.updated')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnBoolean('isActive', 'carousel.admin.field.active');


        if ($this->getPresenter()->isAllowed('carousel', 'edit')) {

            $grid->addAction('items', '')
                ->setIcon('folder-open')
                ->setTitle('carousel.admin.action.items')
                ->setClass('btn btn-sm btn-primary');


            $grid->addAction('edit', '')
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
        $carousels = $this->carouselRepository->getById($id);
        foreach ($carousels AS $carousel)
        {
            $this->entityManager->remove($carousel);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/CarouselGrid.latte');
        $template->render();
    }
}
