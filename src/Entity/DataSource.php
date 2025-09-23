<?php

namespace App\Entity;

use App\Repository\DataSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DataSourceRepository::class)]
class DataSource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private array $options = [];

    /**
     * @var Collection<int, ProcessOverview>
     */
    #[ORM\OneToMany(targetEntity: ProcessOverview::class, mappedBy: 'dataSource')]
    private Collection $processOverviews;

    public function __construct()
    {
        $this->processOverviews = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getLabel();
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

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Collection<int, ProcessOverview>
     */
    public function getProcessOverviews(): Collection
    {
        return $this->processOverviews;
    }

    public function addProcessOverview(ProcessOverview $processOverview): static
    {
        if (!$this->processOverviews->contains($processOverview)) {
            $this->processOverviews->add($processOverview);
            $processOverview->setDataSource($this);
        }

        return $this;
    }

    public function removeProcessOverview(ProcessOverview $processOverview): static
    {
        if ($this->processOverviews->removeElement($processOverview)) {
            // set the owning side to null (unless already changed)
            if ($processOverview->getDataSource() === $this) {
                $processOverview->setDataSource(null);
            }
        }

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->getOptions()['url'] ?? null;
    }
}
