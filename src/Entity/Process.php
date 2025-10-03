<?php

namespace App\Entity;

use App\Repository\ProcessStepRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcessStepRepository::class)]
#[ORM\Table(name: 'rpa_process_overview_process')]
class Process
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProcessOverview $process = null;

    #[ORM\Column]
    private ?int $rank = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProcess(): ?ProcessOverview
    {
        return $this->process;
    }

    public function setProcess(?ProcessOverview $process): static
    {
        $this->process = $process;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }
}
