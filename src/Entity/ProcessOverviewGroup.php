<?php

namespace App\Entity;

use App\Repository\ProcessOverviewGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ProcessOverviewGroupRepository::class)]
class ProcessOverviewGroup
{
    use BlameableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    /**
     * @var Collection<int, ProcessOverview>
     */
    #[ORM\OneToMany(targetEntity: ProcessOverview::class, mappedBy: 'group', orphanRemoval: true)]
    private Collection $overviews;

    public function __construct()
    {
        $this->overviews = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->label ?? $this::class;
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

    /**
     * @return Collection<int, ProcessOverview>
     */
    public function getOverviews(): Collection
    {
        return $this->overviews;
    }

    public function addOverview(ProcessOverview $process): static
    {
        if (!$this->overviews->contains($process)) {
            $this->overviews->add($process);
            $process->setGroup($this);
        }

        return $this;
    }

    public function removeOverview(ProcessOverview $process): static
    {
        if ($this->overviews->removeElement($process)) {
            // set the owning side to null (unless already changed)
            if ($process->getGroup() === $this) {
                $process->setGroup(null);
            }
        }

        return $this;
    }
}
