<?php
namespace Dravencms\Model\Carousel\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Carousel
 * @package App\Model\Carousel\Entities
 * @ORM\Entity
 * @ORM\Table(name="carouselCarousel")
 */
class Carousel
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $identifier;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;
    
    /**
     * @var ArrayCollection|Item[]
     * @ORM\OneToMany(targetEntity="Item", mappedBy="carousel",cascade={"persist"})
     */
    private $items;

    /**
     * Carousel constructor.
     * @param string $name
     * @param bool $isActive
     */
    public function __construct($name, $isActive = true)
    {
        $this->identifier = $name;
        $this->isActive = $isActive;
        $this->items = new ArrayCollection();
    }
    
    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return Item[]|ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }
}

