<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WeBee\gCMS\Helpers\FileSystem\DefaultFileSystem;
use WeBee\gCMS\Helpers\Git\Git;

class UpdateCommand extends AbstractCommand
{
    protected static $defaultName = 'update';

    protected function additionalConfiguration()
    {
        $this->setDescription('Updates repository source content to the newest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ('git' !== $this->config['input']['type']) {
            throw new DomainException('Can not use update command on non Git input path');
        }

        $fs = new DefaultFileSystem();
        $repositoryPath = $this->config['input']['path'];
        $vcsRemote = $this->config['input']['vcsSource'];
        $branch = $this->config['input']['branch'];

        if (false === file_exists($repositoryPath)) {
            $fs->mkdir($repositoryPath);
        }

        $git = new Git($repositoryPath);

        if (!$git->isItGit()) {
            $git->clone($vcsRemote);
        }

        $git->checkout($branch, $this->config['input']['forceCheckout']);
        $git->pull();

        return Command::SUCCESS;
    }
}
