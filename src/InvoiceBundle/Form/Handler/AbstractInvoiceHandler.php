<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\InvoiceBundle\Form\Handler;

use SolidInvoice\CoreBundle\Response\FlashResponse;
use SolidInvoice\CoreBundle\Traits\SaveableTrait;
use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\InvoiceBundle\Entity\RecurringInvoice;
use SolidInvoice\InvoiceBundle\Form\Type\InvoiceType;
use SolidInvoice\InvoiceBundle\Form\Type\RecurringInvoiceType;
use SolidInvoice\InvoiceBundle\Model\Graph;
use SolidWorx\FormHandler\FormHandlerInterface;
use SolidWorx\FormHandler\FormHandlerOptionsResolver;
use SolidWorx\FormHandler\FormHandlerResponseInterface;
use SolidWorx\FormHandler\FormHandlerSuccessInterface;
use SolidWorx\FormHandler\FormRequest;
use SolidWorx\FormHandler\Options;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Workflow\StateMachine;

abstract class AbstractInvoiceHandler implements FormHandlerInterface, FormHandlerResponseInterface, FormHandlerSuccessInterface, FormHandlerOptionsResolver
{
    use SaveableTrait;

    /**
     * @var StateMachine
     */
    private $invoiceStateMachine;

    /**
     * @var StateMachine
     */
    private $recurringInvoiceStateMachine;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(StateMachine $invoiceStateMachine, StateMachine $recurringInvoiceStateMachine, RouterInterface $router)
    {
        $this->invoiceStateMachine = $invoiceStateMachine;
        $this->recurringInvoiceStateMachine = $recurringInvoiceStateMachine;
        $this->router = $router;
    }

    public function getForm(FormFactoryInterface $factory, Options $options)
    {
        $formType = $options->get('recurring') ? RecurringInvoiceType::class : InvoiceType::class;

        return $factory->create($formType, $options->get('invoice'), $options->get('form_options'));
    }

    public function onSuccess(FormRequest $form, $data): ?Response
    {
        /** @var Invoice $data */
        $action = $form->getRequest()->request->get('save');
        $isRecurring = $form->getOptions()->get('recurring');

        if (! $data->getId()) {
            ($isRecurring ? $this->recurringInvoiceStateMachine : $this->invoiceStateMachine)->apply($data, Graph::TRANSITION_NEW);
        }

        if (Graph::STATUS_PENDING === $action) {
            ($isRecurring ? $this->recurringInvoiceStateMachine : $this->invoiceStateMachine)->apply($data, $isRecurring ? Graph::TRANSITION_ACTIVATE : Graph::TRANSITION_ACCEPT);
        }

        $this->save($data);
        $route = $this->router->generate($isRecurring ? '_invoices_view_recurring' : '_invoices_view', ['id' => $data->getId()]);

        return new class($route) extends RedirectResponse implements FlashResponse {
            public function getFlash(): \Generator
            {
                yield self::FLASH_SUCCESS => 'invoice.create.success';
            }
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('invoice')
            ->setAllowedTypes('invoice', [Invoice::class, RecurringInvoice::class])
            ->setDefault('form_options', [])
            ->setDefault('recurring', false)
            ->setAllowedTypes('form_options', 'array')
            ->setAllowedTypes('recurring', 'boolean');
    }
}
