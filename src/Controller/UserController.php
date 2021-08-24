<?php

namespace App\Controller;


use App\Entity\Admin\Reservation;
use App\Form\Admin\ReservationType;
use App\Entity\User;
use App\Form\UserType;
use App\Form\Admin\CommentType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Admin\Comment;
use App\Repository\Admin\CommentRepository;
use App\Repository\Admin\ReservationRepository;
use App\Repository\Admin\RoomRepository;
use App\Repository\HotelRepository;
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/show.html.twig', []);
    }


    #[Route('/comments', name: 'user_comments', methods: ['GET'])]
    public function comments(CommentRepository $commentRepository): Response
    {

        $user = $this->getUser();
        $comments = $commentRepository->getAllCommentsUser($user->getId());

        return $this->render('user/comments.html.twig', [
            'comments' => $comments,

        ]);
    }

    #[Route('/hotels', name: 'user_hotels', methods: ['GET'])]
    public function hotels(): Response
    {
        return $this->render('user/hotels.html.twig', []);
    }

    #[Route('/reservations', name: 'user_reservations', methods: ['GET'])]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser(); // Get login User data
        // $reservations=$reservationRepository->findBy(['userid'=>$user->getId()]);
        $reservations = $reservationRepository->getUserReservation($user->getId());
        // dump($reservations);
        // die();
        return $this->render('user/reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }





    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

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

                $user->setImage($fileName); // Related upload file name with Hotel table image field
            }
            //<<<<<<<<<<<<<<<<<******** file upload ***********>

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'user_comment_delete', methods: ['POST'])]
    public function delete_comment(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_comment_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {

        return $this->render('user/show.html.twig', [
            'user' => $user,
            // 'password' => $user->getPassword(),
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, User $user): Response
    {
        $user = $this->getUser();

        if ($user->getId() != $id) {
            // echo ("Wron User ID") . "<br> " . ("Y0U Are Try hacK ... CAtCh Y0u !!");
            // die();
            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
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


    #[Route('/reservation/{id}', name: 'user_reservation_show', methods: ['GET'])]
    public function reservationshow($id, ReservationRepository $reservationRepository): Response
    {
        // $user = $this->getUser(); // Get login User data

        // $reservations=$reservationRepository->findBy(['userid'=>$user->getId()]);
        $reservation = $reservationRepository->getReservation($id);
        // dump($reservations);
        // die();
        return $this->render('user/reservation_show.html.twig', [
            'reservation' => $reservation,
        ]);
    }







    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/newcomment/{id}", name="user_new_comment", methods={"GET","POST"})
     */
    public function newcomment(Request $request, $id): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('comment', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setHotelid($id);
                $user = $this->getUser();
                $comment->setUserid($user->getId());

                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addFlash('success', 'Your comment has been sent successfuly');
                return $this->redirectToRoute('hotel_show', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('hotel_show', ['id' => $id]);
    }

    #[Route('/reservation/{hid}/{rid}', name: 'user_reservation_new', methods: ['GET', 'POST'])]
    public function newReservation(Request $request, $hid, $rid, HotelRepository $hotelRepository, RoomRepository $roomRepository): Response
    {
        $days = $_REQUEST['days'];
        $people = $_REQUEST['people'];
        $checkin = $_REQUEST['checkin'];

        $checkout = Date("Y-m-d H:i:s", strtotime($checkin . " $days Day")); // Adding days to date
        $checkin = Date("Y-m-d H:i:s", strtotime($checkin . " 0 Day"));


        $hotel = $hotelRepository->findOneBy(['id' => $hid]);
        $room = $roomRepository->findOneBy(['id' => $rid]);
        $total = $people * $days * $room->getPrice();

        $data['total'] = $total;
        $data['days'] = $days;
        $data['checkin'] = $checkin;
        $data['checkout'] = $checkout;


        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted() && $this->isCsrfTokenValid('form-reservation', $submittedToken)) {
            $entityManager = $this->getDoctrine()->getManager();

            $checkin = date_create_from_format("Y-m-d H:i:s", $checkin); //Convert to datetime format
            $checkout = date_create_from_format("Y-m-d H:i:s", $checkout); //Convert to datetime format



            $reservation->setCheckin($checkin);
            $reservation->setCheckout($checkout);

            $reservation->setStatus('New');
            $reservation->setIp($_SERVER['REMOTE_ADDR']);
            $reservation->setHotelid($hid);
            $reservation->setRoomid($rid);
            $user = $this->getUser();
            $reservation->setUserid($user->getId());
            $reservation->setDays($days);
            $reservation->setPeople($people);

            $reservation->setTotal($total);


            //$reservation->setCreatedAt(new));

            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('user_reservations');
        }

        return $this->renderForm('user/newreservation.html.twig', [
            'reservations' => $reservation,
            'room' => $room,
            'hotel' => $hotel,
            'form' => $form,
            'people' => $people,
            'data' => $data,
        ]);
    }
}
