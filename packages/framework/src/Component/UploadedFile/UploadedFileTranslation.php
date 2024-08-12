<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="uploaded_files_translations")
 * @ORM\Entity
 */
class UploadedFileTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     * @Prezent\Translatable(targetEntity="\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile")
     */
    protected $translatable;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
