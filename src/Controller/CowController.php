<?php

namespace App\Controller;

use App\Entity\Cow;
use App\Form\CowType;
use App\Repository\CowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cow')]
final class CowController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'app_cow_index', methods: ['GET'])]
    public function index(CowRepository $cowRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $cowRepository->createQueryBuilder('c')
            ->leftJoin('c.fazenda', 'f')->addSelect('f')
            ->where('c.status = :status')
            ->setParameter('status', Cow::STATUS_VIVO)
            ->orderBy('c.codigo', 'ASC');

        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('cow/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_cow_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $cow = new Cow();
        $form = $this->createForm(CowType::class, $cow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $farm = $cow->getFazenda();
            if ($farm) {
                $maxAnimals = $farm->getTamanho() * 18;
                if (count($farm->getCows()) >= $maxAnimals) {
                    $this->addFlash('danger', sprintf('A fazenda "%s" já atingiu o limite de %d animais.', $farm->getNome(), $maxAnimals));
                    return $this->render('cow/new.html.twig', ['cow' => $cow, 'form' => $form->createView()]);
                }
            }

            $this->entityManager->persist($cow);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Animal cadastrado com sucesso!');
            return $this->redirectToRoute('app_cow_index');
        }

        return $this->render('cow/new.html.twig', ['cow' => $cow, 'form' => $form]);
    }


    #[Route('/slaughter-list', name: 'app_cow_slaughter_list', methods: ['GET'])]
    public function slaughterList(CowRepository $cowRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $baseCandidates = $cowRepository->findForSlaughter();
        
        $rationCandidates = $cowRepository->createQueryBuilder('c')
            ->leftJoin('c.fazenda', 'f')->addSelect('f')
            ->where('c.status = :status')
            ->andWhere('c.leite < 70 AND (c.racao / 7) > 50')
            ->setParameter('status', Cow::STATUS_VIVO)
            ->getQuery()
            ->getResult();

        $allEligibleCows = array_unique(array_merge($baseCandidates, $rationCandidates), SORT_REGULAR);
        usort($allEligibleCows, fn(Cow $a, Cow $b) => $a->getCodigo() <=> $b->getCodigo());

 
        $pagination = $paginator->paginate(
            $allEligibleCows,
            $request->query->getInt('page', 1),
            10 // Itens por página
        );

       
        return $this->render('cow/slaughter_list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'app_cow_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Cow $cow): Response
    {
        return $this->render('cow/show.html.twig', [
            'cow' => $cow,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cow_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Cow $cow): Response
    {
        if ($cow->getStatus() !== Cow::STATUS_VIVO) {
            $this->addFlash('danger', 'Não é possível editar um animal que já foi abatido ou excluído.');
            return $this->redirectToRoute('app_cow_show', ['id' => $cow->getId()]);
        }
        
        $form = $this->createForm(CowType::class, $cow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Dados do animal atualizados com sucesso!');
            return $this->redirectToRoute('app_cow_show', ['id' => $cow->getId()]);
        }

        return $this->render('cow/edit.html.twig', [
            'cow' => $cow,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/slaughter', name: 'app_cow_slaughter', methods: ['POST'])]
    public function slaughter(Request $request, Cow $cow): Response
    {
        if (!$this->isCsrfTokenValid('slaughter'.$cow->getId(), $request->getPayload()->getString('_token'))) {
            throw new AccessDeniedHttpException('Token CSRF inválido.');
        }
        
        if ($cow->getStatus() !== Cow::STATUS_VIVO) {
            $this->addFlash('warning', 'Este animal não está mais vivo e não pode ser abatido.');
            return $this->redirectToRoute('app_cow_slaughter_list'); 
        }

        $isEligible = false;
        if ($cow->getNascimento() < new \DateTime('-5 years')) {
            $isEligible = true;
        } elseif ($cow->getLeite() < 40) {
            $isEligible = true;
        } elseif ($cow->getPeso() > 270) {
            $isEligible = true;
        } elseif ($cow->getLeite() < 70 && ($cow->getRacao() / 7) > 50) {
            $isEligible = true;
        }

        if ($isEligible) {
            $cow->setStatus(Cow::STATUS_ABATIDO);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('O animal de código %s foi enviado para o abate.', $cow->getCodigo()));
        } else {
            $this->addFlash('danger', 'No momento da ação, este animal não atendia mais aos critérios para abate.');
        }

        return $this->redirectToRoute('app_cow_slaughter_list');
    }

    #[Route('/{id}/delete', name: 'app_cow_delete', methods: ['POST'])]
    public function delete(Request $request, Cow $cow): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$cow->getId(), $request->getPayload()->getString('_token'))) {
            throw new AccessDeniedHttpException('Token CSRF inválido.');
        }

        if ($cow->getStatus() === Cow::STATUS_ABATIDO) {
            $this->addFlash('danger', 'Não é possível excluir um animal que já foi processado para abate.');
            return $this->redirectToRoute('app_cow_index');
        }

        $this->entityManager->remove($cow);
        $this->entityManager->flush();
        $this->addFlash('warning', sprintf('O registro do animal de código %s foi permanentemente excluído do sistema.', $cow->getCodigo()));
        return $this->redirectToRoute('app_cow_index');
    }
}