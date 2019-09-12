<?php
namespace Dravencms\Model\Carousel\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

use Nette;

/**
 * Class ItemTranslation
 * @package App\Model\Carousel\Entities
 * @ORM\Entity
 * @ORM\Table(name="carouselItemTranslation")
 */
class ItemTranslation
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=false)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $buttonUrl;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $buttonText;


    /**
     * @var Item
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="translations")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;
    
    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * ItemTranslation constructor.
     * @param Item $item
     * @param Locale $locale
     * @param $name
     * @param $description
     * @param $url
     * @param $buttonUrl
     * @param $buttonText
     */
    public function __construct(Item $item, Locale $locale, $name, $description, $url, $buttonUrl, $buttonText)
    {
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->buttonUrl = $buttonUrl;
        $this->buttonText = $buttonText;
        $this->item = $item;
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param string $buttonUrl
     */
    public function setButtonUrl($buttonUrl)
    {
        $this->buttonUrl = $buttonUrl;
    }

    /**
     * @param string $buttonText
     */
    public function setButtonText($buttonText)
    {
        $this->buttonText = $buttonText;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item)
    {
        $this->item = $item;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getButtonUrl()
    {
        return $this->buttonUrl;
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->buttonText;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

