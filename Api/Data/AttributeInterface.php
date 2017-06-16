<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api\Data;

interface AttributeInterface
{
    const VIAEBAY_ATTRIBUTE_ID = 'viaebay_attribute_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const TYPE = 'type';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return AttributeInterface
     */
    public function setId($id);

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Set ID
     *
     * @param int $id
     * @return AttributeInterface
     */
    public function setAttributeId($id);

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayAttributeId();

    /**
     * Set ID
     *
     * @param int $id
     * @return AttributeInterface
     */
    public function setVIAeBayAttributeId($id);

    /**
     * Get Type
     * @return string|null
     */
    public function getType();

    /**
     * Set Type
     *
     * @param string|null $type
     * @return AttributeInterface
     */
    public function setType($type);
}