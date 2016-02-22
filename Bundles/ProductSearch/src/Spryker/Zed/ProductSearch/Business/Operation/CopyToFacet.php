<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductSearch\Business\Operation;

class CopyToFacet implements OperationInterface
{

    const FACET_NAME_FIELD = 'facet-name';
    const FACET_VALUE_FIELD = 'facet-value';

    /**
     * @param array $sourceDocument
     * @param array $targetDocument
     * @param mixed $sourceField
     * @param string $targetField
     *
     * @return array
     */
    public function enrichDocument(array $sourceDocument, array $targetDocument, $sourceField, $targetField)
    {
        if (isset($sourceDocument[$sourceField]) && !empty($sourceDocument[$sourceField])) {
            $facet = [
                self::FACET_NAME_FIELD => $sourceField,
                self::FACET_VALUE_FIELD => $sourceDocument[$sourceField],
            ];
            $targetDocument[$targetField][] = $facet;
        }

        return $targetDocument;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'CopyToFacet';
    }

}
