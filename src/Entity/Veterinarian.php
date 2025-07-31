<?php

namespace App\Entity;

use App\Repository\VeterinarianRepository;
use Doctrine\Common\Collections\ArrayCollection; // ADICIONADO: Necessário para a coleção
use Doctrine\Common\Collections\Collection;      // ADICIONADO: Necessário para a tipagem da coleção
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
    
    /**
     * ADICIONADO: Propriedade para a relação Muitos-para-Muitos com Farm.
     * "mappedBy" indica que a configuração "dona" da relação está na entidade Farm,
     * em uma propriedade chamada 'veterinarios'.
     * @var Collection<int, Farm>
     */
    #[ORM\ManyToMany(targetEntity: Farm::class, mappedBy: 'veterinarios')]
    private Collection $farms;

    /**
     * ADICIONADO: Construtor para inicializar a coleção.
     * Isso é crucial para evitar erros ao tentar adicionar uma fazenda a um
     * veterinário que ainda não foi salvo no banco de dados.
     */
    public function __construct()
    {
        $this->farms = new ArrayCollection();
    }
 
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

    /**
     * ADICIONADO: O método que faltava para obter as fazendas.
     * @return Collection<int, Farm>
     */
    public function getFarms(): Collection
    {
        return $this->farms;
    }

    public function addFarm(Farm $farm): static
    {
        if (!$this->farms->contains($farm)) {
            $this->farms->add($farm);
            // Garante que o outro lado da relação também seja atualizado
            $farm->addVeterinario($this); 
        }

        return $this;
    }

    public function removeFarm(Farm $farm): static
    {
        if ($this->farms->removeElement($farm)) {
            // Garante que o outro lado da relação também seja atualizado
            $farm->removeVeterinario($this);
        }

        return $this;
    }
}