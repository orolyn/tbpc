<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'category',
                EntityType::class,
                [
                    'class' => Category::class,
                    'choice_label' => 'name'
                ]
            )
            ->add('description', TextType::class)
            ->add(
                'start',
                DateTimeType::class,
                [
                    'date_widget' => 'single_text',
                    'row_attr' => [
                        'class' => 'input-group',
                    ]
                ]
            )
            ->add(
                'finish',
                DateTimeType::class,
                [
                    'date_widget' => 'single_text',
                    'row_attr' => [
                        'class' => 'input-group',
                    ]
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Create Task',
                    'attr' => [
                        'class' => 'btn btn-primary mt-2 ml-0'
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
