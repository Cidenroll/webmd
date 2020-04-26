<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Model\UserRegistrationFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = new User();

        $builder
            ->add('email', EmailType::class)
            // don't use password: avoid EVER setting that on a
            // field that might be persisted
            ->add('plainPassword', PasswordType::class)
            ->add('firstName')
            ->add('agreeTerms', CheckboxType::class)
            ->add('userType', ChoiceType::class, [
                'required'  =>  true,
                'choices'   => $user->getAllUserPossibleValues()
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserRegistrationFormModel::class
        ]);
    }
}
