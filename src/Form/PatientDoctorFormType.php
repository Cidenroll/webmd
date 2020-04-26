<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 2:35 AM
 */

namespace App\Form;


use App\Entity\DoctorToPatient;
use App\Entity\RelationsDp2;
use App\Entity\RelationsPd2;
use App\Repository\DoctorToPatientRepository;
use App\Repository\RelationsDp2Repository;
use App\Repository\RelationsPd2Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PatientDoctorFormType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RelationsPd2Repository
     */
    private $pd2Repository;


    /**
     * UserFileFormType constructor.
     * @param Security $security
     * @param RelationsPd2Repository $pd2Repository
     */
    public function __construct(Security $security, RelationsPd2Repository $pd2Repository)
    {
        $this->security = $security;
        $this->pd2Repository = $pd2Repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentUser = $this->security->getUser();
        $builder
            ->add('doctorId', ChoiceType::class, [
                'label' =>  'Doctor',
                'mapped'    =>  false,
                'required'  =>  false,
                'choices'   =>  $this->pd2Repository->getRemainingAvailableDoctorsForPatient($currentUser->getId())
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RelationsPd2::class,
        ]);
    }
}