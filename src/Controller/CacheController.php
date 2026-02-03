<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Predis\Client;

final class CacheController extends AbstractController
{
    private $contactos = [
        15 => ["name" => "Juan Pérez", "phone" => "524142432", "email" => "juanp@example.org"],
        2 => ["name" => "Ana López", "phone" => "58958448", "email" => "anita@example.org"],
        5 => ["name" => "Mario Montero", "phone" => "5326824", "email" => "mario.mont@example.org"],
        7 => ["name" => "Laura Martínez", "phone" => "42898966", "email" => "lm2000@example.org"],
        9 => ["name" => "Nora Jover", "phone" => "54565859", "email" => "norajover@example.org"]
    ];
    public function __construct(
        private Client $redis
    ) {

    }

    #[Route('/cache-clear', name: 'cache-clear')]
    public function cacheClear(): Response
    {
        $this->redis->flushdb();
        return new Response("Cache cleared");
    }

    #[Route('/cache-invalidate/{id}', name: 'cache-invalidate')]
    public function invalidate($id): Response
    {
        $this->redis->del($id);
        return new Response("Cache invalidate: " . $id);
    }

    #[Route('/{id}', name: '')]
    public function index($id): Response
    {

        return $this->render('base.html.twig', ["id" => $id]);

    }
    private function longRunningQuery($id): array
    {
        // This is the data returned by this long running query
        return $this->contactos[$id];
    }

    private function isCached($id): bool
    {
        return $this->redis->exists($id);
    }
    public function userTemplate($id): Response
    {
        if ($this->isCached($id)) {
            // The second time is already cached, so we don't
            // have to run de long running query
            return new Response("cache hit" . $this->redis->get($id));
        } else {
            $data = $this->longRunningQuery($id);
            $c = $this->render('user.html.twig', ["data" => $data])->getContent();
            // Save html in cache
            $this->cacheSet($id, $c);
            return new Response($c);
        }
    }
    private function cacheSet($id, $data): void
    {
        $this->redis->set($id, $data);
    }
}
