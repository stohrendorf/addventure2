#!/usr/bin/env php
<?php
require_once 'doctrine-bootstrap.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

// -----------------------------------------------------------------------------

class PatchAuthorNames extends Command
{

    protected function configure()
    {
        $this
                ->setName('patch-names')
                ->setDescription('Try to find comments in author names and make them real comments')
                ->addOption(
                        'min-length', null, InputOption::VALUE_OPTIONAL, 'Minimum required length of author name after splitting', 5
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = initDoctrineConnection();

        $threshold = $input->getOption('min-length');

        $qb = $this->em->getRepository('addventure\Episode')->createQueryBuilder('ep')
                ->where('ep.preNotes IS NULL')
                ->andWhere('ep.author IS NOT NULL');

        $output->writeln('<info>Fetching episodes...</info>');

        $flushCounter = 0;
        $updates = 0;
        foreach($qb->getQuery()->iterate() as $row) {
            $ep = $row[0];
            if($ep->getAuthor()->getUser()->getRole()->get() != \addventure\UserRole::Anonymous)
                continue;
            
            $authorName = $ep->getAuthor()->getName();
            $output->writeln('Checking: ``' . $authorName . "''");

            $updated = $this->trySplit($output, $ep, $threshold, $authorName, '(', ')')
                    or $this->trySplit($output, $ep, $threshold, $authorName, '[', ']')
                    or $this->trySplit($output, $ep, $threshold, $authorName, '--', null)
                    or $this->trySplit($output, $ep, $threshold, $authorName, ' - ', null)
                    or $this->trySplit($output, $ep, $threshold, $authorName, ',', null)
                    or $this->trySplit($output, $ep, $threshold, $authorName, ';', null)
                    or $this->trySplit($output, $ep, $threshold, $authorName, '/', null)
            ; // <<== closing semicolon

            if($updated) {
                ++$updates;
            }

            ++$flushCounter;
            if($flushCounter % 1000 == 0) {
                $output->writeln("<info>$flushCounter episodes (flushing)...</info>");
                $this->em->flush();
                $this->em->clear();
            }
        }
        $this->em->flush();
        $this->em->clear();
        $output->writeln("<info>$updates episodes were updated.</info>");
    }

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    private function trySplit(OutputInterface $output, addventure\Episode &$ep, $threshold, $autorWithComment, $leftHull, $rightHull)
    {
        $res = $this->tryFindSplit($threshold, $autorWithComment, $leftHull, $rightHull);
        if(!$res) {
            return FALSE;
        }
        $oldAuthor = $ep->getAuthor();
        $newAuthor = $res[0];
        $notes = $res[1];

        $output->writeln('<info>Patch: ``' . $autorWithComment . "'' => ``" . $newAuthor->getName() . "'', comment ``" . $notes . "''</info>");

        $this->em->beginTransaction();

        $ep->setAuthor($newAuthor);

        $newAuthor->getEpisodes()->add($ep);

        $ep->setPostNotes($notes);
        $this->em->persist($ep);
        $this->em->persist($oldAuthor);

        $this->em->commit();
        return TRUE;
    }

    private function tryFindSplit($threshold, $authorWithComment, $leftHull, $rightHull)
    {
        $commentParts = explode($leftHull, $authorWithComment);
        if(count($commentParts) <= 1) {
            return FALSE;
        }

        $nameParts = array();
        while(!empty($commentParts)) {
            $nameParts[] = array_shift($commentParts);
            $realAuthorName = trim(implode($leftHull, $nameParts));
            if(mb_strlen($realAuthorName) < $threshold) {
                return FALSE;
            }
            $author = $this->em->getRepository('addventure\AuthorName')->findOneBy(array('name' => $realAuthorName));
            if($author === null) {
                continue;
            }

            $comment = trim(implode($leftHull, $commentParts));
            if($rightHull !== null && mb_substr($comment, mb_strlen($comment) - 1) === $rightHull) {
                $comment = mb_substr($comment, 0, mb_strlen($comment) - 1);
            }
            if(empty($comment)) {
                return FALSE;
            }
            return array($author, $comment);
        }
        return FALSE;
    }

}

// -----------------------------------------------------------------------------

class CleanupUsers extends Command
{

