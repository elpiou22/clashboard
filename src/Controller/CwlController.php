<?php

namespace App\Controller;

use App\Entity\Attack;
use App\Entity\Clan;
use App\Entity\ParamRequest;
use App\Entity\Post;
use App\Form\CellIndexType;
use App\Service\CocRequests;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CwlController extends AbstractController
{
  #[Route('/cwl', name: 'cwl_home')]
  public function cwl_home(): Response
  {
    return $this->render('./cwl/home_cwl.html.twig');
  }

  private function normTag(?string $t): string
  {
    if (!$t) return '';
    $t = ltrim($t, "#");
    return strtoupper($t); // ou laisse tel quel si tu préfères
  }

  public function check_and_store_data(
      $clanID,
      $jsonRules,
      $date,
      EntityManagerInterface $entityManager,
  ): array|Response {
    $nodeScriptPath = realpath('../ExcelApoloBot-main/js/app.js');
    $command = "node " . escapeshellarg($nodeScriptPath) . " " . $clanID . " " . $jsonRules . " " . date('y') . date('m');
    //dd($command);
    $output = shell_exec($command); // Exécution du script
    //dd($output);
    if ($output === null) {
      return new Response('Le script n\'a pas produit de sortie ou a échoué.', 500);
    }

    if (str_starts_with($output, 'error')) {
      return new Response('Une erreur s\'est produite : ' . $output, 500);
    }

    $data = json_decode($output, true);
    $membersInCWL = $data[0];
    $excelData    = $data[1];
    $clanName     = $data[2];
    //$data_length  = count($excelData);

    $clanRepo = $entityManager->getRepository(Clan::class);
    $clan = $clanRepo->findOneBy(['clan_id' => $clanID]);

    if (!$clan) {
      $clan = new Clan();
      $clan->setClanId($clanID);
      $clan->setName($clanName ?? 'Unknown');
      $entityManager->persist($clan);
      // on flush ici pour garantir un id pour la FK + pouvoir l’utiliser dans la requête DQL suivante
      $entityManager->flush();
    } else {
      // on met à jour le nom si on en reçoit un de plus frais
      if (!empty($clanName) && $clan->getName() !== $clanName) {
        $clan->setName($clanName);
        $entityManager->persist($clan);
        $entityManager->flush();
      }
    }

    // 4) Charger les attaques existantes pour ce clan et cette date
    $attackRepo = $entityManager->getRepository(Attack::class);
    $existingAttacks = $attackRepo->createQueryBuilder('a')
        ->where('a.clan = :clan')
        ->andWhere('a.date = :date')
        ->setParameter('clan', $clan)
        ->setParameter('date', $date)
        ->getQuery()
        ->getResult();

    // index [pseudo][day] => Attack
    $existingIndex = [];
    foreach ($existingAttacks as $attack) {
      // 15/10/25 - deb
      //$existingIndex[$attack->getPseudo()][$attack->getDay()] = $attack;
      $key = $this->normTag($attack->getTag());
      if ($key === '') { $key = $attack->getPseudo(); }
      $existingIndex[$key][$attack->getDay()] = $attack;
      // 15/10/25 - fin
    }

    $batchSize = 50;
    $pending   = 0;

    foreach ($excelData as $i => $dayData) {
      $dayNumber = $i + 1;


      foreach ($dayData as $contest) {
        // expected keys: playerName, bonusValue, mapPosition, tag, attackerTH, defenderTH, percentage, attackStars
        $playerName  = $contest['playerName']  ?? '';
        $bonusValue  = $contest['bonusValue']  ?? '';
        $rawTag      = $contest['tag']         ?? '';// 15/10/2025
        $tag         = $this->normTag($rawTag);// 15/10/2025
        $mapPosition = $contest['mapPosition'] ?? 0;

        if ($playerName === '') {
          continue; // on ignore les lignes vides
        }

        $key = $tag !== '' ? $tag : $playerName;

        //if (isset($existingIndex[$playerName][$dayNumber])) {
        if (isset($existingIndex[$key][$dayNumber])) { // 15/10/25
          // MAJ si nécessaire
          //$attack = $existingIndex[$playerName][$dayNumber];// 15/10/25
          $attack = $existingIndex[$key][$dayNumber];

          if ($attack->getTag() !== $tag && $tag !== '') {
            $attack->setTag($tag);
            $needPersist = true;
          }

          $needPersist = false;

          if ($attack->getResult() !== $bonusValue) {
            $attack->setResult($bonusValue);
            $needPersist = true;
          }
          if ($attack->getMapPosition() !== (int)$mapPosition) {
            $attack->setMapPosition((int)$mapPosition);
            $needPersist = true;
          }


          if (array_key_exists('tag', $contest) && $attack->getTag() !== $contest['tag']) {
            $attack->setTag($contest['tag']);
            $needPersist = true;
          }
          if (array_key_exists('attackerTH', $contest) && $attack->getAttackerTH() !== (int)$contest['attackerTH']) {
            $attack->setAttackerTH((int)$contest['attackerTH']);
            $needPersist = true;
          }
          if (array_key_exists('defenderTH', $contest) && $attack->getDefenderTH() !== (int)$contest['defenderTH']) {
            $attack->setDefenderTH((int)$contest['defenderTH']);
            $needPersist = true;
          }
          if (array_key_exists('percentage', $contest) && $attack->getPercentage() !== (int)$contest['percentage']) {
            $attack->setPercentage((int)$contest['percentage']);
            $needPersist = true;
          }
          if (array_key_exists('attackStars', $contest) && $attack->getAttackStars() !== (int)$contest['attackStars']) {
            $attack->setAttackStars((int)$contest['attackStars']);
            $needPersist = true;
          }

          if ($needPersist) {
            $entityManager->persist($attack);
            $pending++;
          }
        } else {
          // Création
          $attack = new Attack();
          $attack->setPseudo($playerName);
          $attack->setTag($tag ?: null); // 15/10/25
          $attack->setDate($date);
          $attack->setDay($dayNumber);
          $attack->setResult($bonusValue);
          $attack->setMapPosition((int)$mapPosition);
          $attack->setClan($clan);

          // champs forum
          if (isset($contest['tag']))         { $attack->setTag($contest['tag']); }
          if (isset($contest['attackerTH']))  { $attack->setAttackerTH((int)$contest['attackerTH']); }
          if (isset($contest['defenderTH']))  { $attack->setDefenderTH((int)$contest['defenderTH']); }
          if (isset($contest['percentage']))  { $attack->setPercentage((int)$contest['percentage']); }
          if (isset($contest['attackStars'])) { $attack->setAttackStars((int)$contest['attackStars']); }

          $entityManager->persist($attack);
          $pending++;

          // on enrichit l'index en mémoire pour éviter doublons dans la même passe
          //$existingIndex[$playerName][$dayNumber] = $attack; // 15/10/25
          $existingIndex[$key][$dayNumber] = $attack;
        }

        if ($pending >= $batchSize) {
          $entityManager->flush();
          //$entityManager->clear(); // si tu clears, pense à re-récupérer $clan !
          $clan = $clanRepo->findOneBy(['clan_id' => $clanID]);
          $pending = 0;
        }
      }
    }

    if ($pending >= $batchSize) {
      $entityManager->flush();
      $pending = 0;
    }

    if ($pending > 0) {
      $entityManager->flush();
    }

    return [$membersInCWL, $excelData];
  }


  #[\Symfony\Component\Routing\Attribute\Route('/bonusdata', name: 'view_cwl_bonus_data', methods: ['POST'])]
  public function bonusdata_submit(
      Request $request,
      EntityManagerInterface $entityManager,
      CocRequests $coc
  ): ?Response
  {


    $stars = $request->request->all('stars');
    $logicalOperators = $request->request->all('logical_operator');
    $comparisonOperators = $request->request->all('comparison_operator');
    $results = $request->request->all('result');

    $exceptions_stars = $request->request->all('exceptions_stars');
    $exceptions_thA = $request->request->all('exceptions_townhalllevels_A');
    $exceptions_thD = $request->request->all('exceptions_townhalllevels_D');
    $exceptions_results = $request->request->all('exceptions_result');

    //dump($stars, $logicalOperators, $comparisonOperators, $results); // Pour debug

    $classical_rules =[];
    for ($i = 0; $i < count($stars); $i++) {
      if ($stars[$i] == " "){
        $star = "null";
      }else{
        $star = $stars[$i];
      }

      if ($logicalOperators[$i] == " "){
        $logicalOperator = "null";
      }else{
        $logicalOperator = $logicalOperators[$i];
      }
      if ($comparisonOperators[$i] == " "){
        $comparisonOperator = "null";
      }else{
        $comparisonOperator = $comparisonOperators[$i];
      }
      $result = $results[$i];
      $classical_rules[] = [$star, $logicalOperator, $comparisonOperator, $result];
    }

    $exceptions_rules = [];
    for ($i = 0; $i < count($exceptions_stars); $i++) {
      $star = $exceptions_stars[$i];
      $thA = $exceptions_thA[$i];
      $thD = $exceptions_thD[$i];
      $result = $exceptions_results[$i];
      $exceptions_rules[] = [$star, $thA, $thD, $result];
    }
    $rules = [$classical_rules, $exceptions_rules];

    $jsonRules = json_encode($rules);
    $clanID = $request->request->get('clan_id');

    if ($clanID[0] === "#") {
      $clanID = substr($clanID, 1);
    }
    if (!$clanID) {
      return new Response('Le clan ID est manquant.', 400); // //@todo : vérifier si le clan ID est présent.
    }

    // 24/08/2025 - On vérifie si on est dans les bonnes dates pour générer les valeurs. Si non -> on affiche une popup indiquant que ce n'est pas possible mais on leur montre les vielles données pour l'aperçu du site
    $status = $coc->getStatusCwl($clanID);
    if (!$status['ok']) {
      if ($this->getParameter('kernel.debug')) {
        //dump($status['raw']);
      }
      if ($status['raw']['status'] == 404){
        //dump($status['raw']);
        $dateKey = date('y') . date('m');
        $result = $this->check_and_store_data("P990YPPV", $jsonRules, $dateKey, $entityManager);

        return $this->redirectToRoute('view_cwl_bonus_data_show', [
          'clanId' => $clanID,
          'popup_active' => true,
          'message' => $status['raw']['message']
        ]);
      }
      $this->addFlash('error', $status['human']);
      return $this->redirectToRoute('cwl_home');
    } else {
      $last_request = $entityManager->getRepository(ParamRequest::class)->findOneBy([
          'clanId' => $clanID,
          'date' => date('y') . date('m'),
      ]);

      $new_request = True;
      if (!$last_request) {
        $last_request = new ParamRequest();
        $last_request->setClanId($clanID);
        $last_request->setDate(date('y') . date('m'));
        $last_request->setParameters($jsonRules);
        $new_request = True;
      } else {
        if ($last_request->getParameters() !== $jsonRules) {
          $last_request->setParameters($jsonRules);
          $new_request = True;
        } else {
          $new_request = False;
        }
      }
      $entityManager->persist($last_request);
      $entityManager->flush();

      if ($new_request) {
        $dateKey = date('y') . date('m');;
        $result = $this->check_and_store_data($clanID, $jsonRules, $dateKey, $entityManager);

        if ($result instanceof Response) {
          return $result;
        }
      }
      return $this->redirectToRoute('view_cwl_bonus_data_show', [
          'clanId' => $clanID
      ]);
    }




  }




  #[Route('/bonusdata/{clanId}', name: 'view_cwl_bonus_data_show', methods: ['GET'])]
  public function bonusdata_show(
      string $clanId,
      EntityManagerInterface $em,
      Request $request,
  ): Response
  {
    $dateKey = date('y') . date('m');;

    $clan = $em->getRepository(Clan::class)->findOneBy(['clan_id' => $clanId]);
    if (!$clan) {
      // rien en base → page vide (ou redir /cwl)
      return $this->render('./cwl/view_cwl.html.twig', [
          'membersInCWL'   => [],
          'length_members' => 0,
          'data_All_Players' => [],
          'form'           => $this->createForm(CellIndexType::class)->createView(),
          'clanId'         => $clanId,
          'clanName'       => 'Undefined',
      ]);
    }

/* 15/10/25
    $pseudos = $em->getRepository(Attack::class)->createQueryBuilder('a')
        ->select('DISTINCT a.pseudo, a.mapPosition')
        ->where('a.clan = :clan')->andWhere('a.date = :date')
        ->setParameter('clan', $clan)->setParameter('date', $dateKey)
        ->orderBy('a.mapPosition', 'ASC')
        ->getQuery()->getArrayResult();
    $membersInCWL = array_map(static fn(array $row) => $row['pseudo'], $pseudos);
*/

    // Attaques du clan + date
    $attacks = $em->getRepository(Attack::class)->findBy(
        ['clan' => $clan, 'date' => $dateKey],
        ['mapPosition' => 'ASC', 'day' => 'ASC']
    );

    // Groupement par joueur + tri par mapPosition
    $byPlayer = [];
    $displayNameByKey = [];
    $mapPosByKey = [];
    foreach ($attacks as $attack) {
      //$byPlayer[$attack->getPseudo()][] = $attack; // 15/10/25

      $key = $this->normTag($$attack->getTag());
      if ($key === '') { $key = $attack->getPseudo(); }

      $byPlayer[$key][] = $attack;
      $displayNameByKey[$key] = $attack->getPseudo() ?: ($displayNameByKey[$key] ?? $key);
      if (!isset($mapPosByKey[$key])) {
        $mapPosByKey[$key] = (int)$attack->getMapPosition();
      }
    }

    uksort($byPlayer, function ($ka, $kb) use ($mapPosByKey, $displayNameByKey) {
      $cmp = $mapPosByKey[$ka] <=> $mapPosByKey[$kb];
      return $cmp !== 0 ? $cmp : strcasecmp($displayNameByKey[$ka], $displayNameByKey[$kb]);
    });

    /* 15/10/25
    uksort($byPlayer, function (string $a, string $b) use ($byPlayer) {
      return $byPlayer[$a][0]->getMapPosition() <=> $byPlayer[$b][0]->getMapPosition();
    });
    */


    $popupActive  = $request->query->getBoolean('popup_active', false);
    $popupMessage = (string) $request->query->get('message', '');


    $byPlayer = [];               // key => Attack[]
    $displayNameByKey = [];       // key => string (pseudo à afficher)
    $mapPosByKey = [];            // key => int (pour trier)

    /* 15/10/25
    foreach ($attacks as $a) {
      $key = $a->getTag() ?: $a->getPseudo(); // clé unique
      $byPlayer[$key][] = $a;

      // on préfère le pseudo le plus récent rencontré
      $displayNameByKey[$key] = $a->getPseudo() ?: $displayNameByKey[$key] ?? $key;

      // mapPosition pour trier les joueurs (prend la première connue)
      if (!isset($mapPosByKey[$key])) {
        $mapPosByKey[$key] = (int)$a->getMapPosition();
      }
    }


// Ordre d’affichage par mapPosition, puis alpha
    uksort($byPlayer, function (string $ka, string $kb) use ($mapPosByKey, $displayNameByKey) {
      $cmp = ($mapPosByKey[$ka] <=> $mapPosByKey[$kb]);
      return $cmp !== 0 ? $cmp : strcasecmp($displayNameByKey[$ka], $displayNameByKey[$kb]);
    });

    $players = [];
    foreach (array_keys($byPlayer) as $k) {
      $players[] = ['key' => $k, 'name' => $displayNameByKey[$k]];
    }
    */
    $players = [];
    foreach (array_keys($byPlayer) as $k) {
      $players[] = ['key' => $k, 'name' => $displayNameByKey[$k]];
    }


    return $this->render('./cwl/view_cwl.html.twig', [
        'players'          => $players,              // 15/10/2025
        //'membersInCWL'     => $membersInCWL,
        //'length_members'   => max(count($membersInCWL) - 1, 0),
        'length_members'   => max(count($players) - 1, 0),
        'data_All_Players' => $byPlayer,
        'form'             => $this->createForm(CellIndexType::class)->createView(),
        'clanId'           => $clanId,
        'clanName'         => $clan->getName() ?? 'Undefined',
        'cwlDate'         => $dateKey,
        'popup_active'  => $popupActive,
        'message' => $popupMessage,
    ]);
  }










  #[\Symfony\Component\Routing\Attribute\Route('forum/{clanId}/{url}', name: 'contest')]
  public function cwl_contest(
      Request $request,
              $clanId,
              $url,
      EntityManagerInterface $entityManager,
  ): Response
  {

    $date = substr($url, 0, 4);
    $mapPosition = substr($url, 4, 2);
    $day = substr($url, 6, 1);

    $clan = $entityManager->getRepository(Clan::class)->findOneBy([
        'clan_id' => $clanId
    ]);


    if (!$clan) {
      throw $this->createNotFoundException("Clan $clanId introuvable");
    }

    $attack = $entityManager->getRepository(Attack::class)->findOneBy([
        'clan' => $clan,
        'date' => $date,
        'mapPosition' => $mapPosition,
        'day' => $day
    ]);



    $posts = $entityManager->getRepository(Post::class)->findBy([
        'clan' => $clan,
        'day' => $day,
        'playerMapPosition' => $mapPosition,
        'reply_of' => 0,
    ],
        [
            'vote_weight' => 'DESC',  // priorité sur le vote
            'date' => 'ASC',   // puis date
        ]);
    $postIds = array_map(fn($p) => $p->getId(), $posts);
    $replies = $entityManager->getRepository(Post::class)->findBy([
        'clan' => $clan,
        'day' => $day,
        'playerMapPosition' => $mapPosition,
        'reply_of' => $postIds,
    ],
        [
            'vote_weight' => 'DESC',  // priorité sur le vote
            'date' => 'ASC',   // puis date
        ]);
    $replies_replyIds = array_map(fn($p) => $p->getReplyOf(), $replies);


    $clanName = $clan->getName();
    $clanInfos = $clanName . " (#". $clanId . ")";

    $attackInfos = "th" . $attack->getAttackerTH(). " vs ". "th". $attack->getDefenderTH() . "\n " .$attack->getAttackStars() . " ⭐ - " . $attack->getPercentage() . "%";

    return $this->render('./cwl/forum_cwl.html.twig', ['attack' => $attack,
        'posts' => $posts,
        'replies' => $replies,
        'clanInfos' => $clanInfos,
        'attackInfos' => $attackInfos,
        'attackResult' => $attack->getResult(),
        'attackerName' => $attack->getPseudo(),
        'attackDay' => $day,
        'replies_replyIds' => $replies_replyIds,
        'entityManager' => $entityManager
    ]);
  }


}
