<?php

namespace Shopsys\ShopBundle\Tests\Acceptance\acceptance;

use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Model\Script\ScriptFacade;
use Shopsys\ShopBundle\Tests\Acceptance\acceptance\PageObject\Front\OrderPage;
use Shopsys\ShopBundle\Tests\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Shopsys\ShopBundle\Tests\Test\Codeception\AcceptanceTester;
use Shopsys\ShopBundle\Tests\Test\Codeception\Helper\SymfonyHelper;

class OrderCest
{
    public function testFormRemembersPaymentAndTransportWhenClickingBack(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('have my payment and transport remebered by order');

        $me->amOnPage('/tv-audio/');
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $me->clickByText('Go to cart');
        $me->clickByText('Order');

        $orderPage->assertTransportIsNotSelected('Czech post');
        $orderPage->selectTransport('Czech post');
        $orderPage->assertPaymentIsNotSelected('Personal collection');
        $orderPage->selectPayment('Personal collection');
        $me->waitForAjax();
        $me->clickByText('Continue in order');
        $me->clickByText('Back to shipping and payment selection');

        $orderPage->assertTransportIsSelected('Czech post');
        $orderPage->assertPaymentIsSelected('Personal collection');
    }

    public function testFormRemembersPaymentAndTransportWhenGoingDirectlyToUrl(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('have my payment and transport remebered by order');

        $me->amOnPage('/tv-audio/');
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $me->clickByText('Go to cart');
        $me->clickByText('Order');

        $orderPage->assertTransportIsNotSelected('Czech post');
        $orderPage->selectTransport('Czech post');
        $orderPage->assertPaymentIsNotSelected('Personal collection');
        $orderPage->selectPayment('Personal collection');
        $me->waitForAjax();
        $me->clickByText('Continue in order');
        $me->amOnPage('/order/');

        $orderPage->assertTransportIsSelected('Czech post');
        $orderPage->assertPaymentIsSelected('Personal collection');
    }

    public function testFormRemembersFirstName(ProductListPage $productListPage, OrderPage $orderPage, AcceptanceTester $me)
    {
        $me->wantTo('have my first name remebered by order');

        $me->amOnPage('/tv-audio/');
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $me->clickByText('Go to cart');
        $me->clickByText('Order');
        $orderPage->selectTransport('Czech post');
        $orderPage->selectPayment('Personal collection');
        $me->waitForAjax();
        $me->clickByText('Continue in order');

        $orderPage->fillFirstName('Jan');
        $me->clickByText('Back to shipping and payment selection');
        $me->amOnPage('/order/');
        $me->clickByText('Continue in order');

        $orderPage->assertFirstNameIsFilled('Jan');
    }

    public function testOrderCanBeCompletedAndHasGoogleAnalyticsTrackingIdInSource(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me,
        SymfonyHelper $symfonyHelper
    ) {
        $this->setGoogleAnalyticsTrackingId('GA-test', $symfonyHelper->grabServiceFromContainer(ScriptFacade::class));

        $this->testOrderCanBeCompleted($productListPage, $orderPage, $me);

        $me->seeInSource('GA-test');
    }

    /**
     * @param string $trackingId
     * @param \Shopsys\ShopBundle\Model\Script\ScriptFacade $scriptFacade
     */
    private function setGoogleAnalyticsTrackingId($trackingId, ScriptFacade $scriptFacade)
    {
        $scriptFacade->setGoogleAnalyticsTrackingId($trackingId, Domain::FIRST_DOMAIN_ID);
    }

    private function testOrderCanBeCompleted(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me
    ) {
        $me->amOnPage('/tv-audio/');
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $me->clickByText('Go to cart');
        $me->clickByText('Order');

        $orderPage->selectTransport('Czech post');
        $orderPage->selectPayment('Personal collection');
        $me->waitForAjax();
        $me->clickByText('Continue in order');

        $orderPage->fillPersonalInfo('Karel', 'Novák', 'no-reply@shopsys.com', '123456789');
        $orderPage->fillBillingAddress('Koksární 10', 'Ostrava', '702 00');
        $orderPage->acceptTermsAndConditions();

        $me->clickByText('Finish the order');

        $me->see('Order sent');
    }
}