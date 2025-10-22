<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private string $status = 'pending'; // pending, in_progress, completed

    #[ORM\Column(type: Types::INTEGER)]
    private int $durationSeconds = 0;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->date = new \DateTime(); // Por defecto, hoy
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getDurationSeconds(): int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(int $seconds): static
    {
        $this->durationSeconds = $seconds;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function addDurationSeconds(int $seconds): static
    {
        $this->durationSeconds += $seconds;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        $this->calculateDuration();
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;
        $this->calculateDuration();
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * Calculate duration based on start and end time
     */
    private function calculateDuration(): void
    {
        if ($this->startTime && $this->endTime) {
            $start = new \DateTime($this->startTime->format('H:i:s'));
            $end = new \DateTime($this->endTime->format('H:i:s'));

            // Si endTime es menor que startTime, asumimos que cruza medianoche
            if ($end < $start) {
                $end->modify('+1 day');
            }

            $diff = $start->diff($end);
            $this->durationSeconds = ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
        } else {
            $this->durationSeconds = 0;
        }
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get formatted duration as "Xh Ym"
     */
    public function getFormattedDuration(): string
    {
        if ($this->durationSeconds === 0) {
            return '0h 0m';
        }

        $hours = floor($this->durationSeconds / 3600);
        $minutes = floor(($this->durationSeconds % 3600) / 60);

        return sprintf('%dh %dm', $hours, $minutes);
    }

    /**
     * Helper to start the task timer
     */
    public function startTimer(): static
    {
        $this->startedAt = new \DateTime();
        $this->status = 'in_progress';
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * Helper to stop the timer and accumulate duration
     */
    public function stopTimer(): static
    {
        if ($this->startedAt !== null) {
            $now = new \DateTime();
            $elapsed = $now->getTimestamp() - $this->startedAt->getTimestamp();
            $this->durationSeconds += (int) $elapsed;
            $this->startedAt = null;
            $this->updatedAt = $now;
            if ($this->status === 'in_progress') {
                $this->status = 'pending';
            }
        }

        return $this;
    }
}
