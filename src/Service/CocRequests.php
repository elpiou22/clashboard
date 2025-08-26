<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CocRequests
{
  public function __construct(
      private HttpClientInterface $http,
      private string $token, // injecté depuis .env
  ) {}

  private function norm(string $tag): string
  {
    $raw = str_starts_with($tag, '#') ? $tag : '#'.$tag;
    return rawurlencode($raw); // -> %23TAG
  }

  /** @return array{
   *   ok:bool, status:int, reason?:string, message?:string, type?:string,
   *   roundsCount?:int, state?:string
   * } */
  public function probeCwl(string $clanTag): array
  {
    $url = sprintf('https://api.clashofclans.com/v1/clans/%s/currentwar/leaguegroup', $this->norm($clanTag));
    $res = $this->http->request('GET', $url, [
        'headers' => ['Authorization' => 'Bearer '.$this->token],
        'timeout' => 10,
    ]);

    $status = $res->getStatusCode();
    $body   = $res->getContent(false);
    $json   = json_decode($body, true) ?: [];

    if ($status === 200) {
      $rounds = $json['rounds'] ?? [];
      $state  = $json['state']  ?? null; // e.g. "preparation" / "inWar" / etc.
      if (!is_array($rounds) || count($rounds) === 0) {
        return ['ok'=>false, 'status'=>200, 'reason'=>'empty_leaguegroup', 'message'=>'No rounds in leaguegroup'];
      }
      return ['ok'=>true, 'status'=>200, 'roundsCount'=>count($rounds), 'state'=>$state];
    }

    // 404 est ambigu → on distingue "tag invalide" vs "clan existe mais pas de CWL active"
    if ($status === 404) {
      $check = $this->http->request('GET', 'https://api.clashofclans.com/v1/clans/'.$this->norm($clanTag), [
          'headers' => ['Authorization' => 'Bearer '.$this->token],
          'timeout' => 10,
      ]);
      if ($check->getStatusCode() === 404) {
        return [
            'ok'=>false, 'status'=>404, 'reason'=>'tag_invalid',
            'message'=>'Clan not found (invalid tag)'
        ];
      }
      return [
          'ok'=>false, 'status'=>404, 'reason'=>'cwl_not_active',
          'message'=>'Clan exists but has no active CWL (off season or not enrolled)'
      ];
    }

    // autres codes d’erreurs avec la structure de la doc
    return [
        'ok'     => false,
        'status' => $status,
        'reason' => $json['reason']  ?? match ($status) {
              401 => 'auth', 403 => 'access', 429 => 'rate_limit',
              500 => 'server_error', 503 => 'maintenance', default => 'http_error'
            },
        'message'=> $json['message'] ?? null,
        'type'   => $json['type']    ?? null,
    ];
  }


  /**
   * Enrobe probeCwl() avec un message humain + détail prêt à afficher.
   * @return array{ok:bool,status:int,human:string,raw:array}
   */
  public function getStatusCwl(string $clanTag): array
  {
    $probe = $this->probeCwl($clanTag);

    if ($probe['ok'] ?? false) {
      $human = sprintf(
          "CWL active (rounds: %d, state: %s)",
          $probe['roundsCount'] ?? 0,
          $probe['state'] ?? 'unknown'
      );
      return ['ok'=>true, 'status'=>200, 'human'=>$human, 'raw'=>$probe];
    }

    $human = match ($probe['reason'] ?? '') {
      'tag_invalid'      => 'Tag de clan invalide.',
      'cwl_not_active'   => "Ce clan n’a pas de CWL active actuellement.",
      'empty_leaguegroup'=> "Aucune ronde CWL dans la réponse.",
      'auth'             => 'Clé API invalide/expirée (401).',
      'access'           => 'Accès refusé / journal privé (403).',
      'rate_limit'       => 'Trop de requêtes (429).',
      'server_error'     => 'Erreur côté API (500).',
      'maintenance'      => 'API en maintenance (503).',
      default            => 'Impossible de récupérer la CWL.',
    };

    $detail = sprintf(
        ' (HTTP %d%s%s)',
        $probe['status'] ?? 0,
        isset($probe['reason'])  ? ', reason: '.$probe['reason']   : '',
        isset($probe['message']) ? ', message: '.$probe['message'] : ''
    );

    return ['ok'=>false, 'status'=>$probe['status'] ?? 0, 'human'=>$human.$detail, 'raw'=>$probe];
  }



}