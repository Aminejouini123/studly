<?php

namespace App\EventListener;

use App\Entity\Task;
use App\Entity\ProjectTask;
use App\Entity\Activity;
use App\Service\ScoreService;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ScoreUpdateListener implements EventSubscriberInterface
{
    private ScoreService $scoreService;

    public function __construct(ScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    public function getSubscribedEvents(): array
    {
        return [
            'preUpdate',
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        // Check if status field was changed
        if ($args->hasChangedField('status')) {
            if ($entity instanceof Task) {
                $this->scoreService->updateScoreForTask($entity);
            } elseif ($entity instanceof ProjectTask) {
                $this->scoreService->updateScoreForProjectTask($entity);
            } elseif ($entity instanceof Activity) {
                $this->scoreService->updateScoreForActivity($entity);
            }
        }
    }
}