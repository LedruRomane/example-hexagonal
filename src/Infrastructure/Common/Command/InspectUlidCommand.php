<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Command;

use App\Infrastructure\Common\Uid\UlidUtils;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Dumper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Ulid;

/**
 * Replaces {@see \Symfony\Component\Uid\Command\InspectUlidCommand} until 6.2 is released.
 *
 * @see https://github.com/symfony/symfony/pull/45945/
 */
#[AsCommand(name: 'ulid:inspect', description: 'Inspect a ULID')]
class InspectUlidCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('ulid', InputArgument::REQUIRED, 'The ULID to inspect'),
            ])
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> displays information about a ULID.

    <info>php %command.full_name% 01EWAKBCMWQ2C94EXNN60ZBS0Q</info>
    <info>php %command.full_name% 1BVdfLn3ERmbjYBLCdaaLW</info>
    <info>php %command.full_name% 01771535-b29c-b898-923b-b5a981f5e417</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

        /** @var string $raw */
        $raw = $input->getArgument('ulid');

        try {
            $ulid = UlidUtils::fromHex($raw);
        } catch (\InvalidArgumentException) {
            try {
                $ulid = Ulid::fromString($raw);
            } catch (\InvalidArgumentException $e) {
                $io->error($e->getMessage());

                return 1;
            }
        }

        $dumper = new Dumper($output);

        $io->table(['Label', 'Value'], [
            ['toBase32 (canonical)', (string) $ulid],
            ['toBase58', $ulid->toBase58()],
            ['toRfc4122', $ulid->toRfc4122()],
            ['toBinary', $dumper($ulid->toBinary())],
            ['toHex', UlidUtils::toHex($ulid)],
            new TableSeparator(),
            ['Time', $ulid->getDateTime()->format('Y-m-d H:i:s.v \U\T\C')],
        ]);

        return 0;
    }
}
