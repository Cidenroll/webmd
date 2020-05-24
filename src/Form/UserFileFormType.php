<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\UserFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\File;

class UserFileFormType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * UserFileFormType constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userFile = new UserFile();

        $builder
            ->add('fileName', FileType::class, [
                'label' => 'Medical file (PDF)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => true,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '40M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->add('docType', ChoiceType::class, [
                'label' =>  'Document type',
                'mapped'    =>  false,
                'required'  =>  true,
                'choices'   =>  $userFile->getAvailableDocTypes()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserFile::class,
        ]);
    }
}
