<?php

namespace App\Entity;

use App\Repository\ProcessOverviewGroupRepository;
use App\Trait\PublishableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ProcessOverviewGroupRepository::class)]
class ProcessOverviewGroup implements Publishable
{
    use BlameableEntity;
    use TimestampableEntity;
    use PublishableEntity;

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
     * Get public overviews.
     *
     * A public overview is both ready and published.
     *
     * @return Collection<int, ProcessOverview>
     */
    public function getPublicOverviews(): Collection
    {
        return $this->overviews->filter(static fn (ProcessOverview $overview) => $overview->isReady() && $overview->isPublished());
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
