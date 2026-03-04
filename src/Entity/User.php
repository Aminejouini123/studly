<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id; // @phpstan-ignore property.onlyRead

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $verificationCode = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    /** @var array<int, string> */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères.")]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire", groups: ['create'])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    #[Assert\Length(min: 2, minMessage: "Le prénom doit faire au moins {{ limit }} caractères.")]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 2, minMessage: "Le nom doit faire au moins {{ limit }} caractères.")]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de naissance est obligatoire")]
    #[Assert\LessThanOrEqual("today", message: "La date de naissance ne peut pas être dans le futur.")]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire")]
    #[Assert\Regex(pattern: "/^[2459][0-9]{7}$/", message: "Le numéro de téléphone doit être un numéro tunisien valide (8 chiffres commençant par 2, 4, 5 ou 9).")]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    private ?string $address = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    // Legacy fields kept for compatibility or future removal if needed, made nullable
    /** @var Collection<int, Event> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Event::class)]
    private Collection $events;

    /** @var Collection<int, Motivation> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Motivation::class)]
    private Collection $motivations;

    /** @var Collection<int, Group> */
    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Group::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $groups;

    /** @var Collection<int, Group> */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'members')]
    private Collection $memberGroups;

    /** @var Collection<int, Task> */
    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Task::class)]
    private Collection $assignedTasks;

    /** @var Collection<int, ProjectTask> */
    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: ProjectTask::class)]
    private Collection $assignedProjectTasks;

    /** @var Collection<int, Activity> */
    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Activity::class)]
    private Collection $assignedActivities;

    /** @var Collection<int, Invitation> */
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Invitation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $sentInvitations;

    /** @var Collection<int, Invitation> */
    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Invitation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $receivedInvitations;

    /** @var Collection<int, Notification> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Choice(choices: ['Active', 'Inactive', 'Pending'], message: "Le statut doit être 'Active', 'Inactive' ou 'Pending'.")]
    private ?string $statut = 'Active';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le niveau d'éducation ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $educationLevel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le titre du poste ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $jobTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "L'URL du site web ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Url(message: "L'URL '{{ value }}' n'est pas valide.")]
    private ?string $website = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La biographie ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $bio = null;

    /** @var array<int, string>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $skills = [];

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $score = 0;



    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $googleAccessToken = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $googleRefreshToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $googleTokenExpiresAt = null;

    /** @var Collection<int, UserAction> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserAction::class, orphanRemoval: true)]
    private Collection $userActions;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->roles = ['ROLE_ETUDIANT']; // Default role
        $this->events = new ArrayCollection();
        $this->motivations = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->memberGroups = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->assignedTasks = new ArrayCollection();
        $this->assignedProjectTasks = new ArrayCollection();
        $this->assignedActivities = new ArrayCollection();
        $this->sentInvitations = new ArrayCollection();
        $this->receivedInvitations = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->userActions = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user has at least ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<int, string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);

            $event->setUser($this);
        }

        return $this;
    }

    /** @var Collection<int, Course> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Course::class)]
    private Collection $courses;

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->setUser($this);

        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Motivation>
     */
    public function getMotivations(): Collection
    {
        return $this->motivations;
    }

    public function addMotivation(Motivation $motivation): static
    {
        if (!$this->motivations->contains($motivation)) {
            $this->motivations->add($motivation);
            $motivation->setUser($this);
        }

        return $this;
    }

    public function removeMotivation(Motivation $motivation): static
    {
        if ($this->motivations->removeElement($motivation)) {
            // set the owning side to null (unless already changed)
            if ($motivation->getUser() === $this) {
                $motivation->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getAssignedTasks(): Collection
    {
        return $this->assignedTasks;
    }

    public function addAssignedTask(Task $task): static
    {
        if (!$this->assignedTasks->contains($task)) {
            $this->assignedTasks->add($task);
            $task->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedTask(Task $task): static
    {
        if ($this->assignedTasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getAssignedUser() === $this) {
                $task->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectTask>
     */
    public function getAssignedProjectTasks(): Collection
    {
        return $this->assignedProjectTasks;
    }

    public function addAssignedProjectTask(ProjectTask $projectTask): static
    {
        if (!$this->assignedProjectTasks->contains($projectTask)) {
            $this->assignedProjectTasks->add($projectTask);
            $projectTask->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedProjectTask(ProjectTask $projectTask): static
    {
        if ($this->assignedProjectTasks->removeElement($projectTask)) {
            // set the owning side to null (unless already changed)
            if ($projectTask->getAssignedUser() === $this) {
                $projectTask->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getAssignedActivities(): Collection
    {
        return $this->assignedActivities;
    }

    public function addAssignedActivity(Activity $activity): static
    {
        if (!$this->assignedActivities->contains($activity)) {
            $this->assignedActivities->add($activity);
            $activity->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedActivity(Activity $activity): static
    {
        if ($this->assignedActivities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getAssignedUser() === $this) {
                $activity->setAssignedUser(null);
            }
        }

        return $this;
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->setCreator($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->removeElement($group)) {
            // set the owning side to null (unless already changed)
            if ($group->getCreator() === $this) {
                $group->setCreator(null);
            }
        }

        return $this;
    }

    public function removeCourse(Course $course): static
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getUser() === $this) {
                $course->setUser(null);

            }
        }

        return $this;
    }
    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getEducationLevel(): ?string
    {
        return $this->educationLevel;
    }

    public function setEducationLevel(?string $educationLevel): static
    {
        $this->educationLevel = $educationLevel;
        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;
        return $this;
    }

    /**
     * @return array<int, string>|null
     */
    public function getSkills(): ?array
    {
        return $this->skills;
    }

    /**
     * @param array<int, string>|null $skills
     */
    public function setSkills(?array $skills): static
    {
        $this->skills = $skills;
        return $this;
    }
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(?string $verificationCode): static
    {
        $this->verificationCode = $verificationCode;
        return $this;
    }
    public function getGoogleAccessToken(): ?string
    {
        return $this->googleAccessToken;
    }

    public function setGoogleAccessToken(?string $googleAccessToken): static
    {
        $this->googleAccessToken = $googleAccessToken;
        return $this;
    }

    public function getGoogleRefreshToken(): ?string
    {
        return $this->googleRefreshToken;
    }

    public function setGoogleRefreshToken(?string $googleRefreshToken): static
    {
        $this->googleRefreshToken = $googleRefreshToken;
        return $this;
    }

    public function getGoogleTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->googleTokenExpiresAt;
    }

    public function setGoogleTokenExpiresAt(?\DateTimeInterface $googleTokenExpiresAt): static
    {
        $this->googleTokenExpiresAt = $googleTokenExpiresAt;
        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function addScore(int $points): static
    {
        $this->score += $points;
        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getMemberGroups(): Collection
    {
        return $this->memberGroups;
    }

    public function addMemberGroup(Group $memberGroup): static
    {
        if (!$this->memberGroups->contains($memberGroup)) {
            $this->memberGroups->add($memberGroup);
            $memberGroup->addMember($this);
        }

        return $this;
    }

    public function removeMemberGroup(Group $memberGroup): static
    {
        if ($this->memberGroups->removeElement($memberGroup)) {
            $memberGroup->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getSentInvitations(): Collection
    {
        return $this->sentInvitations;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getReceivedInvitations(): Collection
    {
        return $this->receivedInvitations;
    }

    /**
     * @return Collection<int, UserAction>
     */
    public function getUserActions(): Collection
    {
        return $this->userActions;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    public function addUserAction(UserAction $userAction): static
    {
        if (!$this->userActions->contains($userAction)) {
            $this->userActions->add($userAction);
            $userAction->setUser($this);
        }

        return $this;
    }

    public function removeUserAction(UserAction $userAction): static
    {
        if ($this->userActions->removeElement($userAction)) {
            // set the owning side to null (unless already changed)
            if ($userAction->getUser() === $this) {
                $userAction->setUser(null);
            }
        }

        return $this;
    }

}
