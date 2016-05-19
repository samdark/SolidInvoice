<?php

/*
 * This file is part of CSBill project.
 *
 * (c) 2013-2016 Pierre du Plessis <info@customscripts.co.za>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace CSBill\PaymentBundle\Form\Methods;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthorizeNetAim extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'login_id',
            'text',
            array(
                'constraints' => new NotBlank(),
            )
        );

        $builder->add(
            'transaction_key',
            'text',
            array(
                'constraints' => new NotBlank(),
            )
        );

        $builder->add(
            'sandbox',
            'checkbox',
            array(
                'required' => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'authorize_net_aim';
    }
}