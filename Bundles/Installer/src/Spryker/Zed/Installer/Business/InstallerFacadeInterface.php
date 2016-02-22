<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Installer\Business;

interface InstallerFacadeInterface
{

    /**
     * @return \Spryker\Zed\Installer\Business\Model\AbstractInstaller[]
     */
    public function getInstallers();

    /**
     * @return \Spryker\Zed\Installer\Business\Model\AbstractInstaller[]
     */
    public function getDemoDataInstallers();

    /**
     * @return \Spryker\Zed\Installer\Business\Model\GlossaryInstaller
     */
    public function getGlossaryInstaller();

}
