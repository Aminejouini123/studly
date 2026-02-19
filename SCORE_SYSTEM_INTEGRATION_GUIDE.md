# Smart Gamification Score System Integration Guide

## Overview
The gamification score system has been implemented with the following components:

1. **Entity Updates**: Added score tracking and user assignments
2. **ScoreService**: Handles all score calculations
3. **Event Listener**: Automatically updates scores when task statuses change
4. **ScoreController**: Provides score viewing functionality
5. **Templates**: User interfaces for score display

## Entity Updates Made

### User Entity
- Added `score` field (integer, default 0)
- Added `addScore()` method for score manipulation
- Added relationships for assigned tasks, project tasks, and activities

### Task Entity
- Added `assignedUser` relationship
- Added `deadline` field
- Added `completedAt` timestamp

### ProjectTask Entity
- Added `assignedUser` relationship
- Added `deadline` field
- Added `completedAt` timestamp

### Activity Entity
- Added `assignedUser` relationship
- Added `completedAt` timestamp

## Score Calculation Rules

- **Task completed**: +10 points
- **Task completed before deadline**: +20 points total (+10 base + 10 bonus)
- **Active participation**: +5 points (for activities)
- **Late submission**: -5 points (penalty)

## Integration Without Breaking Existing CRUD

### 1. Automatic Score Updates
The `ScoreUpdateListener` automatically updates scores when entity statuses change via Doctrine events. No changes needed to existing controllers.

### 2. Manual Score Updates (Alternative)
If you prefer manual control, you can inject ScoreService into controllers:

```php
// In any controller that updates task status
public function updateTaskStatus(Task $task, ScoreService $scoreService)
{
    // Your existing logic
    $task->setStatus('completed');

    // Update score manually
    $scoreService->updateScoreForTask($task);

    $this->entityManager->flush();
}
```

### 3. Example Controller Integration

#### For Task Management
```php
<?php
// src/Controller/TaskController.php

namespace App\Controller;

use App\Entity\Task;
use App\Service\ScoreService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/task/{id}/complete', name: 'task_complete')]
    public function completeTask(Task $task, ScoreService $scoreService, EntityManagerInterface $em)
    {
        // Existing validation logic
        if ($task->getAssignedUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Update status (existing logic)
        $task->setStatus('completed');
        $task->setCompletedAt(new \DateTime());

        // Score is automatically updated by the event listener
        // OR manually update if preferred:
        // $scoreService->updateScoreForTask($task);

        $em->flush();

        $this->addFlash('success', 'Task completed successfully!');
        return $this->redirectToRoute('task_list');
    }
}
```

#### For Admin Task Assignment
```php
#[Route('/admin/task/{id}/assign', name: 'admin_task_assign')]
public function assignTask(Task $task, Request $request, EntityManagerInterface $em)
{
    $userId = $request->request->get('user_id');
    $user = $em->getRepository(User::class)->find($userId);

    if ($user) {
        $task->setAssignedUser($user);
        $em->flush();

        $this->addFlash('success', 'Task assigned successfully!');
    }

    return $this->redirectToRoute('admin_tasks');
}
```

## Database Migration

Run the migration to update your database schema:

```bash
php bin/console doctrine:migrations:migrate
```

## Navigation Integration

### For Students
Add a "My Score" link to the student navigation:

```twig
{# templates/base.html.twig #}
{% if is_granted('ROLE_ETUDIANT') %}
    <li class="nav-item">
        <a class="nav-link" href="{{ path('app_my_score') }}">
            <i class="fas fa-trophy"></i> My Score
        </a>
    </li>
{% endif %}
```

### For Admins
Add a "Student Scores" link to the admin navigation:

```twig
{# templates/admin/base.html.twig #}
{% if is_granted('ROLE_ADMIN') %}
    <li class="nav-item">
        <a class="nav-link" href="{{ path('app_admin_scores') }}">
            <i class="fas fa-chart-line"></i> Student Scores
        </a>
    </li>
{% endif %}
```

## Testing the System

1. **Create a task** with a deadline and assign it to a student
2. **Complete the task** before/after the deadline
3. **Check the score** updates automatically
4. **View scores** via the provided routes

## Security Considerations

- Students can only see their own scores
- Admins can see all student scores
- Score updates are protected by entity relationships
- All score calculations are server-side only

## Future Enhancements

- Add score history tracking
- Implement leaderboards
- Add badges/achievements
- Create score-based rewards
- Add score decay over time
- Implement team scoring

## Troubleshooting

### Scores Not Updating
1. Check that the `ScoreUpdateListener` is registered (autoconfigured)
2. Verify entity relationships are correct
3. Ensure `assignedUser` is set on tasks

### Migration Issues
1. Backup your database before running migrations
2. Check foreign key constraints
3. Verify column types match entity definitions

### Permission Issues
1. Ensure users have correct roles (`ROLE_ETUDIANT`, `ROLE_ADMIN`)
2. Check route security annotations
3. Verify user authentication