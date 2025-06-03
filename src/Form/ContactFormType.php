<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType, EmailType, TelType, ChoiceType, TextareaType, SubmitType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr'  => ['placeholder' => 'Nom', 'class' => 'form-control form-control-sm'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre nom.']),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr'  => ['placeholder' => 'Prénom', 'class' => 'form-control form-control-sm'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre prénom.']),
                ],
            ])
            ->add('entreprise', TextType::class, [
                'label' => 'Nom de votre entreprise',
                'required' => false,
                'attr'  => ['placeholder' => 'Entreprise…', 'class' => 'form-control form-control-sm'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail',
                'attr'  => ['placeholder' => 'exemple@domaine.com', 'class' => 'form-control form-control-sm'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre adresse e-mail.']),
                ],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
                'attr'  => ['placeholder' => '00 00 00 00 00', 'class' => 'form-control form-control-sm'],
            ])
            ->add('service', ChoiceType::class, [
                'label' => 'Quel service souhaitez-vous ?',
                'choices' => [
                    'Systèmes Informatiques' => [
                        'Système Lorem 1' => 'Système Lorem 1',
                        'Système Lorem 2' => 'Système Lorem 2',
                        'Système Lorem 3' => 'Système Lorem 3',
                    ],
                    'Logiciels Informatiques' => [
                        'Logiciel Lorem A' => 'Logiciel Lorem A',
                        'Logiciel Lorem B' => 'Logiciel Lorem B',
                        'Logiciel Lorem C' => 'Logiciel Lorem C',
                    ],
                ],
                'attr'  => ['class' => 'form-select form-select-sm'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un service.']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => false,
                'attr'  => ['placeholder' => 'Écrivez ici des informations supplémentaires…', 'rows' => 4, 'class' => 'form-control form-control-sm'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre message.']),
                ],
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Envoyer',
                'attr'  => ['class' => 'btn btn-sm', 'style' => 'background-color: #A65330; color: #fff;'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'contact_item',
        ]);
    }
}