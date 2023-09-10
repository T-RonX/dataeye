<?php

declare(strict_types=1);

namespace App\Forms;

use App\Forms\Transformer\CallbackReverseTransformer;
use DateInterval;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskPostponeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('delay', ChoiceType::class, [
                'choices' => [
                    '15m' => 'PT15M',
                    '1h' => 'PT1H',
                    '4h' => 'PT4H',
                    'tomorrow' => 'P1D',
                    'skip' => 'skip',
                ]
            ])
            ->add('postpone', SubmitType::class, [
                'label' => 'postpone',
            ]);

        $builder->get('delay')->addModelTransformer(new CallbackReverseTransformer(
            $this->reverseTransformDelay(...),
        ));
    }

    private function reverseTransformDelay(string $delay): ?DateInterval
    {
        return $delay === 'skip' ? null : new DateInterval($delay);
    }
}
