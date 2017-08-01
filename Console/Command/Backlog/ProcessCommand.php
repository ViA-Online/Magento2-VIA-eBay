<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Console\Command\Backlog;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VIAeBay\Connector\Service\Category;
use VIAeBay\Connector\Service\Product;

class ProcessCommand extends Command
{
    /**
     * @var Category
     */
    protected $_categoryService;
    /**
     * @var Product
     */
    protected $_productService;

    /**
     * ProcessCommand constructor.
     * @param Category $categoryService
     * @param Product $productService
     * @param null $name
     */
    public function __construct(Category $categoryService, Product $productService, $name = null)
    {
        $this->_categoryService = $categoryService;
        $this->_productService = $productService;
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('viaebay:backlog:process')->setDescription('Process backlog');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Sync categories');
        $this->_categoryService->sync();
        $output->writeln('Export backlog products');
        $this->_productService->exportProducts();
        $output->writeln('Backlog products exported');
    }
}