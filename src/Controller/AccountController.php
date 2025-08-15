<?php


namespace App\Controller;

use App\Entity\PasswordResetRequest;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\PasswordResetRequestRepository;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
      MailerInterface $mailer
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

      // Exemple d'envoi d'email (désactivé ici)
      /*
      $email = (new Email())
          ->from('donotreply@monflix.com')
          ->to($form->get('email')->getData())
          ->subject('Votre compte MonFlix est bien créé.')
          ->text('Bonjour ' . $form->get('firstname')->getData() . ', ...');
      $mailer->send($email);
      */

      return $this->redirectToRoute('home');
    }

    // Si pas de redirection, on retourne null
    return null;
  }


  #[Route('/signin', name: 'acc_signin')]
  public function signin(
      Request $request,
      UserPasswordHasherInterface $passwordHasher,
      EntityManagerInterface $entityManager,
      MailerInterface $mailer
  ): Response {
    if ($redirect = $this->log_sign_in($request, $passwordHasher, $entityManager, $mailer)) {
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
      MailerInterface $mailer
  ): Response {
    if ($redirect = $this->log_sign_in($request, $passwordHasher, $entityManager, $mailer)) {
      return $redirect;
    }

    return $this->render('security/login.html.twig', [
        'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView(),
        'mode' => 'login'
    ]);
  }



    #[Route('/send_reset_email', name: 'acc_send_email_reset')]
    public function request(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {

                $token = bin2hex(random_bytes(32));

                $resetRequest = new PasswordResetRequest();
                $resetRequest->setUser($user);
                $resetRequest->setToken($token);
                $resetRequest->setCreatedAt(new \DateTime());


                $entityManager->persist($resetRequest);
                $entityManager->flush();


                $emailMessage = (new Email())
                    ->from('donotreply@monflix.com')
                    ->to($email)
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html(
                        $this->renderView('security/mail_to_send_to_reset.html.twig', [
                            'token' => $token,
                            'user' => $user,
                        ])
                    );

                $mailer->send($emailMessage);
            }


            // @todo MAUVAIS EMAIL, A FAIRE UNE PAGE OU UNE ERREUR
            return $this->redirectToRoute('home');
        }

        return $this->render('security/send_email.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/reset_password/{token}', name: 'acc_reset_password')]
    public function resetPassword(
        Request $request,
        PasswordResetRequestRepository $resetRequestRepository,
        string $token,
        EntityManagerInterface $entityManager
    ): Response
    {
        $resetRequest = $resetRequestRepository->findOneBy(['token' => $token]);

        if (!$resetRequest || $resetRequest->getCreatedAt() < new \DateTime('-1 hour')) {

            return new Response("Token expiré");
        }

        $form = $this->createForm(ResetPasswordFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $resetRequest->getUser();
            $password = $form->get('plainPassword')->getData();
            $user->setPassword(password_hash($password, PASSWORD_BCRYPT));

            $entityManager->flush();


            $entityManager->remove($resetRequest);
            $entityManager->flush();

            return $this->redirectToRoute('acc_signin');
        }

        return $this->render('security/pwd_reset.html.twig', [
            'form' => $form->createView(),
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

    #[Route('/update_profile', name: 'acc_update_profile')]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function update_profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {

        $user = $this->getUser();

        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($hashedPassword);


            $entityManager->persist($user);
            $entityManager->flush();


            return $this->redirectToRoute('acc_profile');
        }

        return $this->render('security/profile_update.html.twig', [
            'registrationForm' => $form->createView(),
        ]);

    }






}