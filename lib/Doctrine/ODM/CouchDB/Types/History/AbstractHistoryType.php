<?php

namespace Doctrine\ODM\CouchDb\Types\History;

use Doctrine\ODM\CouchDB\DocumentManager;
use Doctrine\ODM\CouchDB\Types\Type;

abstract class AbstractHistoryType
{
    /**
     * @param DocumentManager $dm
     * @param array $values
     *
     * @return array
     * @throws \Exception
     */
    public static function filterViewDate(DocumentManager $dm, $values)
    {
        /*$filterCollection = $dm->getFilterCollection();
        if ($filterCollection->has('validFrom') && $filterCollection->isEnabled('validFrom')) {
            try {
                $viewDate = $filterCollection->getFilter('validFrom')->getParameter('viewDate');
            } catch (\InvalidArgumentException $e) {
                $viewDate = new \DateTime();
            }
        } else {
            throw new \Exception('Can\'t hydrate history value ValidFromFilter is disabled or not present');
        }*/
        $dateType = Type::getType('date');
        foreach ($values as $value) {
            /**
             * @var \DateTime $viewDate
             * @var \DateTime $compareValidFromDate
             * @var \DateTime $compareValidUntilDate
             */
            $compareValidFromDate = $dateType->convertToPHPValue($value['validFrom']);
            if ($viewDate < $compareValidFromDate) {
                continue;
            } else {
                if (isset($value['validUntil'])) {
                    $compareValidUntilDate = $dateType->convertToPHPValue($value['validUntil']);
                    if ($viewDate < $compareValidUntilDate) {
                        return $value['value'];
                    }
                } else {
                    return $value['value'];
                }
            }
        }
        return null;
    }
}
