<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Application\Business\Model\Navigation\Cache;

interface NavigationCacheInterface
{

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @param array $navigation
     */
    public function setNavigation(array $navigation);

    /**
     * @return array
     */
    public function getNavigation();

}
