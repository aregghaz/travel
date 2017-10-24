<?php

namespace DA\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class TourCategory
 *
 * @ORM\Table(name="tour_category")
 * @ORM\Entity()
 * @Gedmo\TranslationEntity(class="DA\MainBundle\Entity\Translations\TourCategoryTranslations")
 */
class TourCategory implements Translatable
{
    static $cat = array(
        1 => 'Pilgrim',
        2 => 'Excursions',
        3 => 'Alpine skiing',
        4 => 'Culinary',
        5 => 'Author',
    );

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    protected $name;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $title;

    /**
     * @ORM\OneToMany(targetEntity="TourName", mappedBy="category",cascade={"persist"})
     */
    protected $tour;

    /**
     * @ORM\OneToMany(targetEntity="DA\MainBundle\Entity\Translations\TourCategoryTranslations", mappedBy="object", cascade={"persist", "remove"})
     */
    protected $translations;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    public function __toString()
    {
        return ($this->name) ? $this->name : '';
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tour = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TourCategory
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->title = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return TourCategory
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add tour
     *
     * @param \DA\MainBundle\Entity\TourName $tour
     *
     * @return TourCategory
     */
    public function addTour(\DA\MainBundle\Entity\TourName $tour)
    {
        $this->tour[] = $tour;

        return $this;
    }

    /**
     * Remove tour
     *
     * @param \DA\MainBundle\Entity\TourName $tour
     */
    public function removeTour(\DA\MainBundle\Entity\TourName $tour)
    {
        $this->tour->removeElement($tour);
    }

    /**
     * Get tour
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTour()
    {
        return $this->tour;
    }

    /**
     * Add translation
     *
     * @param \DA\MainBundle\Entity\Translations\TourCategoryTranslations $translation
     *
     * @return TourCategory
     */
    public function addTranslation(\DA\MainBundle\Entity\Translations\TourCategoryTranslations $translation)
    {
        $this->translations[] = $translation;
        $translation->setObject($this);
        return $this;
    }

    /**
     * Remove translation
     *
     * @param \DA\MainBundle\Entity\Translations\TourCategoryTranslations $translation
     */
    public function removeTranslation(\DA\MainBundle\Entity\Translations\TourCategoryTranslations $translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
