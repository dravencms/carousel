<?php
namespace Dravencms\Model\Carousel\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class CarouselTranslation
 * @package App\Model\Carousel\Entities
 * @ORM\Entity
 * @ORM\Table(name="carouselCarouselTranslation")
 */
class CarouselTranslation extends Nette\Object
{
    use Identifier;

    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $name;

    /**
     * @var Carousel
     * @ORM\ManyToOne(targetEntity="Carousel", inversedBy="translations")
     * @ORM\JoinColumn(name="carousel_id", referencedColumnName="id")
     */
    private $carousel;
    
    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * CarouselTranslation constructor.
     * @param string $name
     * @param Carousel $carousel
     * @param Locale $locale
     */
    public function __construct(Carousel $carousel, Locale $locale, $name)
    {
        $this->name = $name;
        $this->carousel = $carousel;
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
     * @param Carousel $carousel
     */
    public function setCarousel(Carousel $carousel)
    {
        $this->carousel = $carousel;
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
     * @return Carousel
     */
    public function getCarousel()
    {
        return $this->carousel;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }


}

