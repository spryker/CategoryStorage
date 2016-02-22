<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Auth\Dependency\Plugin;

interface AuthPasswordResetSenderInterface
{

    /**
     * @param string $email
     * @param string $token
     *
     * @return mixed
     */
    public function send($email, $token);

}
