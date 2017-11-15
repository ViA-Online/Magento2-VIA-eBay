VIA-Connect_Connector
======================

VIA-Connect Connector Magento2 extension

Module integrates VIA-Connect with Magento 2

Install
=======

1. Open your command line terminal and go to your Magento 2 root installation directory.

2. Run the following commands to install the module:

    ```bash
        composer require viaebay/magento2-connector
        php bin/magento module:enable VIAeBay_Connector
        php bin/magento setup:upgrade
        php bin/magento setup:di:compile
    ```
