<?php

namespace App\Entity;

use App\Repository\TableValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TableValueRepository::class)
 */
class TableValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TableValue::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $table;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $row = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $column = '';

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTable(): ?self
    {
        return $this->table;
    }

    public function setTable(?self $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function getRow(): string
    {
        return $this->row;
    }

    public function setRow(string $row): self
    {
        $this->row = $row;

        return $this;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function setColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }
}
