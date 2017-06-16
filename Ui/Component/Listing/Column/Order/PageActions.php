<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Ui\Component\Listing\Column\Order;

use Magento\Ui\Component\Listing\Columns\Column;

class PageActions extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $magento_order_id = null;
                if (isset($item["magento_order_id"])) {
                    $magento_order_id = $item["magento_order_id"];
                }
                if ($magento_order_id) {
                    $item[$name]["view"] = [
                        "href" => $this->getContext()->getUrl("sales/order/view", ["order_id" => $magento_order_id]),
                        "label" => __("View Order")
                    ];
                } else {
                    $viaOrderId = $item['viaebay_order_id'];

                    $item[$name]["import"] = [
                        "href" => $this->getContext()->getUrl("viaebay_connector/order/singleImport", ["viaebay_order_id" => $viaOrderId]),
                        "label" => __("Import Order")
                    ];
                }
            }
        }

        return $dataSource;
    }
}
