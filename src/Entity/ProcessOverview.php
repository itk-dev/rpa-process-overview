<?php

namespace App\Entity;

use App\Repository\ProcessOverviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ProcessOverviewRepository::class)]
#[ORM\Table(name: 'rpa_process_overview_process_overview')]
#[ORM\HasLifecycleCallbacks]
class ProcessOverview
{
    use BlameableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'processes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProcessOverviewGroup $group = null;

    /**
     * @var Collection<int, Process>
     */
    #[ORM\OneToMany(targetEntity: Process::class, mappedBy: 'process', orphanRemoval: true)]
    private Collection $steps;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $options = null;

    #[ORM\ManyToOne(inversedBy: 'processOverviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DataSource $dataSource = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $processId = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getGroup(): ?ProcessOverviewGroup
    {
        return $this->group;
    }

    public function setGroup(?ProcessOverviewGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Collection<int, Process>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Process $step): static
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setProcess($this);
        }

        return $this;
    }

    public function removeStep(Process $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getProcess() === $this) {
                $step->setProcess(null);
            }
        }

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(string $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getDataSource(): ?DataSource
    {
        return $this->dataSource;
    }

    public function setDataSource(?DataSource $dataSource): static
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    public function getProcessId(): ?string
    {
        return $this->processId;
    }

    public function setProcessId(?string $processId): static
    {
        $this->processId = $processId;

        return $this;
    }

    /**
     * Decide if overview is ready for display.
     */
    public function isReady(): bool
    {
        return null !== $this->getProcessId();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('dataSource')) {
            /** @var self $object */
            $object = $args->getObject();
            $object->setProcessId(null);
        }
    }
}
