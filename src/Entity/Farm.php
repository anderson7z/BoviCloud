<?php

namespace App\Entity;

use App\Repository\FarmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: FarmRepository::class)]
#[UniqueEntity(fields: ['nome'], message: 'Já existe uma fazenda cadastrada com este nome.')]
class Farm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'O nome da fazenda é obrigatório.')]
    #[Assert\Length(min: 3, minMessage: 'O nome da fazenda deve ter pelo menos {{ limit }} caracteres.')]
    private ?string $nome = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'O tamanho da fazenda é obrigatório.')]
    #[Assert\Positive(message: 'O tamanho deve ser um número positivo (maior que zero).')]
    private ?float $tamanho = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O nome do responsável é obrigatório.')]
    private ?string $responsavel = null;

    #[ORM\ManyToMany(targetEntity: Veterinarian::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'farm_veterinarian')]
    private Collection $veterinarios;

    #[ORM\OneToMany(targetEntity: Cow::class, mappedBy: 'fazenda', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $cows;

    public function __construct()
    {
        $this->veterinarios = new ArrayCollection();
        $this->cows = new ArrayCollection();
    }


    public function __toString(): string
    {
        return (string) $this->nome;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getTamanho(): ?float
    {
        return $this->tamanho;
    }

    public function setTamanho(float $tamanho): static
    {
        $this->tamanho = $tamanho;

        return $this;
    }

    public function getResponsavel(): ?string
    {
        return $this->responsavel;
    }

    public function setResponsavel(string $responsavel): static
    {
        $this->responsavel = $responsavel;

        return $this;
    }

    public function getVeterinarios(): Collection
    {
        return $this->veterinarios;
    }

    public function addVeterinario(Veterinarian $veterinario): static
    {
        if (!$this->veterinarios->contains($veterinario)) {
            $this->veterinarios->add($veterinario);
        }

        return $this;
    }

    public function removeVeterinario(Veterinarian $veterinario): static
    {
        $this->veterinarios->removeElement($veterinario);

        return $this;
    }

    public function getCows(): Collection
    {
        return $this->cows;
    }

    public function addCow(Cow $cow): static
    {
        if (!$this->cows->contains($cow)) {
            $this->cows->add($cow);
            $cow->setFazenda($this);
        }

        return $this;
    }

    public function removeCow(Cow $cow): static
    {
        if ($this->cows->removeElement($cow)) {

            if ($cow->getFazenda() === $this) {
                $cow->setFazenda(null);
            }
        }

        return $this;
    }
}