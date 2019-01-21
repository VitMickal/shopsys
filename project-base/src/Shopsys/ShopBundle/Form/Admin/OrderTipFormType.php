<?php

namespace Shopsys\ShopBundle\Form\Admin;

use Shopsys\ShopBundle\Model\Order\Item\OrderItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class OrderTipFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('First name'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter first name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'First name cannot be longer then {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('someBoolean', CheckboxType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => OrderItemData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
