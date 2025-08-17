<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PasswordResetRequest;
use App\Repository\UserRepository;
use App\Repository\PasswordResetRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class PasswordResetController extends AbstractController
{
  public function __construct(
      private EntityManagerInterface $em
  ) {}


  #[Route('/send_reset_email', name: 'password_request', methods: ['GET','POST'])]
  public function request(
      Request $request,
      UserRepository $users,
      PasswordResetRequestRepository $resets,
      MailerInterface $mailer
  ): Response {
    if ($request->isMethod('POST')) {
      $email = trim((string) $request->request->get('email'));
      $user = $users->findOneBy(['email' => $email]);

      $genericFlash = 'Si un compte existe pour cet email, un message vient de vous être envoyé.';

      // Anti-abus (throttle simple): ne pas créer 50 demandes actives
      if ($user) {
        // invalider les anciennes demandes encore valides
        $now = new \DateTimeImmutable();
        $active = $resets->createQueryBuilder('r')
            ->andWhere('r.user = :u')
            ->andWhere('r.usedAt IS NULL')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('u', $user)
            ->setParameter('now', $now)
            ->getQuery()->getResult();

        foreach ($active as $req) {
          $req->setUsedAt($now); // on les “grille”
        }


        $selector = bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(20));
        $hashed = hash('sha256', $token);
        $expires = $now->modify('+90 minutes');

        $reset = new PasswordResetRequest();
        $reset->setUser($user);
        $reset->setSelector($selector);
        $reset->setHashedToken($hashed);
        $reset->setRequestedAt($now);
        $reset->setExpiresAt($expires);

        $this->em->persist($reset);
        $this->em->flush();

        $url = $this->generateUrl('password_check', [
            'selector' => $selector,
            'token'    => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $mail = (new Email())
            ->from(new Address('no-reply@clashboard.com', 'ClashBoard'))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html(
                "<p>Pour réinitialiser votre mot de passe, cliquez sur le lien ci-dessous :</p>
         <p><a href='{$url}'>{$url}</a></p>
         <p>Ce lien expire dans 90 minutes.</p>"
            );

        $mailer->send($mail);
      }

      $this->addFlash('success', $genericFlash);
      return $this->redirectToRoute('acc_login'); // ta page de login
    }

    // GET => petite page avec un champ email
    return $this->render('security/reset_request.html.twig');
  }



  // 2) Saisie du nouveau mot de passe après clic en email
  #[Route('/reset/{selector}/{token}', name: 'password_check', methods: ['GET','POST'])]

  public function reset(
      string $selector,
      string $token,
      Request $request,
      PasswordResetRequestRepository $resets,
      UserPasswordHasherInterface $hasher
  ): Response {
    $now = new \DateTimeImmutable();
    $reset = $resets->findOneBy(['selector' => $selector]);

    // validations de sécurité
    if (!$reset || $reset->getUsedAt() !== null || $reset->getExpiresAt() < $now) {
      $this->addFlash('danger', 'Lien invalide ou expiré.');
      return $this->redirectToRoute('acc_login');
    }

    // vérifier le token secret
    if (!hash_equals($reset->getHashedToken(), hash('sha256', $token))) {
      $this->addFlash('danger', 'Lien invalide.');
      return $this->redirectToRoute('acc_login');
    }

    // afficher formulaire de nouveau mot de passe
    if ($request->isMethod('POST')) {
      $plain = (string) $request->request->get('password');
      if (strlen($plain) < 8) {
        $this->addFlash('danger', 'Mot de passe trop court (min 8).');
        return $this->redirectToRoute('password_check', ['selector'=>$selector,'token'=>$token]);
      }

      $user = $reset->getUser();
      $user->setPassword($hasher->hashPassword($user, $plain));
      $reset->setUsedAt($now);          // marque la demande comme utilisée
      $this->em->flush();

      $this->addFlash('success', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
      return $this->redirectToRoute('acc_login');
    }

    return $this->render('security/reset_form.html.twig', [
        'selector' => $selector,
        'token' => $token,
    ]);
  }
}
