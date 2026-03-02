<?php

/**
 * @author Mygento Team
 * @copyright 2026 Mygento (https://www.mygento.ru)
 * @package Mygento_IndexNow
 */

declare(strict_types=1);

namespace Mygento\IndexNow\Model\Source;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Data\OptionSourceInterface;

class Attributes implements OptionSourceInterface
{
    /** @var array */
    protected $filterTypesNotEqual = [
        'hidden',
        'date',
        'image',
    ];

    protected ?array $options = null;
    protected bool $flatOnly = true;

    public function __construct(
        private Type $entityType,
        private CollectionFactory $attrColFactory,
    ) {
        $this->entityType->loadByCode(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
        );
    }

    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $collection = $this->attrColFactory->create();

        $keyEntityType = Set::KEY_ENTITY_TYPE_ID;
        $collection->addFieldToFilter($keyEntityType, $this->entityType->getId());

        //Filter Type is NOT In Array
        if (!empty($this->filterTypesNotEqual)) {
            $filter = ['nin' => $this->filterTypesNotEqual];
            $collection->addFieldToFilter('main_table.frontend_input', $filter);
        }

        //Filter Type IS In Array
        if (!empty($this->filterTypesEqual)) {
            $filter = ['in' => $this->filterTypesEqual];
            $collection->addFieldToFilter('main_table.frontend_input', $filter);
        }

        if ($this->flatOnly) {
            $collection->addFieldToFilter('used_in_product_listing', 1);
        }

        $collection->setOrder('frontend_label', 'ASC');
        $collection = $this->additionalFilter($collection);

        $attrAll = $collection->load()->getItems();

        $this->options = [];

        // Loop over all attributes
        foreach ($attrAll as $attr) {
            $label = $attr->getStoreLabel() ?? $attr->getFrontendLabel();
            if ('' != $label) {
                $this->options[] = ['label' => $label, 'value' => $attr->getAttributeCode()];
            }
        }

        return $this->options;
    }

    /**
     * Additional Filter
     *
     * @param Collection $collection
     * @return Collection $collection
     */
    protected function additionalFilter(Collection $collection): Collection
    {
        return $collection;
    }
}
