<?php

namespace Alto\Entity;

use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Media;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class AltoDocument extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="Omeka\Entity\Media")
     * @JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $media;

    /**
     * @Column(type="text")
     */
    protected $xml;

    /**
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $modified;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setMedia(Media $media)
    {
        $this->media = $media;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setXml(string $xml): void
    {
        $this->xml = $xml;
    }

    public function getXml(): ?string
    {
        return $this->xml;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function getModified(): ?DateTime
    {
        return $this->modified;
    }

    /**
     * @PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->created = new DateTime('now');
    }

    /**
     * @PreUpdate
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $this->modified = new DateTime('now');
    }
}
