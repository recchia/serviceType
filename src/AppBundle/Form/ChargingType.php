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

class ChargingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', TextType::class, ['label' => 'Cuenta'])
            ->add('service_type', HiddenType::class)
            ->add('send', ButtonType::class, ['label' => 'Enviar'])
            ;
    }
}