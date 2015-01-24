#!/usr/bin/env php
<?php

require_once 'doctrine-bootstrap.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

class PatchAuthorComments extends Command
{

    protected function configure()
    {
        $this
                ->setName('patch-comments')
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

        $ep->setPreNotes($notes);
        $this->em->persist($ep);
        $this->em->persist($oldAuthor);

        $this->em->commit();
        return TRUE;
    }

    private function tryFindSplit($threshold, $autorWithComment, $leftHull, $rightHull)
    {
        $commentParts = explode($leftHull, $autorWithComment);
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

class CreateTranslations extends Command
{

    protected function configure()
    {
        $this
                ->setName('create-translation')
                ->setDescription('Create a Gettext POT file')
                ->addArgument("language", Symfony\Component\Console\Input\InputArgument::REQUIRED, "Language code")
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

function addventureCtl()
{
    $application = new Application();
    $application->add(new PatchAuthorComments());
    $application->add(new CreateTranslations());
    $application->run();
}

addventureCtl();
