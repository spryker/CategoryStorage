<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\StepEngine\Process;

use Spryker\Shared\Transfer\AbstractTransfer;
use Spryker\Yves\StepEngine\Dependency\Step\StepInterface;
use Symfony\Component\HttpFoundation\Request;

interface StepCollectionInterface
{

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $step
     *
     * @return $this
     */
    public function addStep(StepInterface $step);

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $step
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Spryker\Shared\Transfer\AbstractTransfer $dataTransfer
     *
     * @return mixed
     */
    public function canAccessStep(StepInterface $step, Request $request, AbstractTransfer $dataTransfer);

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Spryker\Shared\Transfer\AbstractTransfer $dataTransfer
     *
     * @return \Spryker\Yves\StepEngine\Dependency\Step\StepInterface
     */
    public function getCurrentStep(Request $request, AbstractTransfer $dataTransfer);

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $currentStep
     *
     * @return null|\Spryker\Yves\StepEngine\Dependency\Step\StepInterface
     */
    public function getNextStep(StepInterface $currentStep);

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $currentStep
     *
     * @return \Spryker\Yves\StepEngine\Dependency\Step\StepInterface
     */
    public function getPreviousStep(StepInterface $currentStep);

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $currentStep
     *
     * @return string
     */
    public function getCurrentUrl(StepInterface $currentStep);

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $currentStep
     * @param \Spryker\Shared\Transfer\AbstractTransfer $dataTransfer
     *
     * @return string
     */
    public function getNextUrl(StepInterface $currentStep, AbstractTransfer $dataTransfer);

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $currentStep
     *
     * @return string
     */
    public function getPreviousUrl(StepInterface $currentStep);

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Step\StepInterface $currentStep
     *
     * @return string
     */
    public function getEscapeUrl(StepInterface $currentStep);

}
