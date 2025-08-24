<?php


namespace App\Controller;

use App\Entity\Attack;
use App\Entity\ParamRequest;
use App\Entity\PasswordResetRequest;
use App\Entity\Post;
use App\Form\CellIndexType;

use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\PasswordResetRequestRepository;
use App\Repository\UserRepository;
use Carbon\Carbon;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;



class HomeController extends AbstractController
{

  #[Route('/', name: 'todo')]
  public function todo(): \Symfony\Component\HttpFoundation\RedirectResponse
  {
    return $this->redirectToRoute('home');
  }

  #[Route('/home', name: 'home')]
  public function home(
      EntityManagerInterface $entityManager,

  ): Response
  {
      $user = $this->getUser();
      $tweets = $entityManager->getRepository(Post::class)->findAll();

      return $this->render('home.html.twig', [
          'entityManager' => $entityManager,
          'tweets' => $tweets,
          'user' => $user
      ]);

  }




  #[Route('/legal-notice', name: 'legal_notice')]
  public function legalNotice(): ?Response
  {
    return $this->render('./legal_notice.html.twig');
  }

  #[Route('/confidentiality', name: 'confidentiality')]
  public function confidentiality(): ?Response
  {
    return $this->render('./security/confidentiality.html.twig');
  }


}