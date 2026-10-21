<?php declare(strict_types=1);
namespace Dravencms\Model\Carousel\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Dravencms\Model\File\Entities\StructureFile;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Dravencms\Database\Attributes\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class Item
 * @package App\Model\Carousel\Entities
 */
#[ORM\Entity(repositoryClass: "Gedmo\Sortable\Entity\Repository\SortableRepository")]
#[ORM\UniqueConstraint(name: "identifier_unique", columns: ["identifier", "carousel_id"])]
#[ORM\Table(name: "carouselItem")]
class Item
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     */
    #[ORM\Column(type: "string",length: 255,nullable: false)]
    private $identifier;
    
    /**
     * @var integer
     */
    #[Gedmo\SortablePosition]
    #[ORM\Column(type: "integer")]
    private $position;

    /**
     * @var boolean
     */
    #[ORM\Column(type: "boolean", nullable: false)]
    private $isActive;

    /**
     * @var StructureFile
     */
    #[ORM\ManyToOne(targetEntity: "\Dravencms\Model\File\Entities\StructureFile")]
    #[ORM\JoinColumn(name: "structure_file_id", referencedColumnName: "id")]
    private $structureFile;

    /**
     * @var Carousel
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: "Carousel", inversedBy: "items")]
    #[ORM\JoinColumn(name: "carousel_id", referencedColumnName: "id")]
    private $carousel;

    /**
     * @var ArrayCollection|ItemTranslation[]
     */
    #[ORM\OneToMany(targetEntity: "ItemTranslation", mappedBy: "item",cascade: ["persist", "remove"])]
    private $translations;

    /**
     * Item constructor.
     * @param Carousel $carousel
     * @param StructureFile $structureFile
     * @param $identifier
     * @param bool $isActive
     */
    public function __construct(Carousel $carousel, StructureFile $structureFile, $identifier, $isActive = true)
    {
        $this->identifier = $identifier;
        $this->isActive = $isActive;
        $this->structureFile = $structureFile;
        $this->carousel = $carousel;
        $this->translations = new ArrayCollection();
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
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param StructureFile $structureFile
     */
    public function setStructureFile(StructureFile $structureFile)
    {
        $this->structureFile = $structureFile;
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
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return StructureFile
     */
    public function getStructureFile()
    {
        return $this->structureFile;
    }

    /**
     * @return Carousel
     */
    public function getCarousel()
    {
        return $this->carousel;
    }

    /**
     * @return ArrayCollection|ItemTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}

