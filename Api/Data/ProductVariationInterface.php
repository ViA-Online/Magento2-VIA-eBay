<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api\Data;

interface ProductVariationInterface 
{
    const VIAEBAY_PRODUCT_VARIATION_ID = 'viaebay_product_variation_id';
    const VIAEBAY_PRODUCT_ID = 'viaebay_product_id';
    const VIAEBAY_ID = 'viaebay_id';
    const PRODUCT_ID = 'product_id';

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
     * @return ProductVariationInterface
     */
    public function setId($id);

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductVariationInterface
     */
    public function setProductId($id);


    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayProductVariationId();

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductVariationInterface
     */
    public function setVIAeBayProductVariationId($id);


    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayProductId();

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductVariationInterface
     */
    public function setVIAeBayProductId($id);

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayId();

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductVariationInterface
     */
    public function setVIAeBayId($id);
}