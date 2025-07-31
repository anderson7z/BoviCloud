<?php

namespace App\Entity;

use App\Repository\VeterinarianRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; 

#[ORM\Entity(repositoryClass: VeterinarianRepository::class)]
#[UniqueEntity(
    fields: ['crmv'],
    message: 'Este CRMV já está cadastrado no sistema.'
)]
class Veterinarian
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O nome do veterinário não pode ser vazio.')]
    #[Assert\Length(
        min: 3,
        minMessage: 'O nome deve ter pelo menos {{ limit }} caracteres.'
    )]
    private ?string $nome = null;

    #[ORM\Column(length: 255, unique: true)] 
    #[Assert\NotBlank(message: 'O CRMV não pode ser vazio.')]
    private ?string $crmv = null;
    
 
    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->nome, $this->crmv);
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

    public function getCrmv(): ?string
    {
        return $this->crmv;
    }

    public function setCrmv(string $crmv): static
    {
        $this->crmv = $crmv;

        return $this;
    }
}