    protected function configure()
    {
        $this
                ->setName('cleanup-users')
                ->setDescription('Remove unused author names, unused anonymous users, and '
                        . 'expired "awaiting approval" users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = initDoctrineConnection();
        $q = $em->createQuery('DELETE addventure\AuthorName n WHERE'
                . ' NOT EXISTS (SELECT 1 FROM addventure\Episode e WHERE e.author=n)'
                . ' AND NOT EXISTS (SELECT 1 FROM addventure\Comment c WHERE c.authorName=n)');
        $deleted = $q->execute();
        $em->flush();
        $em->clear();
        $output->writeln("Deleted $deleted unused author names");

        $q = $em->createQuery('DELETE addventure\User u WHERE'
                . ' u.role=0'
                . ' AND NOT EXISTS (SELECT 1 FROM addventure\AuthorName n WHERE n.user=u)');
        $deleted = $q->execute();
        $em->flush();
        $em->clear();
        $output->writeln("Deleted $deleted unused anonymous users");

        $ts = new \DateTime();
        $ts->sub(new \DateInterval('PT' . getAddventureConfigValue('maxAwaitingApprovalHours') . 'H'));
        $q = $em->createQuery('DELETE addventure\User u WHERE'
                        . ' u.role=1'
                        . ' AND u.registeredSince < :when')
                ->setParameter('when', $ts);
        $deleted = $q->execute();
        $em->flush();
        $em->clear();
        $output->writeln("Deleted $deleted expired users");
    }

}

// -----------------------------------------------------------------------------

class CreateTranslations extends Command
{

    protected function configure()
    {
        $this
                ->setName('create-translation')
                ->setDescription('Create a Gettext POT file')
                ->addArgument("language-code", Symfony\Component\Console\Input\InputArgument::REQUIRED, "Language code")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Running tsmarty2c...');

        $outputName = realpath('./application/language') . '/' . $input->getArgument('language') . '.pot';
        $output->writeln("<info>POT file: $outputName</info>");
        system('php ./vendor/smarty-gettext/smarty-gettext/tsmarty2c.php -o ' . $outputName . ' ' . realpath('./templates/'));
        system('xgettext -j -o ' . $outputName . ' ' . realpath('./application') . '/*/*.php');
    }

}

// -----------------------------------------------------------------------------

class FindOrphans extends Command
{

    protected function configure()
    {
        $this
                ->setName('find-orphans')
                ->setDescription('Find episodes that have a parent that does not have a link to that episode')
                ->setHelp('Orphans are episodes, where the parent episode has been deleted (due to spam reasons or rule violations),' . "\n"
                        . 'and later someone created a new episode from the grand-parent\'s episode, replacing the deleted parent.' . "\n"
                        . 'In the old Addventure, this leads to the problem that the replaced parent episode now contains' . "\n"
                        . 'completely unrelated child links which don\'t link to the orphan anymore.  This command finds' . "\n"
                        . 'these episodes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Searching for orphans...');

        $em = initDoctrineConnection();
        $query = $em->createQuery('SELECT e FROM addventure\Episode e WHERE e.parent IS NOT NULL AND NOT EXISTS '
                . '(SELECT 1 FROM addventure\Link l WHERE l.fromEp=e.parent AND l.toEp=e.id)');
        $n = 0;
        foreach($query->iterate() as $row) {
            $ep = $row[0];
            $parent = $ep->getParent()->getId();
            $id = $ep->getId();
            $output->writeln("#$id is orphaned through parent #$parent");
            ++$n;
        }
        $output->writeln("<info>$n orphans found</info>");
    }

}

// -----------------------------------------------------------------------------

class FindRoots extends Command
{

    protected function configure()
    {
        $this
                ->setName('find-roots')
                ->setDescription('Find episodes that do not have a parent')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Searching for roots...');

        $em = initDoctrineConnection();
        $query = $em->createQuery('SELECT e FROM addventure\Episode e WHERE e.parent IS NULL');
        $n = 0;
        foreach($query->iterate() as $row) {
            $ep = $row[0];
            $id = $ep->getId();
            $output->writeln("#$id");
            ++$n;
        }
        $output->writeln("<info>$n roots found</info>");
    }

}

// -----------------------------------------------------------------------------

function addventureCtl()
{
    chdir(dirname(__FILE__));
    $application = new Application();
    $application->add(new PatchAuthorNames());
    $application->add(new CleanupUsers());
    $application->add(new CreateTranslations());
    $application->add(new FindOrphans());
    $application->add(new FindRoots());
    $application->run();
}

addventureCtl();
