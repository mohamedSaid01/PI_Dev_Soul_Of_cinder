<?php
namespace App\Command;

use App\Service\EventService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArchiveExpiredEventsCommand extends Command
{
    protected static $defaultName = 'app:archive-expired-events';
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        parent::__construct();
        $this->eventService = $eventService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Archive automatiquement les événements expirés.')
            ->setHelp('Cette commande archive tous les événements dont la date de fin est dépassée.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Archivage des événements expirés');

        $this->eventService->archiveExpiredEvents();

        $io->success('Archivage terminé avec succès.');

        return Command::SUCCESS;
    }
}
