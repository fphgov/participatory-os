<?php

declare(strict_types=1);

opcache_invalidate(__FILE__, true);

chdir(__DIR__ . '/../');

use App\Entity;
use Doctrine\ORM\EntityManagerInterface;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeMutable(dirname(__DIR__));
$dotenv->load();

$container = require 'config/container.php';

$ogMetas = [];

$em = $container->get(EntityManagerInterface::class);

$ideaRepository    = $em->getRepository(Entity\Idea::class);
$postRepository    = $em->getRepository(Entity\Post::class);
$projectRepository = $em->getRepository(Entity\Project::class);

// Ideas
$ideas = $ideaRepository->findAll();

foreach ($ideas as $idea) {
    $ogMetas['/otletek/' . $idea->getId()] = [
        'title'       => $idea->getTitle(),
        'description' => $idea->getDescription(),
    ];
}

// Projects
$projects = $projectRepository->findAll();

foreach ($projects as $idea) {
    $ogMetas['/projektek/' . $idea->getId()] = [
        'title'       => $idea->getTitle(),
        'description' => $idea->getDescription(),
    ];
}

// Posts
$posts = $postRepository->findAll();

foreach ($posts as $post) {
    $ogMetas['/hirek/' . $post->getSlug()] = [
        'title'       => $post->getTitle(),
        'description' => $post->getDescription(),
    ];
}

$file = dirname(__FILE__, 2) . '/public/seo.json';
touch($file, time());
file_put_contents($file, json_encode($ogMetas));