<?php
/**
 * Copyright Puderbach & Wienczny GbR (c) 2017.
 */

namespace VIAeBay\Connector\Console\Command\Order;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VIAeBay\Connector\Helper\State;
use VIAeBay\Connector\Service\Order;

class ImportCommand extends Command
{
    /**
     * Name of input option
     */
    const INPUT_KEY_ID = 'id';

    /**
     * @var State
     */
    private $state;

    /**
     * @var Order
     */
    private $orderService;

    /**
     * ImportCommand constructor.
     * @param State $state of Magento
     * @param Order $categoryService
     * @param null $name
     */
    function __construct(State $state, Order $categoryService, $name = null)
    {
        $this->state = $state;
        $this->orderService = $categoryService;
        parent::__construct($name);
    }

    /**
     *
     */
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->initializeAreaCode();

        $arguments = $input->getArguments();
        $orderIds = $arguments[self::INPUT_KEY_ID];

        $output->writeln('Import orders');
        $this->orderService->import(empty($orderIds) ? null : $orderIds);
        $output->writeln('Orders imported');
    }
}