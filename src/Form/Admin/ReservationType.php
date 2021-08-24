<?php

namespace App\Form\Admin;

use App\Entity\Admin\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userid')
            ->add('hotelid')
            ->add('roomid')
            ->add('name')
            ->add('surname')
            ->add('email')
            ->add('phone')
            ->add('total')

            ->add('days')
            ->add('message')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'New' => 'New',
                    'Accepted' => 'Accepted',
                    'Completed' => 'Completed',
                    'Canceled' => 'Canceled',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
