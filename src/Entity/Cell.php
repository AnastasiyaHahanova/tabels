<?php

namespace App\Entity;

use App\Repository\CellRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CellRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_row_column_idx", columns={"rowIndex", "columnIndex"})})
 */
class Cell
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Spreadsheet::class)
     * @ORM\JoinColumn(name="spreadsheet", referencedColumnName="id", nullable=false)
     */
    private $spreadsheet;

    /**
     * @ORM\Column(name="rowIndex",type="integer",nullable=false)
     * @Assert\Type(
     *     type="integer",
     *     message="The rows index {{ value }} is not a valid {{ type }}."
     * )
     */
    private $row;

    /**
     * @ORM\Column(name="columnIndex",type="integer",nullable=false)
     * @Assert\Type(
     *     type="integer",
     *     message="The columns index {{ value }} is not a valid {{ type }}."
     * )
     */
    private $column;

    /**
     * @ORM\Column(name="value",type="decimal", precision=15, scale=6)
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    public function setSpreadsheet(Spreadsheet $spreadsheet): self
    {
        $this->spreadsheet = $spreadsheet;

        return $this;
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function setRow(int $row): self
    {
        $this->row = $row;

        return $this;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function setColumn(int $column): self
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
