<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Console\Command\Order;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VIAeBay\Connector\Service\Order;

class ImportCommand extends Command
{
    /**
     * Name of input option
     */
    const INPUT_KEY_ID = 'id';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Order
     */
    private $_orderService;

    /**
     * ImportCommand constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Order $categoryService
     * @param null $name
     */
    function __construct(ObjectManagerInterface $objectManager, Order $categoryService, $name = null)
    {
        $this->_objectManager = $objectManager;
        $this->_orderService = $categoryService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('viaebay:import:order');
        $this->setDescription('Import new orders');
        $this->addArgument(
            self::INPUT_KEY_ID,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Import given ids only.',
            []
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');

        $arguments = $input->getArguments();
        $orderIds = $arguments[self::INPUT_KEY_ID];

        $output->writeln('Import Orders');
        $this->_orderService->import(empty($orderIds) ? null : $orderIds);
    }
}