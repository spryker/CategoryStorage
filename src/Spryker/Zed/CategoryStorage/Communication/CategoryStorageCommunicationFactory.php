<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CategoryStorage\Communication;

use Spryker\Zed\CategoryStorage\CategoryStorageDependencyProvider;
use Spryker\Zed\CategoryStorage\Dependency\Facade\CategoryStorageToCategoryFacadeInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;

/**
 * @method \Spryker\Zed\CategoryStorage\Persistence\CategoryStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CategoryStorage\CategoryStorageConfig getConfig()
 * @method \Spryker\Zed\CategoryStorage\Business\CategoryStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\CategoryStorage\Persistence\CategoryStorageEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\CategoryStorage\Persistence\CategoryStorageRepositoryInterface getRepository()
 */
class CategoryStorageCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \Spryker\Zed\CategoryStorage\Dependency\Facade\CategoryStorageToEventBehaviorFacadeInterface
     */
    public function getEventBehaviorFacade()
    {
        return $this->getProvidedDependency(CategoryStorageDependencyProvider::FACADE_EVENT_BEHAVIOR);
    }

    /**
     * @return \Spryker\Zed\CategoryStorage\Dependency\Facade\CategoryStorageToCategoryFacadeInterface
     */
    public function getCategoryFacade(): CategoryStorageToCategoryFacadeInterface
    {
        return $this->getProvidedDependency(CategoryStorageDependencyProvider::FACADE_CATEGORY);
    }
}
