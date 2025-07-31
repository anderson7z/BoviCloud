<?php

namespace App\Form;

use App\Entity\Farm;
use App\Entity\Veterinarian;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FarmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nome', TextType::class, [
                'label' => 'Nome da Fazenda',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Fazenda Santa Clara',
                ],
            ])
            ->add('tamanho', NumberType::class, [
                'label' => 'Tamanho (em hectares - HA)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 150.5',
                ],
                'html5' => true,
            ])
            ->add('responsavel', TextType::class, [
                'label' => 'Nome do Responsável',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Digite o nome do proprietário ou gerente',
                ],
            ])
            ->add('veterinarios', EntityType::class, [
                'label' => 'Veterinários Associados',
                'class' => Veterinarian::class,
                'choice_label' => 'nome',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check-group border p-3 rounded',
                ],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'veterinario-item'];
                },
                'help' => 'Selecione um ou mais veterinários que atendem esta fazenda.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Farm::class,
        ]);
    }
}