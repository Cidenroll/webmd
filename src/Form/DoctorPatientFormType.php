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
use App\Repository\DoctorToPatientRepository;
use App\Repository\RelationsDp2Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class DoctorPatientFormType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RelationsDp2Repository
     */
    private $doctorToPatientRepository;

    /**
     * UserFileFormType constructor.
     * @param Security $security
     */
    public function __construct(Security $security, RelationsDp2Repository $relationsDp2Repository )
    {
        $this->security = $security;

        $this->doctorToPatientRepository = $relationsDp2Repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentUser = $this->security->getUser();
        $builder
            ->add('patientId', ChoiceType::class, [
                'label' =>  'Patient',
                'mapped'    =>  false,
                'required'  =>  false,
                'choices'   =>  $this->doctorToPatientRepository->getRemainingAvailablePatientsForDoctor($currentUser->getId())
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RelationsDp2::class,
        ]);
    }
}