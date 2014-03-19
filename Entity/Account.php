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
     * @ORM\OneToMany(targetEntity="Coral\ContentBundle\Entity\Node", mappedBy="account")
     */
    private $sitemaps;

    /**
     * @ORM\OneToMany(targetEntity="Coral\CoreBundle\Entity\Client", mappedBy="account")
     */
    private $remoteApplications;

    /**
     * @ORM\OneToMany(targetEntity="Coral\CoreBundle\Entity\Observer", mappedBy="account")
     */
    private $observers;

    /**
     * @ORM\OneToMany(targetEntity="Coral\FileBundle\Entity\File", mappedBy="account")
     */
    private $files;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sitemaps = new \Doctrine\Common\Collections\ArrayCollection();
        $this->remoteApplications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->observers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add sitemaps
     *
     * @param \Coral\ContentBundle\Entity\Node $sitemaps
     * @return Account
     */
    public function addSitemap(\Coral\ContentBundle\Entity\Node $sitemaps)
    {
        $this->sitemaps[] = $sitemaps;

        return $this;
    }

    /**
     * Remove sitemaps
     *
     * @param \Coral\ContentBundle\Entity\Node $sitemaps
     */
    public function removeSitemap(\Coral\ContentBundle\Entity\Node $sitemaps)
    {
        $this->sitemaps->removeElement($sitemaps);
    }

    /**
     * Get sitemaps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSitemaps()
    {
        return $this->sitemaps;
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
     * Add files
     *
     * @param \Coral\FileBundle\Entity\File $files
     * @return Account
     */
    public function addFile(\Coral\FileBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \Coral\FileBundle\Entity\File $files
     */
    public function removeFile(\Coral\FileBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
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