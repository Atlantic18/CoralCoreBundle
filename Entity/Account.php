<?php
namespace Coral\CoreBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="coral_account")
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity="Coral\CoreBundle\Entity\Client", mappedBy="account")
     */
    private $remoteApplications;

    /**
     * @ORM\OneToMany(targetEntity="Coral\CoreBundle\Entity\Observer", mappedBy="account")
     */
    private $observers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->remoteApplications = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Account
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
     * Add remoteApplications
     *
     * @param \Coral\CoreBundle\Entity\Client $remoteApplications
     * @return Account
     */
    public function addRemoteApplication(\Coral\CoreBundle\Entity\Client $remoteApplications)
    {
        $this->remoteApplications[] = $remoteApplications;

        return $this;
    }

    /**
     * Remove remoteApplications
     *
     * @param \Coral\CoreBundle\Entity\Client $remoteApplications
     */
    public function removeRemoteApplication(\Coral\CoreBundle\Entity\Client $remoteApplications)
    {
        $this->remoteApplications->removeElement($remoteApplications);
    }

    /**
     * Get remoteApplications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRemoteApplications()
    {
        return $this->remoteApplications;
    }

    /**
     * Add observers
     *
     * @param \Coral\CoreBundle\Entity\Observer $observers
     * @return Account
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

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Account
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Account
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}