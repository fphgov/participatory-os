<?php

declare(strict_types=1);

opcache_invalidate(__FILE__, true);

if (PHP_SAPI !== 'cli') {
    return false;
}

chdir(__DIR__ . '/../');

use App\Entity\Idea;
use App\Entity\WorkflowState;
use App\Entity\WorkflowStateInterface;
use Doctrine\ORM\EntityManagerInterface;

require 'vendor/autoload.php';

$container = require 'config/container.php';

$em             = $container->get(EntityManagerInterface::class);
$ideaRepository = $em->getRepository(Idea::class);

$publishIdeas = $ideaRepository->findBy([
    'id' => [],
]);

foreach ($publishIdeas as $publishIdea) {
    $publishIdea->setWorkflowState(
        $em->getReference(WorkflowState::class, WorkflowStateInterface::STATUS_PUBLISHED)
    );

    $em->flush();
}

$rejectedIdeas = $ideaRepository->findBy([
    'id' => [],
]);

foreach ($rejectedIdeas as $rejectedIdea) {
    $rejectedIdea->setWorkflowState(
        $em->getReference(WorkflowState::class, WorkflowStateInterface::STATUS_TRASH)
    );

    $em->flush();
}
