<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CacheController extends AbstractController
{
    private $data = [
        1 => ["name" => "Juan Pérez", "phone" => "524142432", "email" => "juanp@example.org"],
        2 => ["name" => "Ana López", "phone" => "58958448", "email" => "anita@example.org"],
        5 => ["name" => "Mario Montero", "phone" => "5326824", "email" => "mario.mont@example.org"],
        7 => ["name" => "Laura Martínez", "phone" => "42898966", "email" => "lm2000@example.org"],
        9 => ["name" => "Nora Jover", "phone" => "54565859", "email" => "norajover@example.org"]
    ];
    #[Route('/{id}', name: '')]
    public function index($id): Response
    {
        if ($this->isCached($id)) {
            return new Response($this->getCached($id));
        } else {
            $data = $this->longRunningQuery($id);
            $c = $this->render('user.html.twig', ["data" => ["name" => "Not cached", "phone" => "3333", "email" => "notcached@example.org"]])->getContent();
            // Save in cache
            $this->cacheSet($id, $c);
            return new Response($c);
        }

    }
    private function longRunningQuery($id): array
    {
        if (isset($this->data[$id])) {
            return $this->data[$id];
        }
        return [];
    }

    private function isCached($id): bool
    {
        return isset($this->data[$id]);
    }
    private function getCached($id): string
    {
        return $this->data[$id]["name"];
    }
    private function cacheSet($id, $data): void
    {
        $this->data[$id] = $data;
    }
}
