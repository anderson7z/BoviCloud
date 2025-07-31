<?php

namespace App\Form;

use App\Entity\Cow;
use App\Entity\Farm; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codigo', TextType::class, [
                'label' => 'Código do Animal',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: BR00123'
                ]
            ])
            ->add('leite', NumberType::class, [
                'label' => 'Produção de Leite (Litros/semana)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 70'
                ]
            ])
            ->add('racao', NumberType::class, [
                'label' => 'Consumo de Ração (Kg/semana)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 350'
                ]
            ])
            ->add('peso', NumberType::class, [
                'label' => 'Peso (Kg)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 450.5'
                ]
            ])
            ->add('nascimento', DateType::class, [
                'label' => 'Data de Nascimento',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('fazenda', EntityType::class, [
                'label' => 'Fazenda',
                'class' => Farm::class,
                'choice_label' => 'nome',
                'placeholder' => 'Selecione a fazenda a que o animal pertence',
                'attr' => [
                    'class' => 'form-select'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cow::class,
        ]);
    }
}