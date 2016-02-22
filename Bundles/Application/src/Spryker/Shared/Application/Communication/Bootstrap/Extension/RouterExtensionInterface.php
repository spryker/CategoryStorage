<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Application\Communication\Bootstrap\Extension;

use Spryker\Shared\Application\Communication\Application;

interface RouterExtensionInterface
{

    /**
     * @param \Spryker\Shared\Application\Communication\Application $application
     *
     * @return \Symfony\Component\Routing\RouterInterface[]
     */
    public function getRouter(Application $application);

}
