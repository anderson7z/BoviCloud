<?php

namespace App\Repository;

use App\Entity\Cow;
use App\Entity\Veterinarian;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cow::class);
    }

    /**
     * Retorna a produção total de leite.
     * Se um veterinário for fornecido, a busca é filtrada para as fazendas associadas a ele.
     */
    public function getTotalMilkProduction(?Veterinarian $veterinarian = null): float
    {
        $qb = $this->createQueryBuilder('c')
            // COALESCE garante que o resultado seja 0 se a soma for nula, evitando a exceção.
            ->select('COALESCE(SUM(c.leite), 0)')
            ->where('c.status = :status')
            ->setParameter('status', Cow::STATUS_VIVO);

        if ($veterinarian) {
            $qb->innerJoin('c.fazenda', 'f')
               ->innerJoin('f.veterinarios', 'v')
               ->andWhere('v = :vet') // Usamos 'v = :vet' para passar o objeto inteiro
               ->setParameter('vet', $veterinarian);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Retorna o consumo total de ração.
     * Se um veterinário for fornecido, a busca é filtrada para as fazendas associadas a ele.
     */
    public function getTotalRationConsumption(?Veterinarian $veterinarian = null): float
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COALESCE(SUM(c.racao), 0)')
            ->where('c.status = :status')
            ->setParameter('status', Cow::STATUS_VIVO);

        if ($veterinarian) {
            $qb->innerJoin('c.fazenda', 'f')
               ->innerJoin('f.veterinarios', 'v')
               ->andWhere('v = :vet')
               ->setParameter('vet', $veterinarian);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Encontra vacas jovens que consomem muita ração.
     * Se um veterinário for fornecido, a busca é filtrada.
     */
    public function findYoungHeavyEaters(?Veterinarian $veterinarian = null): array
    {
        $oneYearAgo = new \DateTime('-1 year');
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.fazenda', 'f')->addSelect('f')
            ->where('c.status = :status')
            ->andWhere('c.nascimento >= :oneYearAgo')
            ->andWhere('c.racao > 500')
            ->setParameter('status', Cow::STATUS_VIVO)
            ->setParameter('oneYearAgo', $oneYearAgo);

        if ($veterinarian) {
            // A junção com 'fazenda' já foi feita, só precisamos adicionar a de 'veterinarios'.
            $qb->innerJoin('f.veterinarios', 'v')
               ->andWhere('v = :vet')
               ->setParameter('vet', $veterinarian);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Encontra vacas já abatidas.
     * Se um veterinário for fornecido, a busca é filtrada.
     */
    public function findSlaughtered(?Veterinarian $veterinarian = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.fazenda', 'f')->addSelect('f')
            ->where('c.status = :status')
            ->setParameter('status', Cow::STATUS_ABATIDO)
            ->orderBy('c.codigo', 'ASC');

        if ($veterinarian) {
            $qb->innerJoin('f.veterinarios', 'v')
               ->andWhere('v = :vet')
               ->setParameter('vet', $veterinarian);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Conta o número total de vacas vivas.
     * Se um veterinário for fornecido, a contagem é filtrada.
     */
    public function countForVeterinarian(?Veterinarian $veterinarian = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Cow::STATUS_VIVO);
            
        if ($veterinarian) {
            $qb->innerJoin('c.fazenda', 'f')
               ->innerJoin('f.veterinarios', 'v')
               ->andWhere('v = :vet')
               ->setParameter('vet', $veterinarian);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Encontra vacas que atendem aos critérios para abate.
     * (Este método não precisava de filtro de veterinário no seu código original, mas pode ser adicionado se necessário).
     */
    public function findForSlaughter(): array
    {
        $fiveYearsAgo = new \DateTime('-5 years');
        $eighteenArrobasInKg = 18 * 15;

        return $this->createQueryBuilder('c')
            ->leftJoin('c.fazenda', 'f')->addSelect('f')
            ->where('c.status = :status')
            ->andWhere('c.nascimento <= :fiveYearsAgo OR c.leite < 40 OR c.peso > :maxWeight')
            ->setParameter('status', Cow::STATUS_VIVO)
            ->setParameter('fiveYearsAgo', $fiveYearsAgo)
            ->setParameter('maxWeight', $eighteenArrobasInKg)
            ->orderBy('c.codigo', 'ASC')
            ->getQuery()->getResult();
    }
}