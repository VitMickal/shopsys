<?php

namespace Shopsys\ShopBundle\Form\Admin;

use Shopsys\FrameworkBundle\Form\OrderItemsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class OrderItemsFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderTip', OrderTipFormType::class, []);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OrderItemsType::class;
    }
}
