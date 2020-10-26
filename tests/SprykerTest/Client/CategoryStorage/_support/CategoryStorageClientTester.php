<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\CategoryStorage;

use Codeception\Actor;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\CategoryStorage\CategoryStorageClientInterface;
use Spryker\Client\CategoryStorage\CategoryStorageConfig;
use Spryker\Client\CategoryStorage\CategoryStorageDependencyProvider;
use Spryker\Client\Kernel\Container;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class CategoryStorageClientTester extends Actor
{
    use _generated\CategoryStorageClientTesterActions;

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\CategoryStorage\CategoryStorageFactory $categoryStorageFactoryMock
     *
     * @return \Spryker\Client\CategoryStorage\CategoryStorageClientInterface
     */
    public function getClientMock(MockObject $categoryStorageFactoryMock): CategoryStorageClientInterface
    {
        $categoryStorageFactoryMock
            ->method('getConfig')
            ->willReturn(new CategoryStorageConfig());

        $container = new Container();
        $categoryStorageDependencyProvider = new CategoryStorageDependencyProvider();
        $categoryStorageDependencyProvider->provideServiceLayerDependencies($container);

        $categoryStorageFactoryMock->setContainer($container);

        /** @var \Spryker\Client\CategoryStorage\CategoryStorageClient $categoryStorageClient */
        $categoryStorageClient = $this->getClient();
        $categoryStorageClient->setFactory($categoryStorageFactoryMock);

        return $categoryStorageClient;
    }

    /**
     * @return \Spryker\Client\CategoryStorage\CategoryStorageClientInterface
     */
    public function getClient(): CategoryStorageClientInterface
    {
        return $this->getLocator()->categoryStorage()->client();
    }
}
