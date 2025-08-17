<?php


namespace App\Controller;

use App\Entity\PasswordResetRequest;
use App\Form\ProfileFormType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\PasswordResetRequestRepository;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;



class AccountController extends AbstractController
{

    #[Route('/session-check', name: 'acc_session_check')]
    public function sessionCheck(): JsonResponse
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['status' => 'session_expired'], 401);
        }
        return new JsonResponse(['status' => 'session_active']);
    }



  private function log_sign_in(
      Request $request,
      UserPasswordHasherInterface $passwordHasher,
      EntityManagerInterface $entityManager,
      MailerInterface $mailer,
      Security $security
  ): ?Response {
    // 11/08/2025 - Si l'utilisateur est déjà connecté, on le redirige vers /home
    if ($this->getUser()) {
      return $this->redirectToRoute('home');
    }

    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $hashedPassword = $passwordHasher->hashPassword(
          $user,
          $form->get('password')->getData()
      );
      $user->setPassword($hashedPassword);
      $user->setRoles(['ROLE_USER']);

      $entityManager->persist($user);
      $entityManager->flush();

      $mail = (new Email())
          ->from(new Address('no-reply@clashboard.com', 'ClashBoard'))
          ->to($user->getEmail())
          ->subject('Bienvenue'. $user->getPseudo())
          ->html(
              "<p>Bonjour ". $user->getPseudo() . "</p>
                    <p>Votre compte est bien créé avec l'email:" . $user->getEmail() . "</p>"
          );

      $mailer->send($mail);
      $resp = $security->login(
          $user,
          'security.authenticator.form_login.main', // ← authenticator
          'main'                                    // ← firewall
      );

      return $resp ?? $this->redirectToRoute('home');
      //return $this->redirectToRoute('home');
    }
    return null;
  }


  #[Route('/signin', name: 'acc_signin')]
  public function signin(
      Request $request,
      UserPasswordHasherInterface $passwordHasher,
      EntityManagerInterface $entityManager,
      MailerInterface $mailer,
      Security $security
  ): Response {
    if ($redirect = $this->log_sign_in($request, $passwordHasher, $entityManager, $mailer, $security)) {
      return $redirect;
    }

    return $this->render('security/login.html.twig', [
        'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView(),
        'mode' => 'signin'
    ]);
  }

  #[Route('/login', name: 'acc_login')]
  public function login(
      Request $request,
      UserPasswordHasherInterface $passwordHasher,
      EntityManagerInterface $entityManager,
      MailerInterface $mailer,
      Security $security
  ): Response {
    if ($redirect = $this->log_sign_in($request, $passwordHasher, $entityManager, $mailer, $security)) {
      return $redirect;
    }

    return $this->render('security/login.html.twig', [
        'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView(),
        'mode' => 'login'
    ]);
  }





    #[Route('/profile', name: 'acc_profile')]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function profile(): Response
    {

        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('security/account.html.twig', [
            'user' => $user,
        ]);
    }

  #[Route('/update_profile', name: 'update_profile')]
  #[IsGranted("IS_AUTHENTICATED_FULLY")]
  public function update_profile(Request $request, EntityManagerInterface $em): Response
  {
    /** @var \App\Entity\User $user */
    $user = $this->getUser();

    $form = $this->createForm(\App\Form\ProfileFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      $em->flush();

      $this->addFlash('success', 'Profil mis à jour.');
      return $this->redirectToRoute('acc_profile');
    }

    return $this->render('security/profile_update.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
  }








}