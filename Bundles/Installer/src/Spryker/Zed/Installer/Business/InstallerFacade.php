<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Installer\Business;

use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\Installer\Business\InstallerBusinessFactory getFactory()
 */
class InstallerFacade extends AbstractFacade implements InstallerFacadeInterface
{

    /**
     * @return \Spryker\Zed\Installer\Business\Model\AbstractInstaller[]
     */
    public function getInstallers()
    {
        return $this->getFactory()->getInstallers();
    }

    /**
     * @return \Spryker\Zed\Installer\Business\Model\AbstractInstaller[]
     */
    public function getDemoDataInstallers()
    {
        return $this->getFactory()->getDemoDataInstallers();
    }

    /**
     * @return \Spryker\Zed\Installer\Business\Model\GlossaryInstaller
     */
    public function getGlossaryInstaller()
    {
        return $this->getFactory()->createGlossaryInstaller();
    }

}
