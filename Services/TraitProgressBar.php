<?php


namespace VanWittlaerTaxdooConnector\Services;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

trait TraitProgressBar
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @param int $limit
     * @param string $message
     * @return ProgressBar|null
     */
    private function createProgressBar(int $limit, string $message): ?ProgressBar
    {
        if ($this->symfonyStyle === null) {
            return null;
        }

        $progressBar = $this->symfonyStyle->createProgressBar($limit);
        $progressBar->setFormat('%message%: %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->setMessage($message);

        return $progressBar;
    }

    /**
     * @param ProgressBar|null $progress
     * @param int $step
     */
    private function progressAdvance(?ProgressBar $progress, int $step = 1)
    {
        $progress === null ?: $progress->advance($step);
    }

    /**
     * @param ProgressBar|null $progress
     */
    private function progressFinish(?ProgressBar $progress)
    {
        if ($progress === null) {

            return;
        }
        $progress->finish();
        $this->symfonyStyle->writeln(PHP_EOL);
    }
}