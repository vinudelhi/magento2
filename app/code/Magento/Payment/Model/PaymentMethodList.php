<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface;

/**
 * Payment method list class.
 */
class PaymentMethodList implements \Magento\Payment\Api\PaymentMethodListInterface
{
    /**
     * @var \Magento\Payment\Api\Data\PaymentMethodInterfaceFactory
     */
    private $methodFactory;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Payment\Api\Data\PaymentMethodInterfaceFactory $methodFactory
     * @param \Magento\Payment\Helper\Data $helper
     */
    public function __construct(
        \Magento\Payment\Api\Data\PaymentMethodInterfaceFactory $methodFactory,
        \Magento\Payment\Helper\Data $helper
    ) {
        $this->methodFactory = $methodFactory;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($storeId)
    {
        $methodsInstances = $this->helper->getStoreMethods($storeId);

        $methodsInstances = array_filter($methodsInstances, function (MethodInterface $method) {
            return !($method instanceof \Magento\Payment\Model\Method\Substitution);
        });

        $methodList = array_map(
            function (MethodInterface $methodInstance) use ($storeId) {

                return $this->methodFactory->create([
                    'code' => (string)$methodInstance->getCode(),
                    'title' => (string)$methodInstance->getTitle(),
                    'storeId' => (int)$storeId,
                    'isActive' => (bool)$methodInstance->isActive($storeId)
                ]);
            },
            $methodsInstances
        );

        return array_values($methodList);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveList($storeId)
    {
        $methodList = array_filter(
            $this->getList($storeId),
            function (PaymentMethodInterface $method) {
                return $method->getIsActive();
            }
        );

        return array_values($methodList);
    }
}
