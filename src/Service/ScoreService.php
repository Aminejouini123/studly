<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\ProjectTask;
use App\Entity\Activity;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\Common\Collections\Collection;

class ScoreService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Reset scores for a collection of users
     */
    public function resetScores(Collection $users): void
    {
        foreach ($users as $user) {
            $user->setScore(0);
        }
        $this->entityManager->flush();
    }

    /**
     * Update score when a task status changes
     */
    public function updateScoreForTask(Task $task): void
    {
        $user = $task->getAssignedUser();
        if (!$user) {
            return;
        }

        $oldStatus = $this->getOldStatus($task);
        $newStatus = $task->getStatus();

        // Remove points from old status if applicable
        if ($oldStatus === 'completed') {
            $this->removeTaskCompletionPoints($user, $task);
        }

        // Add points for new status
        if ($newStatus === 'completed') {
            $this->addTaskCompletionPoints($user, $task);
            $task->setCompletedAt(new \DateTime());
        } else {
            $task->setCompletedAt(null);
        }

        $this->entityManager->flush();
    }

    /**
     * Update score when a project task status changes
     */
    public function updateScoreForProjectTask(ProjectTask $projectTask): void
    {
        $user = $projectTask->getAssignedUser();
        if (!$user) {
            return;
        }

        $oldStatus = $this->getOldStatus($projectTask);
        $newStatus = $projectTask->getStatus();

        // Remove points from old status if applicable
        if ($oldStatus === ProjectTask::STATUS_DONE) {
            $this->removeProjectTaskCompletionPoints($user, $projectTask);
        }

        // Add points for new status
        if ($newStatus === ProjectTask::STATUS_DONE) {
            $this->addProjectTaskCompletionPoints($user, $projectTask);
            $projectTask->setCompletedAt(new \DateTime());
        } else {
            $projectTask->setCompletedAt(null);
        }

        $this->entityManager->flush();
    }

    /**
     * Update score when an activity status changes
     */
    public function updateScoreForActivity(Activity $activity): void
    {
        $user = $activity->getAssignedUser();
        if (!$user) {
            return;
        }

        $oldStatus = $this->getOldStatus($activity);
        $newStatus = $activity->getStatus();

        // Remove points from old status if applicable
        if ($oldStatus === 'completed') {
            $this->removeActivityParticipationPoints($user, $activity);
        }

        // Add points for new status
        if ($newStatus === 'completed') {
            $this->addActivityParticipationPoints($user, $activity);
            $activity->setCompletedAt(new \DateTime());
        } else {
            $activity->setCompletedAt(null);
        }

        $this->entityManager->flush();
    }

    /**
     * Add points for task completion
     */
    private function addTaskCompletionPoints(User $user, Task $task): void
    {
        $points = 10; // Base points for task completion

        // Bonus for completing before deadline
        if ($task->getDeadline() && $task->getCompletedAt() && $task->getCompletedAt() <= $task->getDeadline()) {
            $points += 10; // Additional 10 points for early completion
        }

        $user->addScore($points);
    }

    /**
     * Remove points for task completion (when status changes from completed)
     */
    private function removeTaskCompletionPoints(User $user, Task $task): void
    {
        $points = 10; // Base points for task completion

        // Bonus for completing before deadline
        if ($task->getDeadline() && $task->getCompletedAt() && $task->getCompletedAt() <= $task->getDeadline()) {
            $points += 10; // Additional 10 points for early completion
        }

        $user->addScore(-$points);
    }

    /**
     * Add points for project task completion
     */
    private function addProjectTaskCompletionPoints(User $user, ProjectTask $projectTask): void
    {
        $points = 10; // Base points for project task completion

        // Bonus for completing before project deadline
        if ($projectTask->getProject() && $projectTask->getProject()->getDeadline() &&
            $projectTask->getCompletedAt() && $projectTask->getCompletedAt() <= $projectTask->getProject()->getDeadline()) {
            $points += 10; // Additional 10 points for early completion
        }

        $user->addScore($points);
    }

    /**
     * Remove points for project task completion
     */
    private function removeProjectTaskCompletionPoints(User $user, ProjectTask $projectTask): void
    {
        $points = 10; // Base points for project task completion

        // Bonus for completing before project deadline
        if ($projectTask->getProject() && $projectTask->getProject()->getDeadline() &&
            $projectTask->getCompletedAt() && $projectTask->getCompletedAt() <= $projectTask->getProject()->getDeadline()) {
            $points += 10; // Additional 10 points for early completion
        }

        $user->addScore(-$points);
    }

    /**
     * Add points for activity participation
     */
    private function addActivityParticipationPoints(User $user, Activity $activity): void
    {
        $points = 5; // Points for active participation
        $user->addScore($points);
    }

    /**
     * Remove points for activity participation
     */
    private function removeActivityParticipationPoints(User $user, Activity $activity): void
    {
        $points = 5; // Points for active participation
        $user->addScore(-$points);
    }

    /**
     * Get the old status from the database (for comparison)
     * This is a simplified version - in a real implementation, you'd track changes differently
     */
    private function getOldStatus($entity): ?string
    {
        $uow = $this->entityManager->getUnitOfWork();
        $changeset = $uow->getEntityChangeSet($entity);

        if (isset($changeset['status'])) {
            return $changeset['status'][0]; // index 0 is old value
        }

        return null;
    }

    /**
     * Get user score
     */
    public function getUserScore(User $user): int
    {
        return $user->getScore();
    }

    /**
     * Get all users' scores (for admin)
     */
    public function getAllUsersScores(): array
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $scores = [];

        foreach ($users as $user) {
            $scores[] = [
                'user' => $user,
                'score' => $user->getScore(),
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'email' => $user->getEmail()
            ];
        }

        // Sort by score descending
        usort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $scores;
    }
}