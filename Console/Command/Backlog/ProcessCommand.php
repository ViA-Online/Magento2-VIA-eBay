<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Console\Command\Backlog;


use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VIAeBay\Connector\Service\Category;
use VIAeBay\Connector\Service\Product;

class ProcessCommand extends Command
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var Category
     */
    protected $_categoryService;
    /**
     * @var Product
     */
    protected $_productService;

    public function __construct(ObjectManagerInterface $objectManager, Category $categoryService, Product $productService, $name = null)
    {
        $this->_objectManager = $objectManager;
        $this->_categoryService = $categoryService;
        $this->_productService = $productService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('viaebay:backlog:process')->setDescription('Process backlog');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');
        $output->writeln('Sync Categories');
        $this->_categoryService->sync();
        $output->writeln('Export Backlog Products');
        $this->_productService->exportProducts();
    }
}