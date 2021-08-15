<?php

namespace App\Controller;

use App\Repository\Admin\SettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SettingsRepository $settingsRepository): Response
    {

        $data = $settingsRepository->findBy(['id' => 1]);
        // $data = $settingsRepository->findAll();



        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'data' => $data,
        ]);
    }
}
