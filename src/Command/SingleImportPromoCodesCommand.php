<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Order\PromoCode\Import\SingleImportPromoCodeFacade;
use App\Model\Order\PromoCode\PromoCode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SingleImportPromoCodesCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'sconto:import:promocodes';

    /**
     * @var \App\Model\Order\PromoCode\Import\SingleImportPromoCodeFacade
     */
    private SingleImportPromoCodeFacade $singleImportPromoCodeFacade;

    /**
     * @param \App\Model\Order\PromoCode\Import\SingleImportPromoCodeFacade $singleImportPromoCodeFacade
     */
    public function __construct(SingleImportPromoCodeFacade $singleImportPromoCodeFacade)
    {
        parent::__construct();
        $this->singleImportPromoCodeFacade = $singleImportPromoCodeFacade;
    }

    protected function configure()
    {
        $this->setDescription('Single import promo codes, ec: php bin/console sconto:import:promocodes -f /web/public/importFiles/Sconto_CZ_Export_Welcome2_UTF.csv');
        $this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'FilePath to promo code CSV file. (exam: /web/public/importFiles/Sconto_CZ_Export_Welcome2_UTF.csv)');
        $this->addOption('price_limit', 'l', InputOption::VALUE_OPTIONAL, 'Price Limit (number)', 1);
        $this->addOption('discount_type', 't', InputOption::VALUE_OPTIONAL, 'Discount type (number PromoCode::DISCOUNT_TYPE_PERCENT = 1, PromoCode::DISCOUNT_TYPE_NOMINAL = 2)', PromoCode::DISCOUNT_TYPE_PERCENT);
        $this->addOption('discount', 'd', InputOption::VALUE_OPTIONAL, 'discount (number)', 30);
        $this->addOption('moeve_code', 'c', InputOption::VALUE_OPTIONAL, 'moeve code (string - 2 chars) for domain CZ domain - SC, for SK domain - SS, if you left default value than moeve code will setup by domains.', 'XX');
        $this->addOption('on_sale', null, InputOption::VALUE_OPTIONAL, 'flag on sale (yes/no)', 'no');
        $this->addOption('in_action', null, InputOption::VALUE_OPTIONAL, 'flag in action (yes/no)', 'no');
        $this->addOption('sconto_price', null, InputOption::VALUE_OPTIONAL, 'flag sconto price (yes/no)', 'yes');
        $this->addOption('without_low_price', null, InputOption::VALUE_OPTIONAL, 'flag without low price (yes/no)', 'no');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->singleImportPromoCodeFacade->runTransfer($input->getOptions());

        return 0;
    }
}
