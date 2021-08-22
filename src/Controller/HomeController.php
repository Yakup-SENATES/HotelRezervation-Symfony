<?php

namespace App\Controller;

use App\Repository\Admin\SettingsRepository;
use App\Repository\HotelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ImageRepository;
use App\Entity\Hotel;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Admin\Messages;
use App\Entity\Admin\Settings;
use App\Form\Admin\MessagesType;
use SebastianBergmann\Environment\Console;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
//use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;



class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SettingsRepository $settingsRepository, HotelRepository $hotelRepository): Response
    {
        //data = hotel
        $data = $settingsRepository->findAll();
        // $data = $settingsRepository->findAll();
        $slider = $hotelRepository->findBy([], ['title' => 'ASC'], 3);
        $hotels = $hotelRepository->findBy([], ['title' => 'DESC'], 4);
        $count = 0;
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'slider' => $slider,
            'hotels' => $hotels,
            'data' => $data,
            'count' => $count,
        ]);
    }

    #[Route('/index2', name: 'index_2')]
    public function index2(SettingsRepository $settingsRepository, HotelRepository $hotelRepository): Response
    {
        //data = hotel
        $data = $settingsRepository->findAll();
        // $data = $settingsRepository->findAll();
        $slider = $hotelRepository->findBy([], ['title' => 'ASC'], 3);
        $hotels = $hotelRepository->findBy([], ['title' => 'DESC'], 4);
        $newhotels = $hotelRepository->findAll();

        return $this->render('home/index2.html.twig', [
            'controller_name' => 'HomeController',
            'hotels' => $hotels,
            'newhotels' => $newhotels,
            'slider' => $slider,
            'data' => $data,
        ]);
    }

    #[Route('/hotel/{id}', name: 'hotel_show')]
    public function show(Hotel $hotel, $id, ImageRepository $imageRepository): Response
    {
        $images = $imageRepository->findBy(['hotel' => $id]);

        return $this->render('home/hotelshow.html.twig', [
            'hotel' => $hotel,
            'images' => $images,

        ]);
    }


    #[Route('/about', name: 'home_about')]
    public function about(SettingsRepository $settingsRepository): Response
    {
        $settings = $settingsRepository->findAll();

        return $this->render('home/aboutus.html.twig', [
            'setting' => $settings,
        ]);
    }


    /*
    #[Route('/contact', name: 'home_contact')]
    public function contact(SettingsRepository $settingsRepository, Request $request, MailerInterface $mailer): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');
        $settings = $settingsRepository->findAll();

        if ($form->isSubmitted()) { //&&  $form->isValid()
            if ($this->isCsrfTokenValid('form-message', $submittedToken)) {

                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);

                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success', 'Mesajiniz Başariyla iletlmştir');

                //********** SEND EMAIL ***********************>>>>>>>>>>>>>>>
                $email = (new Email())
                    ->from($settings[0]->getSmtpemail())
                    ->to('your@gmail.com')
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject('AllHoliday Your Request')
                    //->text('Simple Text')
                    ->html(
                        "Dear " . $form['name']->getData() . "<br>
                                             <p>We will evaluate your requests and contact you as soon as possible</p> 
                                             Thank You for your message<br> 
                                             =====================================================
                                             <br>" . $settings[0]->getCompany() . "  <br>
                                             Address : " . $settings[0]->getAddress() . "<br>
                                             Phone   : " . $settings[0]->getPhone() . "<br>"
                    );

                try {
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    // some error prevented the email sending; display an
                    echo "<script>alert('same message');</script>";
                }

                //<<<<<<<<<<<<<<<<********** SEND EMAIL ***********************



                return $this->redirectToRoute('home_contact');
            }
        }
        $settings = $settingsRepository->findAll();

        return $this->render('home/contact.html.twig', [
            'setting' => $settings,
            'form' => $form->createView(),
        ]);
    }*/

    #[Route('/contact', name: 'home_contact')]
    public function contact(SettingsRepository $settingsRepository, Request $request, \Swift_Mailer $mailer): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');
        $settings = $settingsRepository->findAll();

        if ($form->isSubmitted()) { //&&  $form->isValid()
            if ($this->isCsrfTokenValid('form-message', $submittedToken)) {

                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);

                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success', 'Mesajiniz Başariyla iletlmştir');

                //********** SEND EMAIL ***********************>>>>>>>>>>>>>>>
                $message = (new \Swift_Message('Hello Email'))

                    ->setFrom($settings[0]->getSmtpemail())
                    ->setTo('yakup2@test.com')
                    ->setBody('Umarım basarılı olursun hayatta')

                    // you can remove the following code if you don't define a text version for your emails
                    ->addPart(
                        ''
                    );


                $mailer->send($message);


                //<<<<<<<<<<<<<<<<********** SEND EMAIL ***********************



                return $this->redirectToRoute('home_contact');
            }
        }
        $settings = $settingsRepository->findAll();

        return $this->render('home/contact.html.twig', [
            'setting' => $settings,
            'form' => $form->createView(),
        ]);
    }
}
