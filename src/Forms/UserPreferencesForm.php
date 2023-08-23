<?php

declare(strict_types=1);

namespace App\Forms;

use App\Locale\Entity\Timezone;
use App\User\Entity\User;
use App\UserPreference\Provider\UserPreferenceProvider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPreferencesForm extends AbstractType
{
    public function __construct(
        private readonly UserPreferenceProvider $preferenceProvider
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];
        $timezonePreference = $this->preferenceProvider->getTimezone($user);
        $tz = $timezonePreference->getTimezone();


        $builder
            ->add('timezone', EntityType::class, [
                'class' => Timezone::class,
                'data' => $timezonePreference->getTimezone(),
                'choice_label' => fn(Timezone $timezone) => $this->formatTimezone($timezone),
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ]);
    }

    private function formatTimezone(Timezone $timezone): string
    {
        $name = $timezone->getName();
        $hours = $timezone->getOffsetHours();
        $minutes = $timezone->getOffsetMinutes();

        return sprintf('(GMT%s:%s) %s', sprintf('%s%02d', $hours >= 0 ? ($hours > 0 ? '+' : ' ') : '-', abs($hours)), sprintf('%02d', $minutes), $name);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
        ]);
    }
}
