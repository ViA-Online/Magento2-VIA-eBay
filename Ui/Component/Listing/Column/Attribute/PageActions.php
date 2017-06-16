<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */
namespace VIAeBay\Connector\Ui\Component\Listing\Column\Attribute;

use Magento\Ui\Component\Listing\Columns\Column;

class PageActions extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if (isset($item["viaebay_attribute_id"])) {
                    $id = $item["viaebay_attribute_id"];
                }
                $item[$name]["edit"] = [
                    "href" => $this->getContext()->getUrl(
                        "viaebay_connector/attribute/edit",
                        ["viaebay_attribute_id" => $id]
                    ),
                    "label" => __("Edit")
                ];
                $item[$name]['delete'] = [
                    'href' => $this->getContext()->getUrl(
                        "viaebay_connector/attribute/delete",
                        ["viaebay_attribute_id" => $id]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete "${ $.$data.type }"'),
                        'message' => __('Are you sure you wan\'t to delete a "${ $.$data.type }" record?')
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
