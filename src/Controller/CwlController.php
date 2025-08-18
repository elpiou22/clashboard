<?php

namespace App\Controller;

use App\Entity\Attack;
use App\Entity\Clan;
use App\Entity\ParamRequest;
use App\Entity\Post;
use App\Form\CellIndexType;
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

  public function check_and_store_data(
      $clanID,
      $jsonRules,
      $date,
      EntityManagerInterface $entityManager,
  ): array|Response {
    $nodeScriptPath = realpath('../ExcelApoloBot-main/js/app.js');
    $command = "node " . escapeshellarg($nodeScriptPath) . " " . $clanID . " " . $jsonRules;
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
      $existingIndex[$attack->getPseudo()][$attack->getDay()] = $attack;
    }

    $batchSize = 50;
    $pending   = 0;

    foreach ($excelData as $i => $dayData) {
      $dayNumber = $i + 1;

      foreach ($dayData as $contest) {
        // expected keys: playerName, bonusValue, mapPosition, tag, attackerTH, defenderTH, percentage, attackStars
        $playerName  = $contest['playerName']  ?? '';
        $bonusValue  = $contest['bonusValue']  ?? '';
        $mapPosition = $contest['mapPosition'] ?? 0;

        if ($playerName === '') {
          continue; // on ignore les lignes vides
        }

        if (isset($existingIndex[$playerName][$dayNumber])) {
          // MAJ si nécessaire
          $attack = $existingIndex[$playerName][$dayNumber];

          $needPersist = false;

          if ($attack->getResult() !== $bonusValue) {
            $attack->setResult($bonusValue);
            $needPersist = true;
          }
          if ($attack->getMapPosition() !== (int)$mapPosition) {
            $attack->setMapPosition((int)$mapPosition);
            $needPersist = true;
          }

          // champs ajoutés (on met à jour si différents)
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
          $attack->setDate($date);            // ⚠ utilise bien le $date reçu en paramètre
          $attack->setDay($dayNumber);
          $attack->setResult($bonusValue);
          $attack->setMapPosition((int)$mapPosition);

          // liaison FK
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
          $existingIndex[$playerName][$dayNumber] = $attack;
        }

        if ($pending >= $batchSize) {
          $entityManager->flush();
          $entityManager->clear(); // si tu clears, pense à re-récupérer $clan !
          // ⚠️ après clear(), il faut ré-attacher $clan pour la suite
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
  public function displayData(
      Request $request,
      EntityManagerInterface $entityManager,
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

    // Vérifier si les données sont bien récupérées
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

    $last_request = $entityManager->getRepository(ParamRequest::class)->findOneBy([
        'clanId' => $clanID,
        'date' => "2501",
    ]);


    $new_request = True;
    if (!$last_request) {
      $last_request = new ParamRequest();
      $last_request->setClanId($clanID);
      $last_request->setDate("2501");
      $last_request->setParameters($jsonRules);
      $entityManager->persist($last_request);
      $entityManager->flush();
      $new_request = True;
    } else {
      if ($last_request->getParameters() !== $jsonRules) {
        $last_request->setParameters($jsonRules);
        $entityManager->persist($last_request);
        $entityManager->flush();
        $new_request = True;
      } else {
        $new_request = False;
      }
    }


    if ($new_request){
      $rr = $this->check_and_store_data($clanID, $jsonRules, "2501", $entityManager); //@todo
      $membersInCWL = $rr[0];
      //$excelData = $rr[1];
    } else{
      $query = $entityManager->getRepository(Attack::class)
          ->createQueryBuilder('a')
          ->select('DISTINCT a.pseudo')  // Sélectionner les pseudos distincts
          ->where('a.date = 2501')
          ->orderBy('a.mapPosition', 'ASC')  // Trier par mapPosition
          ->getQuery();
      $pseudos= $query->getResult();
      $membersInCWL = array_map(function ($row) {
        return $row['pseudo'];
      }, $pseudos);
    }


    $clan = $entityManager->getRepository(Clan::class)
        ->findOneBy(['clan_id' => $clanID]);

    $clanName = "Undefined";
    if (!$clan) {
      // si aucun clan trouvé
      $results = [];
    } else {
      $clanName = $clan->getName();
      $results = $entityManager->getRepository(Attack::class)->findBy([
          'clan' => $clan,
          'date' => "2501",
      ]);
    }


    $data_Per_Player = [];
    foreach ($results as $attack) {
      $playerName = $attack->getPseudo();
      if (!isset($data_Per_Player[$playerName])) {
        $data_Per_Player[$playerName] = [];
      }
      $data_Per_Player[$playerName][] = $attack;
    }

    uksort($data_Per_Player, function($a, $b) use ($data_Per_Player) {
      $mapPositionA = $data_Per_Player[$a][0]->getMapPosition();
      $mapPositionB = $data_Per_Player[$b][0]->getMapPosition();
      if ($mapPositionA == $mapPositionB) {
        return 0;
      }
      return ($mapPositionA < $mapPositionB) ? -1 : 1;
    });

    //dump($data_Per_Player['Ancien Piou']);



    $form = $this->createForm(CellIndexType::class);


    return $this->render('./cwl/view_cwl.html.twig', [
        'membersInCWL' => $membersInCWL,
        'length_members' => count($membersInCWL) -1,
        'data_All_Players' => $data_Per_Player,
        'form' => $form->createView(),
        'clanId' => $clanID,
        'clanName' => $clanName
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
