<?php

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;

trait PublishableEntity
{
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    public function isPublished(): bool
    {
        $publishedAt = $this->getPublishedAt();

        return null !== $publishedAt && $publishedAt <= new \DateTimeImmutable();
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
