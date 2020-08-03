<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="form_submission")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class FormSubmission
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private $id;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="instance", type="string", length=255)
     */
    private $instance;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=2)
     */
    private $locale;

    /**
     * @var array<string, mixed>
     *
     * @ORM\Column(name="data", type="json_array")
     */
    private $data;

    /**
     * @var Collection<int, FormSubmissionFile>
     *
     * @ORM\OneToMany(targetEntity="FormSubmissionFile", mappedBy="formSubbmission", cascade={"persist", "remove"})
     */
    protected $files;

    /**
     * @var int
     *
     * @ORM\Column(name="process_try_counter", type="integer", nullable=false, options={"default": 0})
     */
    private $processTryCounter;

    /**
     * @var string
     *
     * @ORM\Column(name="process_id", type="string", length=255, nullable=true)
     */
    private $processId;

    public function __construct(string $name, string $instance, string $locale, array $data)
    {
        $now = new \DateTime();

        $this->id = Uuid::uuid4();
        $this->created = $now;
        $this->modified = $now;
        $this->processTryCounter = 0;

        $this->name = $name;
        $this->instance = $instance;
        $this->locale = $locale;
        $this->data = $data;

        $this->files = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateModified(): void
    {
        $this->modified = new \DateTime();
    }

    public function getProcessTryCounter(): int
    {
        return $this->processTryCounter;
    }

    public function setProcessTryCounter(int $processTryCounter): FormSubmission
    {
        $this->processTryCounter = $processTryCounter;
        return $this;
    }

    public function getProcessId(): ?string
    {
        return $this->processId;
    }

    public function setProcessId(string $processId): FormSubmission
    {
        $this->processId = $processId;
        return $this;
    }
}
