<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Copyright © MultiSafepay, Inc. All rights reserved.
 * See DISCLAIMER.md for disclaimer details.
 */

declare(strict_types=1);

namespace MultiSafepay\MagewireCheckout\Payment;

use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Magewire\Checkout\Payment\MethodList;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\QuoteRepository;
use MultiSafepay\ConnectCore\Config\Config;

class MethodListExtended extends MethodList
{
    public ?string $method = null;

    public function __construct(
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $cartRepository,
        EvaluationResultFactory $evaluationResultFactory,
        private readonly Config $config,
        protected QuoteRepository $quoteRepository,
    ) {
        parent::__construct(
            $sessionCheckout,
            $cartRepository,
            $evaluationResultFactory
        );
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getPaymentFromQuote(): Payment
    {
        return $this->sessionCheckout->getQuote()->getPayment();
    }

    public function boot(): void
    {
        try {
            $method = $this->sessionCheckout->getQuote()->getPayment()->getMethod();

            if ($this->config->getPreselectedMethod() && !$method) {
                $this->updatedMethod($this->config->getPreselectedMethod());
                $method = $this->config->getPreselectedMethod();
            }
        } catch (LocalizedException $exception) {
            $method = null;
        }

        $this->method = $method;
    }
}
