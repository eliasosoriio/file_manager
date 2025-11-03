<?php

namespace App\Entity;

use App\Repository\TimeEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeEntryRepository::class)]
#[ORM\Table(name: 'time_entries')]
class TimeEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $task = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;
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
     * Helper to start the timer
     */
    public function startTimer(): static
    {
        $this->startedAt = new \DateTime();
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
        }

        return $this;
    }
}
