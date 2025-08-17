<?php


namespace App\Controller;

use App\Entity\Attack;
use App\Entity\PasswordResetRequest;
use App\Entity\Post;
use App\Form\MovieType;
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
use App\Entity\Movie;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;



class PostController extends AbstractController
{


  #[Route('/post_create', name: 'post_create', methods: ['POST'])]
  public function create(
      Request $request,
      EntityManagerInterface $entityManager
  ): Response
  {

    // 15/08/2025 - Ajout PhpStan: "?? ''"
    $referrer           = $request->headers->get('referer') ?? '';
    $path               = parse_url($referrer, PHP_URL_PATH) ?? '';
    $segments           = explode('/', trim($path, '/')) ;

    $clanId             = $segments[1];
    $cwlId              = substr($segments[2], 0, 4);
    $playerMapPosition  = (int) substr($segments[2], 4, 2);
    $day                = (int) substr($segments[2], 6, 1);

    $content = $request->get('content');
    if(empty($content)){
        $this->addFlash('error', 'content empty');
        return $this->redirectToRoute('home');
    }
    $reply_of_id = $request->get('id');
    if($reply_of_id === null){
      $this->addFlash('error', '$reply_of_id empty');
      return $this->redirectToRoute('home');
    }
    $post = new Post();
    $post->setText((string)$content);
    $post->setDate(new \DateTime());
    $post->setAuthor(1); // @todo
    $post->setUpvote(0);
    $post->setDownvote(0);
    $post->setClanId($clanId);
    $post->setCwlId($cwlId);
    $post->setPlayerMapPosition($playerMapPosition);
    $post->setDay($day);
    // 13/08/2025 - gestion réponse aux posts
    $post->setReplyOf($reply_of_id !== null ? (int)$reply_of_id : 0);

    $entityManager->persist($post);

    $attack = $entityManager->getRepository(Attack::class)->findOneBy([
      'clanID' => $clanId,
      'date' => $cwlId,
      'day' => $day,
      'mapPosition' => $playerMapPosition,
    ]);
    if ($attack) {
      $attack->setNbPosts($attack->getNbPosts() + 1);
      $entityManager->persist($attack);
    }

    if ($reply_of_id != 0){
      $old_post = $entityManager->getRepository(Post::class)->findOneBy([
          'id' => $reply_of_id,
      ]);
      if ($old_post) {
        $old_post->addNbReplies();
        $entityManager->persist($old_post);
      }
    }
    //error_log(print_r(" ", true));
    $entityManager->flush();

    $this->addFlash('success', 'tweet created');

    return $this->redirectToRoute('contest', [
        'clanId' => $clanId,
        'url' => $segments[2],
    ]);
  }



  #[Route('/vote', name: 'vote', methods: ['POST'])]
  public function vote(
      Request $request,
      EntityManagerInterface $entityManager
  ): JsonResponse
  {
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
      return new JsonResponse(['error' => 'auth_required'], 401);
    }




    $data = json_decode($request->getContent(), true);
    $postId = $data['post_id'] ?? null;
    $voteType = $data['vote_type'] ?? null; // 'up' ou 'down'

    if (!$postId || !in_array($voteType, ['up', 'down'])) {
      return new JsonResponse(['error' => 'Invalid data'], 400);
    }

    $post = $entityManager->getRepository(Post::class)->find($postId);

    if (!$post) {
      return new JsonResponse(['error' => 'Post not found'], 404);
    }

    // Mise à jour du vote_weight
    if ($voteType === 'up') {
      $post->upvote();
    } else {
      $post->downvote();
    }

    $entityManager->persist($post);
    $entityManager->flush();

    return new JsonResponse([
        'postId' => $post->getId(),
        'vote_weight' => $post->getVoteWeight(),
    ]);
  }


  /**
   * Récupère les parents des parents des parents, en liste pour afficher une liste de replies
   *
   * @param Post $post
   * @param EntityManagerInterface $em
   * @return Post[]
   */
  private function getParentChain(Post $post, EntityManagerInterface $em): array
  {
    $chain = [];
    $current = $post;

    // remonte les parents
    while ($current->getReplyOf()) {
      $parent = $em->getRepository(Post::class)->find($current->getReplyOf());
      if (!$parent) break;
      array_unshift($chain, $parent); // parents du plus ancien au plus récent
      $current = $parent;
    }

    // ajoute le post courant à la fin
    $chain[] = $post;

    return $chain;
  }



  #[Route('post/{postId}', name: 'show_post')]
  public function show_post(
      Request $request,
      int $postId,
      EntityManagerInterface $entityManager
  ): Response
  {
    $post = $entityManager->getRepository(Post::class)->find($postId);
    if (!$post) {
      throw $this->createNotFoundException("Post not found");
    }

    // Récupère tous les parents
    $parentChain = $this->getParentChain($post, $entityManager);

    // Récupère les replies directes
    $replies = $entityManager->getRepository(Post::class)->findBy([
        'reply_of' => $post->getId(),
    ]);

    $clanId = $post->getClanId();
    $date   = $post->getCwlId();
    $firstarg = $post->getPlayerMapPosition();
    $secondarg = $post->getDay();

    return $this->render('post/show.html.twig', [
      'post' => $post,
      'parentChain' => $parentChain,
      'replies' => $replies,
      'last_post' => $postId,
      'entityManager' => $entityManager,

      'clanId' => $clanId,
      'date' => $date,
      'firstarg' => $firstarg,
      'secondarg' => $secondarg,

    ]);
  }



}
