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

namespace Dravencms\AdminModule\Components\Carousel\ItemForm;

use Dravencms\Components\BaseFormFactory;
use Dravencms\File\File;
use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\Item;
use Dravencms\Model\File\Repository\StructureFileRepository;
use Dravencms\Model\Carousel\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of ItemForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var ItemRepository */
    private $pictureRepository;
    
    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Carousel */
    private $carousel;

    /** @var File */
    private $file;

    /** @var Item|null */
    private $item = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * ItemForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param ItemRepository $itemRepository
     * @param StructureFileRepository $structureFileRepository
     * @param LocaleRepository $localeRepository
     * @param Carousel $carousel
     * @param File $file,
     * @param Item|null $item
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        ItemRepository $itemRepository,
        StructureFileRepository $structureFileRepository,
        LocaleRepository $localeRepository,
        Carousel $carousel,
        File $file,
        Item $item = null
    ) {
        parent::__construct();

        $this->carousel = $carousel;
        $this->item = $item;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->pictureRepository = $itemRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->localeRepository = $localeRepository;
        $this->file = $file;

        if ($this->item) {
            
            $defaults = [
                /*'name' => $this->item->getName(),
                'description' => $this->item->getDescription(),*/
                'position' => $this->item->getPosition(),
                'isActive' => $this->item->isActive(),
                'structureFile' => $this->item->getStructureFile()->getId(),
                /*'buttonUrl' => $this->item->getButtonUrl(),
                'buttonText' => $this->item->getButtonText(),
                'url' => $this->item->getUrl(),*/
            ];

            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaults += $repository->findTranslations($this->item);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale) {
                $defaults[$defaultLocale->getLanguageCode()]['name'] = $this->item->getName();
                $defaults[$defaultLocale->getLanguageCode()]['description'] = $this->item->getDescription();
                $defaults[$defaultLocale->getLanguageCode()]['buttonUrl'] = $this->item->getButtonUrl();
                $defaults[$defaultLocale->getLanguageCode()]['buttonText'] = $this->item->getButtonText();
                $defaults[$defaultLocale->getLanguageCode()]['url'] = $this->item->getUrl();
            }

        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return \Dravencms\Components\BaseForm
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('Please enter carousel item name.')
                ->addRule(Form::MAX_LENGTH, 'Carousel name is too long.', 255);

            $container->addTextArea('description');

            $container->addText('buttonUrl');
            $container->addText('buttonText');

            $container->addText('url');
        }

        $form->addText('structureFile')
            ->setType('number')
            ->setRequired('Please select the photo.');

        $form->addText('position')
            ->setDisabled(is_null($this->item));

        $form->addCheckbox('isActive');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->pictureRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->carousel, $this->item)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->presenter->isAllowed('carousel', 'edit')) {
            $form->addError('Nemáte oprávění editovat carousel.');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        $structureFile = $this->structureFileRepository->getOneById($values->structureFile);
        if ($this->item) {
            $item = $this->item;
            /*$item->setName($values->name);
            $item->setDescription($values->description);*/
            $item->setIsActive($values->isActive);
            $item->setPosition($values->position);
            /*$item->setUrl($values->url);
            $item->setButtonText($values->buttonText);
            $item->setButtonUrl($values->buttonUrl);*/
            $item->setStructureFile($structureFile);
        } else {
            $defaultLocale = $this->localeRepository->getDefault();
            $item = new Item($this->carousel, $structureFile, $values->{$defaultLocale->getLanguageCode()}->name, $values->{$defaultLocale->getLanguageCode()}->description, $values->{$defaultLocale->getLanguageCode()}->url, $values->{$defaultLocale->getLanguageCode()}->buttonUrl, $values->{$defaultLocale->getLanguageCode()}->buttonText, $values->isActive);
        }

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($item, 'name', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->name)
                ->translate($item, 'description', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->description)
                ->translate($item, 'url', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->url)
                ->translate($item, 'buttonText', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->buttonText)
                ->translate($item, 'buttonUrl', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->buttonUrl);
        }

        $this->entityManager->persist($item);

        $this->entityManager->flush();

        $this->onSuccess($item);
    }


    public function render()
    {
        $template = $this->template;
        $template->fileSelectorPath = $this->file->getFileSelectorPath();
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/ItemForm.latte');
        $template->render();
    }
}