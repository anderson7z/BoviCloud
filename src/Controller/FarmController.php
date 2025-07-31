<?php

namespace App\Controller;

use App\Entity\Farm;
use App\Form\FarmType;
use App\Repository\FarmRepository;
use App\Repository\CowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/farm')]
final class FarmController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }


    #[Route('/', name: 'app_farm_index', methods: ['GET'])]
    public function index(
        FarmRepository $farmRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $query = $farmRepository->createQueryBuilder('f')
            ->orderBy('f.nome', 'ASC');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('farm/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }


    #[Route('/new', name: 'app_farm_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $farm = new Farm();
        $form = $this->createForm(FarmType::class, $farm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($farm);
            $this->entityManager->flush();

            $this->addFlash('success', 'Fazenda cadastrada com sucesso!');
            return $this->redirectToRoute('app_farm_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('farm/new.html.twig', [
            'farm' => $farm,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_farm_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(
        Farm $farm,
        CowRepository $cowRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
       
        $cowsQuery = $cowRepository->createQueryBuilder('c')
            ->where('c.fazenda = :farm') 
            ->setParameter('farm', $farm)
            ->orderBy('c.codigo', 'ASC');

        $cowsPagination = $paginator->paginate(
            $cowsQuery,
            $request->query->getInt('page', 1),
            10
        );

       
        return $this->render('farm/show.html.twig', [
            'farm' => $farm,
            'cowsPagination' => $cowsPagination,
        ]);
    }

    
    #[Route('/{id}/edit', name: 'app_farm_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Farm $farm): Response
    {
        $form = $this->createForm(FarmType::class, $farm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Dados da fazenda atualizados com sucesso!');
            return $this->redirectToRoute('app_farm_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('farm/edit.html.twig', [
            'farm' => $farm,
            'form' => $form,
        ]);
    }

 
    #[Route('/{id}', name: 'app_farm_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Farm $farm): Response
    {
        if (!$farm->getCows()->isEmpty()) {
            $this->addFlash('danger', 'Não é possível excluir uma fazenda que possui animais cadastrados.');
            return $this->redirectToRoute('app_farm_index');
        }

        if ($this->isCsrfTokenValid('delete'.$farm->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($farm);
            $this->entityManager->flush();
            $this->addFlash('warning', 'Fazenda excluída com sucesso.');
        }

        return $this->redirectToRoute('app_farm_index', [], Response::HTTP_SEE_OTHER);
    }
}