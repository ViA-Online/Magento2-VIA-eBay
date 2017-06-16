<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */
namespace VIAeBay\Connector\Ui\Component\Listing\Column\Backlog\Product;

use Magento\Ui\Component\Listing\Columns\Column;

class PageActions extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                $productId = "X";
                if (isset($item["viaebay_backlog_product_id"])) {
                    $id = $item["viaebay_backlog_product_id"];
                }
                if (isset($item["product_id"])) {
                    $productId = $item["product_id"];
                }

                $item[$name]["view"] = [
                    "href" => $this->getContext()->getUrl(
                        "catalog/product/edit",
                        ["id" => $productId]
                    ),
                    "label" => __("View Product")
                ];
            }
        }

        return $dataSource;
    }
}
