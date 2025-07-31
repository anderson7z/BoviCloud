<?php

namespace App\Entity;

use App\Repository\CowRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CowRepository::class)]
#[UniqueEntity(fields: ['codigo'], message: 'Já existe um animal com este código.')]
class Cow
{

    public const STATUS_VIVO = 'vivo';
    public const STATUS_ABATIDO = 'abatido';
   // public const STATUS_MORTO = 'morto';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'O código é obrigatório.')]
    private ?string $codigo = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'A produção de leite é obrigatória.')]
    #[Assert\PositiveOrZero(message: 'O valor do leite não pode ser negativo.')]
    private ?float $leite = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'A quantidade de ração é obrigatória.')]
    #[Assert\PositiveOrZero(message: 'O valor da ração não pode ser negativo.')]
    private ?float $racao = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'O peso é obrigatório.')]
    #[Assert\Positive(message: 'O peso deve ser um valor positivo.')]
    private ?float $peso = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'A data de nascimento é obrigatória.')]
    #[Assert\LessThanOrEqual('today', message: 'A data de nascimento não pode ser no futuro.')]
    private ?\DateTimeInterface $nascimento = null;

    #[ORM\ManyToOne(inversedBy: 'cows')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'A fazenda é obrigatória.')]
    private ?Farm $fazenda = null;

    #[ORM\Column(length: 255)]
    private string $status;

    public function __construct()
    {
  
        $this->status = self::STATUS_VIVO;
    }

    public function __toString(): string
    {
        return (string) $this->codigo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getLeite(): ?float
    {
        return $this->leite;
    }

    public function setLeite(float $leite): static
    {
        $this->leite = $leite;

        return $this;
    }

    public function getRacao(): ?float
    {
        return $this->racao;
    }

    public function setRacao(float $racao): static
    {
        $this->racao = $racao;

        return $this;
    }

    public function getPeso(): ?float
    {
        return $this->peso;
    }

    public function setPeso(float $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function getNascimento(): ?\DateTimeInterface
    {
        return $this->nascimento;
    }

    public function setNascimento(\DateTimeInterface $nascimento): static
    {
        $this->nascimento = $nascimento;

        return $this;
    }

    public function getFazenda(): ?Farm
    {
        return $this->fazenda;
    }

    public function setFazenda(?Farm $fazenda): static
    {
        $this->fazenda = $fazenda;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
       
        if (!in_array($status, [self::STATUS_VIVO, self::STATUS_ABATIDO])) {
            throw new \InvalidArgumentException("Status inválido fornecido: '{$status}'");
        }
        $this->status = $status;

        return $this;
    }
}