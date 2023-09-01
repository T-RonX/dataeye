<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Locale\Entity\Timezone;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Entity\TaskCompletion;
use App\Task\Entity\TaskParticipant;
use App\Task\Entity\TaskPostpone;
use App\Task\Entity\TaskRecurrenceDay;
use App\Task\Entity\TaskRecurrenceMonthRelative;
use App\Task\Entity\TaskRecurrenceWeek;
use App\Task\Entity\TaskRecurrenceYearRelative;
use App\Task\Enum\Day;
use App\Task\Enum\DayOrdinal;
use App\Task\Enum\Month;
use App\Task\Enum\WeekOrdinal;
use App\User\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class Load030_Task extends Fixture
{
    private EntityManagerInterface $entityManager;

    public function load(ObjectManager|EntityManagerInterface $manager): void
    {
        $this->entityManager = $manager;

        $this->createCategories();

        $user_1 = $this->getReference('user_1', User::class);
        $user_2 = $this->getReference('user_2', User::class);

        $task_1 = (new Task())
            ->setName('Task 1')
            ->setDescription('Some description')
            ->setOwnedBy($user_1)
            ->setCategory($this->getReference('user_1_category_1', TaskCategory::class))
            ->setDuration(60)
            ->setDateTime(new DateTimeImmutable('2023-8-5 23:30:48'))
            ->setTimezone($this->getReference('timezone_Europe/Amsterdam', Timezone::class));

        $task_2 = (new Task())
            ->setName('Task 2')
            ->setDescription('Some description')
            ->setOwnedBy($user_1)
            ->setCategory($this->getReference('user_1_category_2', TaskCategory::class))
            ->setDuration(60)
            ->setDateTime(new DateTimeImmutable('2000-1-1 14:00:10'))
            ->setTimezone($this->getReference('timezone_Europe/Amsterdam', Timezone::class));

        $task_3 = (new Task())
            ->setName('Task 3')
            ->setDescription('Some description')
            ->setOwnedBy($user_2)
            ->setCategory($this->getReference('user_2_category_1', TaskCategory::class))
            ->setDuration(60)
            ->setDateTime(new DateTimeImmutable('2023-1-1 16:00:59'))
            ->setTimezone($this->getReference('timezone_Europe/Amsterdam', Timezone::class));

        $this->addReference('user_1_task_1', $task_1);
        $this->addReference('user_1_task_2', $task_2);
        $this->addReference('user_2_task_1', $task_3);

        $this->createRecurrences();
        $this->createParticipants();
        $this->createCompletions();
        $this->createPostpones();

        $task_1->setRecurrences(new ArrayCollection([
            $this->getReference('user_1_task_1_recurrence_1', TaskRecurrenceYearRelative::class),
            $this->getReference('user_1_task_1_recurrence_2', TaskRecurrenceMonthRelative::class),
        ]))
        ->setParticipants(new ArrayCollection([
            $this->getReference('user_1_task_1_participant_1', TaskParticipant::class),
        ]))
        ->setCompletions(new ArrayCollection([
            $this->getReference('user_1_task_1_completion_1', TaskCompletion::class),
        ]));

        $task_2->setRecurrences(new ArrayCollection([
            $this->getReference('user_1_task_2_recurrence_1', TaskRecurrenceDay::class),
        ]))
        ->setParticipants(new ArrayCollection([
            $this->getReference('user_1_task_2_participant_1', TaskParticipant::class),
            $this->getReference('user_1_task_2_participant_2', TaskParticipant::class),
        ]))
        ->setPostpones(new ArrayCollection([
            $this->getReference('user_1_task_2_postpone_1', TaskPostpone::class),
            $this->getReference('user_1_task_2_postpone_2', TaskPostpone::class),
        ]))
        ->setCompletions(new ArrayCollection([
                $this->getReference('user_1_task_2_completion_1', TaskCompletion::class),
                $this->getReference('user_1_task_2_completion_2', TaskCompletion::class),
        ]));

        $task_3->setRecurrences(new ArrayCollection([
            $this->getReference('user_2_task_1_recurrence_1', TaskRecurrenceWeek::class),
        ]))
        ->setParticipants(new ArrayCollection([
            $this->getReference('user_2_task_1_participant_1', TaskParticipant::class),
            $this->getReference('user_2_task_1_participant_2', TaskParticipant::class),
        ]));

        $manager->persist($task_1);
        $manager->persist($task_2);
        $manager->persist($task_3);

        $manager->flush();
    }

    private function createCategories(): void
    {
        $user_1 = $this->getReference('user_1', User::class);
        $user_2 = $this->getReference('user_2', User::class);

        $category_1 = (new TaskCategory())
            ->setName('Category 1')
            ->setOwnedBy($user_1);

        $category_2 = (new TaskCategory())
            ->setName('Category 2')
            ->setOwnedBy($user_1);

        $category_3 = (new TaskCategory())
            ->setName('Category 1')
            ->setOwnedBy($user_2);

        $this->addReference('user_1_category_1', $category_1);
        $this->addReference('user_1_category_2', $category_2);
        $this->addReference('user_2_category_1', $category_3);

        $this->entityManager->persist($category_1);
        $this->entityManager->persist($category_2);
        $this->entityManager->persist($category_3);
    }

    private function createRecurrences(): void
    {
        $recurrence_1 = (new TaskRecurrenceYearRelative())
            ->setTask($this->getReference('user_1_task_1', Task::class))
            ->setStartDate(new DateTimeImmutable('2023-8-5 12:50:33'))
            ->setEndDate(new DateTimeImmutable('2023-12-31 23:59:59'))
            ->setDay(Day::Tuesday)
            ->setMonth(Month::February)
            ->setDayOrdinal(DayOrdinal::Second)
            ->setDeletedAt(new DateTime());

        $recurrence_2 = (new TaskRecurrenceMonthRelative())
            ->setTask($this->getReference('user_1_task_1', Task::class))
            ->setStartDate(new DateTimeImmutable('2025-1-1 00:00:00'))
            ->setEndDate(null)
            ->setInterval(14)
            ->setWeekOrdinal(WeekOrdinal::Last)
            ->setDay(Day::Friday);

        $recurrence_3 = (new TaskRecurrenceDay())
            ->setTask($this->getReference('user_1_task_2', Task::class))
            ->setStartDate(new DateTimeImmutable('2000-1-1 00:00:00'))
            ->setEndDate(new DateTimeImmutable('2050-1-1 23:59:59'))
            ->setInterval(20);

        $recurrence_4 = (new TaskRecurrenceWeek())
            ->setTask($this->getReference('user_2_task_1', Task::class))
            ->setStartDate(new DateTimeImmutable('2023-1-1 00:00:00'))
            ->setEndDate(null)
            ->setInterval(2)
            ->setDays([Day::Saturday, Day::Sunday]);

        $this->addReference('user_1_task_1_recurrence_1', $recurrence_1);
        $this->addReference('user_1_task_1_recurrence_2', $recurrence_2);
        $this->addReference('user_1_task_2_recurrence_1', $recurrence_3);
        $this->addReference('user_2_task_1_recurrence_1', $recurrence_4);

        $this->entityManager->persist($recurrence_1);
        $this->entityManager->persist($recurrence_2);
        $this->entityManager->persist($recurrence_3);
        $this->entityManager->persist($recurrence_4);
    }

    private function createParticipants(): void
    {
        $participant_1 = (new TaskParticipant())
            ->setTask($this->getReference('user_1_task_1', Task::class))
            ->setUser($this->getReference('user_1', User::class));

        $participant_2 = (new TaskParticipant())
            ->setTask($this->getReference('user_1_task_2', Task::class))
            ->setUser($this->getReference('user_1', User::class));

        $participant_3 = (new TaskParticipant())
            ->setTask($this->getReference('user_1_task_2', Task::class))
            ->setUser($this->getReference('user_2', User::class));

        $participant_4 = (new TaskParticipant())
            ->setTask($this->getReference('user_2_task_1', Task::class))
            ->setUser($this->getReference('user_2', User::class));

        $participant_5 = (new TaskParticipant())
            ->setTask($this->getReference('user_2_task_1', Task::class))
            ->setUser($this->getReference('user_1', User::class));

        $this->addReference('user_1_task_1_participant_1', $participant_1);
        $this->addReference('user_1_task_2_participant_1', $participant_2);
        $this->addReference('user_1_task_2_participant_2', $participant_3);
        $this->addReference('user_2_task_1_participant_1', $participant_4);
        $this->addReference('user_2_task_1_participant_2', $participant_5);

        $this->entityManager->persist($participant_1);
        $this->entityManager->persist($participant_2);
        $this->entityManager->persist($participant_3);
        $this->entityManager->persist($participant_4);
        $this->entityManager->persist($participant_5);
    }

    private function createCompletions(): void
    {
        $completion_1 = (new TaskCompletion())
            ->setTask($this->getReference('user_1_task_2', Task::class))
            ->setCompletionAt(new DateTimeImmutable('2020-5-5 09:30:00'))
            ->setCompletionBy($this->getReference('user_1_task_2_participant_2', TaskParticipant::class));

        $completion_2 = (new TaskCompletion())
            ->setTask($this->getReference('user_1_task_2', Task::class))
            ->setCompletionAt(new DateTimeImmutable('2020-5-8 09:30:00'))
            ->setCompletionBy($this->getReference('user_1_task_2_participant_1', TaskParticipant::class));

        $completion_3 = (new TaskCompletion())
            ->setTask($this->getReference('user_2_task_1', Task::class))
            ->setCompletionAt(new DateTimeImmutable('2020-5-8 09:30:00'))
            ->setCompletionBy($this->getReference('user_2_task_1_participant_2', TaskParticipant::class));

        $this->addReference('user_1_task_1_completion_1', $completion_1);
        $this->addReference('user_1_task_2_completion_1', $completion_2);
        $this->addReference('user_1_task_2_completion_2', $completion_3);

        $this->entityManager->persist($completion_1);
        $this->entityManager->persist($completion_2);
        $this->entityManager->persist($completion_3);
    }

    private function createPostpones(): void
    {
        $postpone_1 = (new TaskPostpone())
            ->setTask($this->getReference('user_1_task_1', Task::class))
            ->setPostponedAt(new DateTimeImmutable('2020-3-5 12:00:50'))
            ->setPostponedBy($this->getReference('user_1_task_2_participant_1', TaskParticipant::class));

        $postpone_2 = (new TaskPostpone())
            ->setTask($this->getReference('user_1_task_1', Task::class))
            ->setPostponedAt(new DateTimeImmutable('2020-3-4 22:20:00'))
            ->setPostponedBy($this->getReference('user_1_task_2_participant_2', TaskParticipant::class));

        $this->addReference('user_1_task_2_postpone_1', $postpone_1);
        $this->addReference('user_1_task_2_postpone_2', $postpone_2);

        $this->entityManager->persist($postpone_1);
        $this->entityManager->persist($postpone_2);
    }
}
