<?php

namespace App\Form;

use App\Entity\Farm;
use App\Entity\Veterinarian;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VeterinarianType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nome', TextType::class, [
                'label' => 'Nome Completo',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Digite o nome do veterinário',
                ],
            ])
            ->add('crmv', TextType::class, [
                'label' => 'CRMV',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: CRMV-SP 12345',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Veterinarian::class,
        ]);
    }
}