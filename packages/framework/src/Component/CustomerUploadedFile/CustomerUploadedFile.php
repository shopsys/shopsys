<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

/**
 * @ORM\Table(name="customer_uploaded_files", indexes={
 *     @ORM\Index(columns={"entity_name", "entity_id"}),
 *     @ORM\Index(columns={"id", "slug", "extension", "customer_user_id"}),
 *     @ORM\Index(columns={"id", "slug", "extension", "hash"}),
 * })
 * @ORM\Entity
 */
class CustomerUploadedFile extends AbstractUploadedFile
{
    protected const string UPLOAD_KEY = 'customerUploadedFile';

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $entityName;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $type;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $customerUser;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $hash;

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param int $position
     * @param string $hash
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(
        string $entityName,
        int $entityId,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        int $position,
        string $hash,
        ?CustomerUser $customerUser = null,
    ) {
        $this->entityName = $entityName;
        $this->entityId = $entityId;
        $this->type = $type;
        $this->setTemporaryFilename($temporaryFilename);
        $this->setNameAndSlug($uploadedFilename);
        $this->position = $position;
        $this->hash = $hash;
        $this->customerUser = $customerUser;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     */
    public function checkForDelete(string $entityName, int $entityId): void
    {
        if ($this->entityName !== $entityName || $this->entityId !== $entityId) {
            throw new CustomerFileNotFoundException(
                sprintf(
                    'Entity "%s" with ID "%s" does not own file with ID "%s"',
                    $entityName,
                    $entityId,
                    $this->id,
                ),
            );
        }
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    protected function getUploadKey(): string
    {
        return self::UPLOAD_KEY;
    }

    /**
     * @return string
     */
    protected function getFileForUploadCategory(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
