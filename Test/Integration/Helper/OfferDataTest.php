<?php

namespace MageSuite\DailyDeal\Test\Integration\Helper;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OfferDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\DailyDeal\Helper\OfferData
     *
     */
    protected $offerDataHelper;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->offerDataHelper = $this->objectManager->get(\MageSuite\DailyDeal\Helper\OfferData::class);

        $priceRender = $this->objectManager->get(\Magento\Framework\View\LayoutInterface::class)->getBlock('product.price.render.default');

        if (!$priceRender) {
            $this->objectManager->get(\Magento\Framework\View\LayoutInterface::class)->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                    ],
                ]
            );
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItReturnEnabledDeal()
    {
        $offerData = $this->offerDataHelper->prepareOfferData(600);

        $this->assertArrayHasKey('deal', $offerData);

        $this->assertTrue($offerData['deal']);
        $this->assertEquals(50, $offerData['items']);
        $this->assertEquals(60, $offerData['initialAmount']);
        $this->assertEquals(1521417600, $offerData['from']);
        $this->assertEquals(1931932800, $offerData['to']);
        $this->assertEquals('5.0000', $offerData['price']);
        $this->assertEquals('none', $offerData['displayType']);

        $this->assertEquals(30, $offerData['oldDiscount']);

        $this->assertContains('$10.00', $offerData['oldPriceHtmlOnTile']);
        $this->assertNotContains('$5.00', $offerData['oldPriceHtmlOnTile']);
        $this->assertContains('$10.00', $offerData['oldPriceHtmlOnPdp']);
        $this->assertNotContains('$5.00', $offerData['oldPriceHtmlOnPdp']);

    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItReturnDisabledDeal()
    {
        $offerData = $this->offerDataHelper->prepareOfferData(601);

        $this->assertFalse($offerData['deal']);

        $this->assertEquals(2, $offerData['items']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 0
     */
    public function testItReturnCorrectDataWithDisabledLimit()
    {
        $offerData = $this->offerDataHelper->prepareOfferData(600);

        $this->assertArrayHasKey('deal', $offerData);

        $this->assertTrue($offerData['deal']);

        $this->assertEquals(0, $offerData['items']);
        $this->assertEquals('5.0000', $offerData['price']);
    }

    public static function loadProducts()
    {
        require __DIR__ . '/../_files/products.php';
    }

    public static function loadProductsRollback()
    {
        require __DIR__ . '/../_files/products_rollback.php';
    }
}