<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Form\ActividadType;
use App\Repository\ActividadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/actividad')]
#[IsGranted('ROLE_USER')]
final class ActividadController extends AbstractController
{
    #[Route(name: 'app_actividad_index', methods: ['GET'])]
    public function index(ActividadRepository $actividadRepository): Response
    {
        return $this->render('actividad/index.html.twig', [
            'actividads' => $actividadRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_actividad_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_TESORERO')) {
            throw $this->createAccessDeniedException('No tienes permiso para modificar actividades.');
        }

        $actividad = new Actividad();
        $form = $this->createForm(ActividadType::class, $actividad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($actividad);
            $entityManager->flush();
            $this->addFlash('success', 'Actividad creada correctamente.');

            return $this->redirectToRoute('app_actividad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('actividad/new.html.twig', [
            'actividad' => $actividad,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_actividad_show', methods: ['GET'])]
    public function show(Actividad $actividad): Response
    {
        return $this->render('actividad/show.html.twig', [
            'actividad' => $actividad,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_actividad_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Actividad $actividad, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_TESORERO')) {
            throw $this->createAccessDeniedException('No tienes permiso para modificar actividades.');
        }

        $form = $this->createForm(ActividadType::class, $actividad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Actividad actualizada correctamente.');

            return $this->redirectToRoute('app_actividad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('actividad/edit.html.twig', [
            'actividad' => $actividad,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_actividad_delete', methods: ['POST'])]
    public function delete(Request $request, Actividad $actividad, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_TESORERO')) {
            throw $this->createAccessDeniedException('No tienes permiso para modificar actividades.');
        }

        if ($this->isCsrfTokenValid('delete'.$actividad->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($actividad);
            $entityManager->flush();
            $this->addFlash('success', 'Actividad eliminada correctamente.');
        } else {
            $this->addFlash('error', 'No se pudo eliminar la actividad. Recarga la página e inténtalo de nuevo.');
        }

        return $this->redirectToRoute('app_actividad_index', [], Response::HTTP_SEE_OTHER);
    }
}
