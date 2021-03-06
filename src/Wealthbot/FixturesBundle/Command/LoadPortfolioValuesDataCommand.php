<?php

namespace Wealthbot\FixturesBundle\Command;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Wealthbot\ClientBundle\Entity\ClientPortfolioValue;

class LoadPortfolioValuesDataCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('wealthbot:fixtures:portfolio_values')
            ->setDescription('Load client portfolio values data.')
            ->addArgument('accountNumber', InputArgument::OPTIONAL, '')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $accountNumber = $input->getArgument('accountNumber');
        $systemAccount = $em->getRepository('WealthbotClientBundle:SystemAccount')->findOneBy(array('account_number' => $accountNumber));
        if ($systemAccount == null) {
            throw new EntityNotFoundException("System client account by account number [$accountNumber] not found.");
        }

        $portfolio = $em->getRepository('WealthbotClientBundle:ClientPortfolio')->findOneByClient($systemAccount->getClient());
        if ($portfolio == null) {
            $clientId = $systemAccount->getClient()->getId();
            throw new EntityNotFoundException("Client portfolio by client id [$clientId] not found.");
        }

        $i = 0;
        mt_srand(0);
        foreach (array(2013, 2014) as $yr) {
            $mo = ($yr === 2013) ? 13 : (int) date('m');
            for ($m = 1; $m < $mo; $m++) {
                $pdate = \DateTime::createFromFormat('m-d-Y', "{$m}-01-{$yr}");

                $securities = mt_rand(0, 1000000);
                $money_market = mt_rand(0, 1000000);
                $accounts = mt_rand(0, 1000000);
                $total = $securities + $money_market + $accounts;

                $sql = "SELECT * FROM client_portfolio_values cpv WHERE DATE(cpv.date) = DATE(:createdAt) AND cpv.client_portfolio_id = :clientPortfolioId LIMIT 1";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue('createdAt', $pdate->format('Y-m-d'));
                $stmt->bindValue('clientPortfolioId', $portfolio->getId());
                $stmt->execute();

                if ((bool) $stmt->fetchColumn() == false) {
                    $portfolioValue = new ClientPortfolioValue();
                    $portfolioValue->setClientPortfolio($portfolio);
                    $portfolioValue->setTotalCashInMoneyMarket($money_market);
                    $portfolioValue->setTotalInSecurities($securities);
                    $portfolioValue->setTotalCashInAccounts($accounts);
                    $portfolioValue->setTotalValue($total);
                    $portfolioValue->setSasCash(0);
                    $portfolioValue->setCashBuffer(0);
                    $portfolioValue->setBillingCash(0);
                    $portfolioValue->setDate($pdate);
                    $portfolioValue->setModelDeviation(4);
                    $portfolioValue->setRequiredCash(mt_rand(1000, 300000));
                    $portfolioValue->setInvestableCash(mt_rand(1000, 30000));
                    $em->persist($portfolioValue);
                    $i++;
                }
            }
        }

        $em->flush();
        $output->writeln("Client portfolio value [{$i}] has been loaded.");
        $output->writeln("Success!");
    }
}
