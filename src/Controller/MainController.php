<?php

namespace App\Controller;

use App\Repository\CowRepository;
use App\Repository\FarmRepository;
use App\Repository\VeterinarianRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(
        Request $request,
        CowRepository $cowRepository,
        FarmRepository $farmRepository,
        VeterinarianRepository $veterinarianRepository
    ): Response {

        $vetId = $request->query->get('vet_id');
        $selectedVet = $vetId ? $veterinarianRepository->find($vetId) : null;

      
        $data = [
            'totalMilk' => $cowRepository->getTotalMilkProduction($selectedVet),
            'totalRation' => $cowRepository->getTotalRationConsumption($selectedVet),
            'youngHeavyEaters' => $cowRepository->findYoungHeavyEaters($selectedVet),
            'slaughteredCows' => $cowRepository->findSlaughtered($selectedVet),
            'totalFarms' => $farmRepository->countForVeterinarian($selectedVet),
            'totalCows' => $cowRepository->countForVeterinarian($selectedVet),
        ];

  
        $data['selected_vet'] = $selectedVet;
        $data['all_veterinarians'] = $veterinarianRepository->findBy([], ['nome' => 'ASC']);

        return $this->render('main/index.html.twig', $data);
    }
    #[Route('/developer', name: 'app_developer_info')]
    public function developerInfo(): Response
    {
       
        return $this->render('main/developer.html.twig');
    }
}