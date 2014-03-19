<?php
namespace Coral\CoreBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="coral_event")
 */
class Event
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", unique=true, length=64, nullable=false)
     */
    private $name;

    /** 
     * @ORM\OneToMany(targetEntity="Coral\CoreBundle\Entity\Observer", mappedBy="event")
     */
    private $observers;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->observers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
    
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
     * Add observers
     *
     * @param \Coral\CoreBundle\Entity\Observer $observers
     * @return Event
     */
    public function addObserver(\Coral\CoreBundle\Entity\Observer $observers)
    {
        $this->observers[] = $observers;
    
        return $this;
    }

    /**
     * Remove observers
     *
     * @param \Coral\CoreBundle\Entity\Observer $observers
     */
    public function removeObserver(\Coral\CoreBundle\Entity\Observer $observers)
    {
        $this->observers->removeElement($observers);
    }

    /**
     * Get observers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getObservers()
    {
        return $this->observers;
    }
}