<?php

namespace App\Repository;

use App\Entity\Farm;
use App\Entity\Veterinarian;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FarmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Farm::class);
    }

    /**
     * Conta o número de fazendas.
     * Se um veterinário for fornecido, conta apenas as fazendas associadas a ele.
     * Se nenhum veterinário for fornecido (null), conta todas as fazendas no sistema.
     *
     * @param Veterinarian|null $veterinarian O veterinário para filtrar, ou null para não filtrar.
     * @return int O número de fazendas.
     */
    public function countForVeterinarian(?Veterinarian $veterinarian = null): int
    {
        $qb = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)');

        // Adiciona o filtro SOMENTE se um veterinário for passado como argumento.
        if ($veterinarian) {
            $qb->innerJoin('f.veterinarios', 'v')
               ->where('v = :vet') // É uma boa prática passar o objeto inteiro
               ->setParameter('vet', $veterinarian);
        }

        // Se $veterinarian for null, a consulta será um simples "SELECT COUNT(f.id) FROM farm f",
        // que é exatamente o que queremos para contar o total de fazendas.
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}