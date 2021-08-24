<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Room;
use App\Form\Admin\RoomType;
use App\Repository\Admin\RoomRepository;
use App\Repository\HotelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/room')]
class RoomController extends AbstractController
{
    #[Route('/', name: 'admin_room_index', methods: ['GET'])]
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('admin/room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'admin_room_new', methods: ['GET', 'POST'])]
    public function new(Request $request, $id, HotelRepository $hotelRepository, RoomRepository $roomRepository): Response
    {
        $rooms = $roomRepository->findBy(['hotelid' => $id]);

        $hotel =  $hotelRepository->findOneBy(['id' => $id]);

        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //************** file upload ***>>>>>>>>>>>>
            /** @var file $file */
            $file = $form['image']->getData();
            if ($file) {
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('images_directory'), // in Servis.yaml defined folder for upload images
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $room->setImage($fileName); // Related upload file name with Hotel table image field
            }
            //<<<<<<<<<<<<<<<<<******** file upload ***********>

            $entityManager = $this->getDoctrine()->getManager();

            $room->setHotelid($hotel->getId());
            //$room->setHotelid($id);

            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('admin_room_new', ['id' => $id]);
        }

        return $this->renderForm('admin/room/new.html.twig', [
            'room' => $room,
            'rooms' => $rooms,
            'form' => $form,
            'hotel' => $hotel,
        ]);
    }

    #[Route('/{id}', name: 'admin_room_show', methods: ['GET'])]
    public function show(Room $room): Response
    {
        return $this->render('admin/room/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/{id}/edit/{hid}', name: 'admin_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $hid, Room $room): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //************** file upload ***>>>>>>>>>>>>
            /** @var file $file */
            $file = $form['image']->getData();
            if ($file) {
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('images_directory'), // in Servis.yaml defined folder for upload images
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $room->setImage($fileName); // Related upload file name with Hotel table image field
            }
            //<<<<<<<<<<<<<<<<<******** file upload ***********>

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_room_new', ['id' => $hid], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/room/edit.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/{hid}', name: 'admin_room_delete', methods: ['POST'])]
    public function delete(Request $request, $hid, Room $room): Response
    {
        if ($this->isCsrfTokenValid('delete' . $room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_room_new', ['id' => $hid], Response::HTTP_SEE_OTHER);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
