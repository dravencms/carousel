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

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\File\File;
use Dravencms\Model\Carousel\Entities\Carousel;
use Dravencms\Model\Carousel\Entities\Item;
use Dravencms\Model\Carousel\Entities\ItemTranslation;
use Dravencms\Model\Carousel\Repository\ItemTranslationRepository;
use Dravencms\Model\File\Repository\StructureFileRepository;
use Dravencms\Model\Carousel\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;

/**
 * Description of ItemForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var ItemRepository */
    private $itemRepository;
    
    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var ItemTranslationRepository */
    private $itemTranslationRepository;

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
     * @param ItemTranslationRepository $itemTranslationRepository
     * @param StructureFileRepository $structureFileRepository
     * @param LocaleRepository $localeRepository
     * @param Carousel $carousel
     * @param File $file
     * @param Item|null $item
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        ItemRepository $itemRepository,
        ItemTranslationRepository $itemTranslationRepository,
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
        $this->itemRepository = $itemRepository;
        $this->itemTranslationRepository = $itemTranslationRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->localeRepository = $localeRepository;
        $this->file = $file;

        if ($this->item) {
            
            $defaults = [
                'identifier' => $this->item->getIdentifier(),
                'position' => $this->item->getPosition(),
                'isActive' => $this->item->isActive(),
                'structureFile' => ($this->item->getStructureFile() ? $this->item->getStructureFile()->getId() : null),
            ];

            foreach ($this->item->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
                $defaults[$translation->getLocale()->getLanguageCode()]['description'] = $translation->getDescription();
                $defaults[$translation->getLocale()->getLanguageCode()]['buttonUrl'] = $translation->getButtonUrl();
                $defaults[$translation->getLocale()->getLanguageCode()]['buttonText'] = $translation->getButtonText();
                $defaults[$translation->getLocale()->getLanguageCode()]['url'] = $translation->getUrl();
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
     * @return \Dravencms\Components\BaseForm\BaseForm
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

        $form->addText('identifier')
            ->setRequired('Please fill in an identifier');

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

        if (!$this->itemRepository->isIdentifierFree($values->identifier, $this->carousel, $this->item)) {
            $form->addError('Tento identifier je již zabrán.');
        }
        
        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->itemTranslationRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->carousel, $this->item)) {
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
            $item->setIdentifier($values->identifier);
            $item->setIsActive($values->isActive);
            $item->setPosition($values->position);
            $item->setStructureFile($structureFile);
        } else {
            $item = new Item($this->carousel, $structureFile, $values->identifier, $values->isActive);
        }

        $this->entityManager->persist($item);

        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($articleTranslation = $this->itemTranslationRepository->getTranslation($item, $activeLocale))
            {
                $articleTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
                $articleTranslation->setButtonText($values->{$activeLocale->getLanguageCode()}->buttonText);
                $articleTranslation->setButtonUrl($values->{$activeLocale->getLanguageCode()}->buttonUrl);
                $articleTranslation->setDescription($values->{$activeLocale->getLanguageCode()}->description);
                $articleTranslation->setUrl($values->{$activeLocale->getLanguageCode()}->url);
            }
            else
            {
                $articleTranslation = new ItemTranslation(
                    $item,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name,
                    $values->{$activeLocale->getLanguageCode()}->description,
                    $values->{$activeLocale->getLanguageCode()}->url,
                    $values->{$activeLocale->getLanguageCode()}->buttonUrl,
                    $values->{$activeLocale->getLanguageCode()}->buttonText
                );
            }
            $this->entityManager->persist($articleTranslation);
        }
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
