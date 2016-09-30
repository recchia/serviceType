<?php
/**
 * Created by PhpStorm.
 * User: recchia
 * Date: 29/09/16
 * Time: 11:31
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ChargingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', TextType::class, [
                'label' => 'Cuenta',
                'constraints' => [
                    new NotBlank(['message' => 'Debe ingresar cuenta']),
                    new Type(['type' => 'digit', 'message' => 'El valor {{ value }} no es un {{ type }} v&aacute;lido'])
                ]
            ])
            ->add('service_type', HiddenType::class)
            ->add('send', ButtonType::class, ['label' => 'Enviar'])
            ;
    }
}