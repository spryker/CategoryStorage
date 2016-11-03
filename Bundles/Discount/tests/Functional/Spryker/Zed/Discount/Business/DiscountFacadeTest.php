<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Functional\Spryker\Zed\Discount\Business;

use Codeception\TestCase\Test;
use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\CollectedDiscountTransfer;
use Generated\Shared\Transfer\DiscountableItemTransfer;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\DiscountFacade;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperators;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\DiscountDependencyProvider;

/**
 * @group Functional
 * @group Spryker
 * @group Zed
 * @group Discount
 * @group Business
 * @group DiscountFacadeTest
 */
class DiscountFacadeTest extends Test
{

    /**
     * @return void
     */
    public function testIsSatisfiedBySkuShouldReturnTrueWhenGiveSkuIsInQuote()
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSku('123');
        $quoteTransfer->addItem($itemTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('123');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING
        ]);

        $isSatisfied = $discountFacade->isItemSkuSatisfiedBy($quoteTransfer, $itemTransfer, $clauseTransfer);

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testIsQuoteGrandTotalSatisfiedByShouldReturnTrueIfGrandTotalMatchesExpected()
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $totalTransfer = new TotalsTransfer();
        $totalTransfer->setGrandTotal(1000);
        $quoteTransfer->setTotals($totalTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue(10);
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER
        ]);

        $isSatisfied = $discountFacade->isQuoteGrandTotalSatisfiedBy(
            $quoteTransfer,
            new ItemTransfer(),
            $clauseTransfer
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testIsTotalQuantitySatisfiedByShouldReturnTrueWhenQuoteTotalQuantityMatchesExpected()
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setQuantity(2);
        $quoteTransfer->addItem($itemTransfer);

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setQuantity(3);
        $quoteTransfer->addItem($itemTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue(5);
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER
        ]);

        $isSatisfied = $discountFacade->isTotalQuantitySatisfiedBy(
            $quoteTransfer,
            $itemTransfer,
            $clauseTransfer
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testIsSubTotalSatisfiedByShouldReturnTrueWhenSubtotalMatchesExpected()
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $totalsTransfer = new TotalsTransfer();
        $totalsTransfer->setSubtotal(5000);
        $quoteTransfer->setTotals($totalsTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue(50);
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER
        ]);

        $isSatisfied = $discountFacade->isSubTotalSatisfiedBy(
            $quoteTransfer,
            new ItemTransfer(),
            $clauseTransfer
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testCollectBySkuShouldReturnItemMatchingGivenSku()
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSku('sku');
        $quoteTransfer->addItem($itemTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('sku');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER
        ]);

        $collected = $discountFacade->collectBySku($quoteTransfer, $clauseTransfer);

        $this->assertCount(1, $collected);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldsByTypeForCollectorShouldReturnListOfFieldsForGivenType()
    {
        $discountFacade = $this->createDiscountFacade();
        $fields = $discountFacade->getQueryStringFieldsByType(MetaProviderFactory::TYPE_COLLECTOR);

        $this->assertNotEmpty($fields);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldsByTypeForDecisionRuleShouldReturnListOfFieldsForGivenType()
    {
        $discountFacade = $this->createDiscountFacade();
        $fields = $discountFacade->getQueryStringFieldsByType(MetaProviderFactory::TYPE_DECISION_RULE);

        $this->assertNotEmpty($fields);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldExpressionsForFieldCollectorShouldReturnListOfExpressions()
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringFieldExpressionsForField(MetaProviderFactory::TYPE_COLLECTOR, 'sku');

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldExpressionsForFieldDecisionRuleShouldReturnListOfExpressions()
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringFieldExpressionsForField(MetaProviderFactory::TYPE_DECISION_RULE, 'sku');

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testGetQueryStringComparatorExpressionsForDecisionRuleShouldReturnListOfComparatorExpressions()
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringComparatorExpressions(MetaProviderFactory::TYPE_DECISION_RULE);

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testGetQueryStringComparatorExpressionsForCollectorShouldReturnListOfComparatorExpressions()
    {
        $discountFacade = $this->createDiscountFacade();
        $logicalComparators = $discountFacade->getQueryStringComparatorExpressions(MetaProviderFactory::TYPE_DECISION_RULE);

        $this->assertNotEmpty($logicalComparators);
    }

    /**
     * @return void
     */
    public function testGetQueryStringLogicalComparatorsShouldReturnListOfComparators()
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringLogicalComparators(MetaProviderFactory::TYPE_COLLECTOR);

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testQueryStringCompareShouldReturnTrueWhenValuesMatches()
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING
        ]);

        $withValue = 'value';

        $isMatching = $discountFacade->queryStringCompare($clauseTransfer, $withValue);

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testQueryStringCompareShouldReturnFalseWhenValuesNotMatching()
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING
        ]);

        $withValue = 'value2';

        $isMatching = $discountFacade->queryStringCompare($clauseTransfer, $withValue);

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testValidateQueryStringByTypeShouldReturnListErrorsWhenInvalidQueryString()
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING
        ]);

        $errors = $discountFacade->validateQueryStringByType(MetaProviderFactory::TYPE_DECISION_RULE, 'invalid =');

        $this->assertCount(1, $errors);
    }

    /**
     * @return void
     */
    public function testSaveDiscountDecisionRuleShouldPersistAllConfiguredData()
    {
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();

        $discountFacade = $this->createDiscountFacade();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $this->assertNotEmpty($idDiscount);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral();
        $this->assertEquals($discountGeneralTransfer->getDisplayName(), $discountEntity->getDisplayName());
        $this->assertEquals($discountGeneralTransfer->getIsActive(), $discountEntity->getIsActive());
        $this->assertEquals($discountGeneralTransfer->getIsExclusive(), $discountEntity->getIsExclusive());
        $this->assertEquals($discountGeneralTransfer->getDescription(), $discountEntity->getDescription());
        $this->assertEquals($discountGeneralTransfer->getValidFrom(), $discountEntity->getValidFrom());
        $this->assertEquals($discountGeneralTransfer->getValidTo(), $discountEntity->getValidTo());

        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();
        $this->assertEquals($discountCalculatorTransfer->getAmount(), $discountEntity->getAmount());
        $this->assertEquals($discountCalculatorTransfer->getCalculatorPlugin(), $discountEntity->getCalculatorPlugin());
        $this->assertEquals($discountCalculatorTransfer->getCollectorQueryString(), $discountEntity->getCollectorQueryString());

        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition();
        $this->assertEquals($discountConditionTransfer->getDecisionRuleQueryString(), $discountEntity->getDecisionRuleQueryString());
    }

    /**
     * @return void
     */
    public function testSaveDiscountVoucherShouldCreateDiscountWithEmptyVoucherPool()
    {
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer->getDiscountGeneral()
            ->setDiscountType(DiscountConstants::TYPE_VOUCHER);

        $discountFacade = $this->createDiscountFacade();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $this->assertNotEmpty($idDiscount);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertNotEmpty($discountEntity->getFkDiscountVoucherPool());

        $voucherPool = $discountEntity->getVoucherPool();
        $this->assertNotEmpty($voucherPool);
    }

    /**
     * @return void
     */
    public function testValidateQueryStringByTypeShouldReturnEmptySetWhenQueryStringIsValid()
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING
        ]);

        $errors = $discountFacade->validateQueryStringByType(MetaProviderFactory::TYPE_DECISION_RULE, 'sku = "123"');

        $this->assertEmpty($errors);
    }

    /**
     * @return void
     */
    public function testUpdateDiscountShouldUpdateExistingRecordWithNewData()
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountConfiguratorTransfer
            ->getDiscountGeneral()
            ->setIdDiscount($idDiscount);

        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral();
        $discountGeneralTransfer->setDisplayName('updated functional discount facade test');
        $discountGeneralTransfer->setDescription('Updated description');
        $discountGeneralTransfer->setIsActive(false);
        $discountGeneralTransfer->setIsExclusive(false);
        $discountGeneralTransfer->setValidFrom(new \DateTime());
        $discountGeneralTransfer->setValidTo(new \DateTime());

        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();
        $discountCalculatorTransfer->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED);
        $discountCalculatorTransfer->setAmount(5);
        $discountCalculatorTransfer->setCollectorQueryString('sku = "new-sku"');

        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition();
        $discountConditionTransfer->setDecisionRuleQueryString('sku = "new-decision-sku"');

        $isUpdated = $discountFacade->updateDiscount($discountConfiguratorTransfer);

        $this->assertTrue($isUpdated);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertEquals($discountGeneralTransfer->getDisplayName(), $discountEntity->getDisplayName());
        $this->assertEquals($discountGeneralTransfer->getIsActive(), $discountEntity->getIsActive());
        $this->assertEquals($discountGeneralTransfer->getIsExclusive(), $discountEntity->getIsExclusive());
        $this->assertEquals($discountGeneralTransfer->getDescription(), $discountEntity->getDescription());
        $this->assertEquals($discountGeneralTransfer->getValidFrom(), $discountEntity->getValidFrom());
        $this->assertEquals($discountGeneralTransfer->getValidTo(), $discountEntity->getValidTo());

        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();
        $this->assertEquals($discountCalculatorTransfer->getAmount(), $discountEntity->getAmount());
        $this->assertEquals($discountCalculatorTransfer->getCalculatorPlugin(), $discountEntity->getCalculatorPlugin());
        $this->assertEquals($discountCalculatorTransfer->getCollectorQueryString(), $discountEntity->getCollectorQueryString());

        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition();
        $this->assertEquals($discountConditionTransfer->getDecisionRuleQueryString(), $discountEntity->getDecisionRuleQueryString());
    }

    /**
     * @return void
     */
    public function testGetHydratedDiscountConfiguratorByIdDiscountShouldHydrateToSameObjectWhichWasPersisted()
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);
        $discountConfiguratorTransfer->getDiscountGeneral()->setIdDiscount($idDiscount);

        $hydratedDiscountConfiguratorTransfer = $discountFacade->getHydratedDiscountConfiguratorByIdDiscount(
            $idDiscount
        );

        $originalConfiguratorArray = $discountConfiguratorTransfer->toArray();
        $hydratedConfiguratorArray = $hydratedDiscountConfiguratorTransfer->toArray();

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertEquals($originalConfiguratorArray, $hydratedConfiguratorArray);
        $this->assertTrue($discountEntity->getIsActive());
    }

    /**
     * @return void
     */
    public function testToggleDiscountVisibilityWhenDisableShouldSetToIsActiveToFalse()
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $isUpdated = $discountFacade->toggleDiscountVisibility($idDiscount, false);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertTrue($isUpdated);
        $this->assertFalse($discountEntity->getIsActive());
    }

    /**
     * @return void
     */
    public function testToggleDiscountVisibilityWhenDisableShouldSetToIsActiveToTrue()
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer->getDiscountGeneral()->setIsActive(false);
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $isUpdated = $discountFacade->toggleDiscountVisibility($idDiscount, true);

        $this->assertTrue($isUpdated);
    }

    /**
     * @return void
     */
    public function testSaveVouchersShouldGenerateVouchers()
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);
        $discountVoucherTransfer->setCustomCode('functional spryker test voucher');
        $discountVoucherTransfer->setMaxNumberOfUses(0);
        $discountVoucherTransfer->setQuantity(5);
        $discountVoucherTransfer->setRandomGeneratedCodeLength(10);

        $voucherCreateInfoTransfer = $discountFacade->saveVoucherCodes($discountVoucherTransfer);

        $this->assertEquals($voucherCreateInfoTransfer->getType(), DiscountConstants::MESSAGE_TYPE_SUCCESS);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $voucherPoolEntity = $discountEntity->getVoucherPool();
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();

        $this->assertCount(5, $voucherCodes);
    }

    /**
     * @return void
     */
    public function testCalculatedPercentageShouldCalculatePercentageFromItemTotal()
    {
        $discountableItems = [];

        $itemTransfer = new ItemTransfer();
        $calculatedDiscounts = new \ArrayObject();
        $itemTransfer->setCalculatedDiscounts($calculatedDiscounts);

        $discountableItemTransfer = new DiscountableItemTransfer();
        $discountableItemTransfer->setQuantity(3);
        $discountableItemTransfer->setUnitGrossPrice(30);
        $discountableItemTransfer->setOriginalItemCalculatedDiscounts($calculatedDiscounts);
        $discountableItems[] = $discountableItemTransfer;

        $discountFacade = $this->createDiscountFacade();

        $amount = $discountFacade->calculatePercentage($discountableItems, 10 * 100);

        $this->assertEquals(9, $amount);
    }

    /**
     * @return void
     */
    public function testCalculatedFixedShouldUseFixedAmountGiver()
    {
        $discountFacade = $this->createDiscountFacade();
        $amount = $discountFacade->calculateFixed([], 50);

        $this->assertEquals(50, $amount);
    }

    /**
     * @return void
     */
    public function testDistributeAmountShouldDistributeDiscountToDiscountableItems()
    {
        $collectedDiscountTransfer = new CollectedDiscountTransfer();

        $totalDiscountAmount = 100;
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setAmount($totalDiscountAmount);
        $collectedDiscountTransfer->setDiscount($discountTransfer);

        $discountableItems = new \ArrayObject();

        foreach ([100, 600] as $price) {
            $itemTransfer = new ItemTransfer();
            $calculatedDiscounts = new \ArrayObject();
            $itemTransfer->setCalculatedDiscounts($calculatedDiscounts);

            $discountableItemTransfer = new DiscountableItemTransfer();
            $discountableItemTransfer->setQuantity(1);
            $discountableItemTransfer->setUnitGrossPrice($price);
            $discountableItemTransfer->setOriginalItemCalculatedDiscounts($calculatedDiscounts);
            $discountableItems->append($discountableItemTransfer);
        }

        $collectedDiscountTransfer->setDiscountableItems($discountableItems);

        $discountFacade = $this->createDiscountFacade();
        $discountFacade->distributeAmount($collectedDiscountTransfer);

        $firstItemDistributedAmount = $discountableItems[0]->getOriginalItemCalculatedDiscounts()[0]->getUnitGrossAmount();
        $secondItemDistributedAmount = $discountableItems[1]->getOriginalItemCalculatedDiscounts()[0]->getUnitGrossAmount();

        $this->assertEquals(14, $firstItemDistributedAmount);
        $this->assertEquals(86, $secondItemDistributedAmount);
        $this->assertEquals($totalDiscountAmount, $firstItemDistributedAmount + $secondItemDistributedAmount);
    }

    /**
     * @return void
     */
    public function testReleaseUsedVoucherCodesShouldSetNumberOfUsesCounterBack()
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);
        $discountVoucherTransfer->setCustomCode('functional spryker test voucher');
        $discountVoucherTransfer->setMaxNumberOfUses(5);
        $discountVoucherTransfer->setQuantity(1);
        $discountVoucherTransfer->setRandomGeneratedCodeLength(3);

        $discountFacade->saveVoucherCodes($discountVoucherTransfer);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $voucherPoolEntity = $discountEntity->getVoucherPool();
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();

        $voucherCodeList = [];
        foreach ($voucherCodes as $voucherCodeEntity) {
            $voucherCodeEntity->setNumberOfUses(1);
            $voucherCodeEntity->save();
            $voucherCodeList[] = $voucherCodeEntity->getCode();
        }

        $released = $discountFacade->releaseUsedVoucherCodes($voucherCodeList);

        $this->assertEquals(1, $released);
    }

    /**
     * @return void
     */
    public function testUseVoucherCodesShouldUpdateVoucherCounterThatItWasAlreadyUsed()
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);
        $discountVoucherTransfer->setCustomCode('functional spryker test voucher');
        $discountVoucherTransfer->setMaxNumberOfUses(5);
        $discountVoucherTransfer->setQuantity(1);
        $discountVoucherTransfer->setRandomGeneratedCodeLength(3);

        $discountFacade->saveVoucherCodes($discountVoucherTransfer);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $voucherPoolEntity = $discountEntity->getVoucherPool();
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();

        $voucherCodeList = [];
        foreach ($voucherCodes as $voucherCodeEntity) {
            $voucherCodeList[] = $voucherCodeEntity->getCode();
        }

        $discountFacade->useVoucherCodes($voucherCodeList);

        $voucherPoolEntity->reload(true);
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();
        $voucherCodeEntity = $voucherCodes[0];

        $this->assertEquals(1, $voucherCodeEntity->getNumberOfUses());
    }

    /**
     * @return \Spryker\Zed\Discount\Business\DiscountFacade
     */
    protected function createDiscountFacade()
    {
        $discountFacade = new DiscountFacade();

        return $discountFacade;
    }

    /**
     * @return \Generated\Shared\Transfer\DiscountConfiguratorTransfer
     */
    protected function createDiscountConfiguratorTransfer()
    {
        $discountConfiguratorTransfer = new DiscountConfiguratorTransfer();

        $discountGeneralTransfer = new DiscountGeneralTransfer();
        $discountGeneralTransfer->setDisplayName('functional discount facade test');
        $discountGeneralTransfer->setDiscountType(DiscountConstants::TYPE_CART_RULE);
        $discountGeneralTransfer->setIsActive(true);
        $discountGeneralTransfer->setIsExclusive(true);
        $discountGeneralTransfer->setDescription('Description');
        $discountGeneralTransfer->setValidFrom(new \DateTime());
        $discountGeneralTransfer->setValidTo(new \DateTime());
        $discountConfiguratorTransfer->setDiscountGeneral($discountGeneralTransfer);

        $discountCalculatorTransfer = new DiscountCalculatorTransfer();
        $discountCalculatorTransfer->setAmount(10);
        $discountCalculatorTransfer->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED);
        $discountCalculatorTransfer->setCollectorQueryString('sku = "123"');
        $discountConfiguratorTransfer->setDiscountCalculator($discountCalculatorTransfer);

        $discountConditionTransfer = new DiscountConditionTransfer();
        $discountConditionTransfer->setDecisionRuleQueryString('sku = "123"');
        $discountConfiguratorTransfer->setDiscountCondition($discountConditionTransfer);

        return $discountConfiguratorTransfer;
    }

}
