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

namespace Dravencms\AdminModule\Components\Carousel\CarouselForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Repository\CarouselRepository;
use Dravencms\Database\EntityManager;
use Nette\Application\UI\Form;

/**
 * Description of CarouselForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class CarouselForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var CarouselRepository */
    private $carouselRepository;

    /** @var Carousel|null */
    private $carousel = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * CarouselForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param CarouselRepository $carouselRepository
     * @param Carousel|null $carousel
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        CarouselRepository $carouselRepository,
        ?Carousel $carousel = null
    ) {
        parent::__construct();

        $this->carousel = $carousel;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->carouselRepository = $carouselRepository;
    }

    /**
     * @return \Dravencms\Components\BaseForm\BaseForm
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addText('identifier')
            ->setRequired('carousel.admin.validation.carouselIdentifierRequired')
            ->addRule(Form::MAX_LENGTH, 'carousel.admin.validation.identifierTooLong', 255);

        $form->addCheckbox('isActive');


        $form->addSubmit('send', 'carousel.admin.action.save');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        if ($this->carousel) {
            $defaults = [
                'identifier' => $this->carousel->getIdentifier(),
                'isActive' => $this->carousel->isActive()
            ];
        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $form->setDefaults($defaults);

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();
        if (!$this->carouselRepository->isIdentifierFree($values->identifier, $this->carousel)) {
            $form->addError('carousel.admin.validation.identifierTaken');
        }

        if (!$this->getPresenter()->isAllowed('carousel', 'edit')) {
            $form->addError('carousel.admin.validation.editForbidden');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form)
    {
        $this->requirePermission('carousel', 'edit');
        $values = $form->getValues();


        if ($this->carousel) {
            $carousel = $this->carousel;
            $carousel->setIdentifier($values->identifier);
            $carousel->setIsActive($values->isActive);
        } else {
            $carousel = new Carousel($values->identifier, $values->isActive);
        }


        $this->entityManager->persist($carousel);

        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/CarouselForm.latte');
        $template->render();
    }
}
