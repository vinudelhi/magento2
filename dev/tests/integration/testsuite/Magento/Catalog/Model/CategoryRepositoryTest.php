<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Backend\Model\Auth;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\CacheInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Bootstrap as TestBootstrap;

/**
 * Provide tests for CategoryRepository model.
 */
class CategoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test subject.
     *
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var Auth
     */
    private $authorization;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var CacheInterface
     */
    private $aclCache;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->repository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->authorization = Bootstrap::getObjectManager()->get(Auth::class);
        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
        $this->aclCache = Bootstrap::getObjectManager()->get(CacheInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->authorization->logout();
        $this->aclCache->clean();
    }

    /**
     * Test authorization when saving category's design settings.
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSaveDesign()
    {
        $category = $this->repository->get(333);
        $this->authorization->login(TestBootstrap::ADMIN_NAME, TestBootstrap::ADMIN_PASSWORD);

        //Admin doesn't have access to category's design.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_category_design');

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repository->save($category);
        $this->assertEmpty($category->getCustomAttribute('custom_design'));

        //Admin has access to category' design.
        $this->aclBuilder->getAcl()
            ->allow(null, ['Magento_Catalog::categories', 'Magento_Catalog::edit_category_design']);

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repository->save($category);
        $this->assertNotEmpty($category->getCustomAttribute('custom_design'));
        $this->assertEquals(2, $category->getCustomAttribute('custom_design')->getValue());
    }
}
