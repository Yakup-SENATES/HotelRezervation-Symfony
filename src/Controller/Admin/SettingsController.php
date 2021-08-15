<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Settings;
use App\Form\Admin\SettingsType;
use App\Repository\Admin\SettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/settings')]
class SettingsController extends AbstractController
{
    #[Route('/', name: 'admin_settings_index', methods: ['GET'])]
    public function index(SettingsRepository $settingsRepository): Response
    {
        return $this->render('admin/settings/index.html.twig', [
            'settings' => $settingsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_settings_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $setting = new Settings();
        $form = $this->createForm(SettingsType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($setting);
            $entityManager->flush();

            return $this->redirectToRoute('admin_settings_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/settings/new.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_settings_show', methods: ['GET'])]
    public function show(Settings $setting): Response
    {
        return $this->render('admin/settings/show.html.twig', [
            'setting' => $setting,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_settings_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Settings $setting): Response
    {
        $form = $this->createForm(SettingsType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_settings_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/settings/edit.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_settings_delete', methods: ['POST'])]
    public function delete(Request $request, Settings $setting): Response
    {
        if ($this->isCsrfTokenValid('delete'.$setting->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($setting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_settings_index', [], Response::HTTP_SEE_OTHER);
    }
}
