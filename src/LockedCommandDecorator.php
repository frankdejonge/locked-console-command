<?php

namespace FrankDeJonge\LockedConsoleCommand;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

class LockedCommandDecorator extends Command
{
    /**
     * @var Command
     */
    private $decoratedCommand;

    /**
     * @var string|LockHandler
     */
    private $lockName;

    /**
     * @var string
     */
    private $lockPath;

    /**
     * Constructor.
     *
     * @param Command            $command
     * @param string|LockHandler $lockName
     * @param string             $lockPath
     */
    public function __construct(Command $command, $lockName = null, $lockPath = null)
    {
        $this->decoratedCommand = $command;
        $this->setLockName($lockName);
        $this->lockPath = $lockPath;
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreValidationErrors()
    {
        $this->decoratedCommand->ignoreValidationErrors();
    }

    /**
     * {@inheritdoc}
     */
    public function setApplication(Application $application = null)
    {
        $this->decoratedCommand->setApplication($application);
    }

    /**
     * {@inheritdoc}
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->decoratedCommand->setHelperSet($helperSet);
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperSet()
    {
        return $this->decoratedCommand->getHelperSet();
    }

    /**
     * {@inheritdoc}
     */
    public function getApplication()
    {
        return $this->decoratedCommand->getApplication();
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->decoratedCommand->isEnabled();
    }

    /**
     * @param string|LockHandler|null $lockName
     */
    public function setLockName($lockName)
    {
        $this->lockName = $lockName;
    }

    /**
     * Runs the command.
     *
     * Before the decorated command is run, a lock is requested.
     * When failed to acquire the lock, the command exits.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int The command exit code
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $lock = $this->getLockHandler($input);

        if (! $lock->lock()) {
            $this->writeLockedMessage($input, $output);

            return 0;
        }

        try {
            $result = $this->decoratedCommand->run($input, $output);
            $lock->release();
        } catch (Exception $e) {
            $lock->release();
            throw $e;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->decoratedCommand->setCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeApplicationDefinition($mergeArgs = true)
    {
        $this->decoratedCommand->mergeApplicationDefinition($mergeArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition($definition)
    {
        $this->decoratedCommand->setDefinition($definition);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return $this->decoratedCommand->getDefinition();
    }

    /**
     * {@inheritdoc}
     */
    public function getNativeDefinition()
    {
        return $this->decoratedCommand->getNativeDefinition();
    }

    /**
     * {@inheritdoc}
     */
    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        $this->decoratedCommand->addArgument($name, $mode, $description, $default);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->decoratedCommand->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->decoratedCommand->setName($name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessTitle($title)
    {
        $this->decoratedCommand->setProcessTitle($title);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->decoratedCommand->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->decoratedCommand->setDescription($description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->decoratedCommand->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function setHelp($help)
    {
        $this->decoratedCommand->setHelp($help);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelp()
    {
        return $this->decoratedCommand->getHelp();
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessedHelp()
    {
        return $this->decoratedCommand->getProcessedHelp();
    }

    /**
     * {@inheritdoc}
     */
    public function setAliases($aliases)
    {
        $this->decoratedCommand->setAliases($aliases);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return $this->decoratedCommand->getAliases();
    }

    /**
     * {@inheritdoc}
     */
    public function getSynopsis()
    {
        return $this->decoratedCommand->getSynopsis();
    }

    /**
     * {@inheritdoc}
     */
    public function getHelper($name)
    {
        return $this->decoratedCommand->getHelper($name);
    }

    /**
     * Get the locking helper.
     *
     * @param  InputInterface $input
     * @return LockHandler
     */
    private function getLockHandler(InputInterface $input)
    {
        if ($this->lockName instanceof LockHandler) {
            return $this->lockName;
        }

        $lockName = $this->getLockName($input);
        $lockPath = $this->getLockPath($input);

        return new LockHandler($lockName, $lockPath);
    }

    /**
     * Get the name for the lock.
     *
     * @param  InputInterface $input
     * @return string
     */
    public function getLockName(InputInterface $input)
    {
        if (is_string($this->lockName)) {
            return $this->lockName;
        }

        if ($this->lockName instanceof LockHandler) {
            return 'UNKNOWN';
        }

        if ($this->decoratedCommand instanceof SpecifiesLockName) {
            return $this->decoratedCommand->getLockName($input);
        }

        return $this->decoratedCommand->getName();
    }

    /**
     * Get the lock path.
     *
     * @param  InputInterface $input
     * @return null|string
     */
    public function getLockPath(InputInterface $input)
    {
        if ($this->lockName instanceof LockHandler) {
            return 'UNKNOWN';
        }

        if ($this->lockPath !== null) {
            return $this->lockPath;
        }

        if ($this->decoratedCommand instanceof SpecifiesLockPath) {
            return $this->decoratedCommand->getLockPath($input);
        }

        return null;
    }

    /**
     * Write the "is locked" message.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function writeLockedMessage(InputInterface $input, OutputInterface $output)
    {
        $commandName = $this->decoratedCommand->getName();
        $lockName = $this->getLockName($input);
        $lockPath = $this->getLockPath($input);
        $message = sprintf(
            '<info>Command "%s" is already running, locked with "%s" at path "%s"</info>',
            $commandName,
            $lockName,
            $lockPath
        );

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln($message);
        }
    }
}
