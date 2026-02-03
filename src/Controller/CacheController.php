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
    #[Route('/{id}', name: '')]
    public function index($id): Response
    {
        if ($this->isCached($id)) {
            return new Response("cache hit" . $this->getCached($id));
        } else {
            $data = $this->longRunningQuery($id);
            $c = $this->render('user.html.twig', ["data" => $data])->getContent();
            // Save in cache
            $this->cacheSet($id, $c);
            return new Response($c);
        }

    }
    private function longRunningQuery($id): array
    {
        return $this->contactos[$id];
    }

    private function isCached($id): bool
    {
        return $this->redis->exists($id);
    }
    private function getCached($id): string
    {
        return $this->redis->get($id);
    }
    private function cacheSet($id, $data): void
    {
        $this->redis->set($id, $data);
    }
}
