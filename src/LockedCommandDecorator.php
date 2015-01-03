<?php

namespace FrankDeJonge\LockedConsoleCommand;

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
     * @param Command $command
     * @param string|LockHandler    $lockName
     * @param string    $lockPath
     */
    public function __construct(Command $command, $lockName = null, $lockPath = null)
    {
        $this->decoratedCommand = $command;
        $this->setLockName($lockName);
        $this->lockPath = $lockPath;
    }

    /**
     * Ignores validation errors.
     *
     * This is mainly useful for the help command.
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
     * @param string|LockHandler $lockName
     */
    public function setLockName($lockName)
    {
        $this->lockName = $lockName;
    }

    /**
     * Runs the command.
     *
     *
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
        } finally {
            $lock->release();
        }

        return $result;
    }

    /**
     * Sets the code to execute when running this command.
     *
     * If this method is used, it overrides the code defined
     * in the execute() method.
     *
     * @param callable $code A callable(InputInterface $input, OutputInterface $output)
     *
     * @return Command The current instance
     *
     * @throws \InvalidArgumentException
     *
     * @see execute()
     *
     * @api
     */
    public function setCode($code)
    {
        $this->decoratedCommand->setCode($code);

        return $this;
    }

    /**
     * Merges the application definition with the command definition.
     *
     * This method is not part of public API and should not be used directly.
     *
     * @param bool $mergeArgs Whether to merge or not the Application definition arguments to Command definition arguments
     */
    public function mergeApplicationDefinition($mergeArgs = true)
    {
        $this->decoratedCommand->mergeApplicationDefinition($mergeArgs);
    }

    /**
     * Sets an array of argument and option instances.
     *
     * @param array|InputDefinition $definition An array of argument and option instances or a definition instance
     *
     * @return Command The current instance
     *
     * @api
     */
    public function setDefinition($definition)
    {
        $this->decoratedCommand->setDefinition($definition);

        return $this;
    }

    /**
     * Gets the InputDefinition attached to this Command.
     *
     * @return InputDefinition An InputDefinition instance
     *
     * @api
     */
    public function getDefinition()
    {
        return $this->decoratedCommand->getDefinition();
    }

    /**
     * Gets the InputDefinition to be used to create XML and Text representations of this Command.
     *
     * Can be overridden to provide the original command representation when it would otherwise
     * be changed by merging with the application InputDefinition.
     *
     * This method is not part of public API and should not be used directly.
     *
     * @return InputDefinition An InputDefinition instance
     */
    public function getNativeDefinition()
    {
        return $this->decoratedCommand->getNativeDefinition();
    }

    /**
     * Adds an argument.
     *
     * @param string $name        The argument name
     * @param int    $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
     * @param string $description A description text
     * @param mixed  $default     The default value (for InputArgument::OPTIONAL mode only)
     *
     * @return Command The current instance
     *
     * @api
     */
    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        $this->decoratedCommand->addArgument($name, $mode, $description, $default);

        return $this;
    }

    /**
     * Adds an option.
     *
     * @param string $name        The option name
     * @param string $shortcut    The shortcut (can be null)
     * @param int    $mode        The option mode: One of the InputOption::VALUE_* constants
     * @param string $description A description text
     * @param mixed  $default     The default value (must be null for InputOption::VALUE_REQUIRED or InputOption::VALUE_NONE)
     *
     * @return Command The current instance
     *
     * @api
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->decoratedCommand->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    /**
     * Sets the name of the command.
     *
     * This method can set both the namespace and the name if
     * you separate them by a colon (:)
     *
     *     $command->setName('foo:bar');
     *
     * @param string $name The command name
     *
     * @return Command The current instance
     *
     * @throws \InvalidArgumentException When the name is invalid
     *
     * @api
     */
    public function setName($name)
    {
        $this->decoratedCommand->setName($name);

        return $this;
    }

    /**
     * Sets the process title of the command.
     *
     * This feature should be used only when creating a long process command,
     * like a daemon.
     *
     * PHP 5.5+ or the proctitle PECL library is required
     *
     * @param string $title The process title
     *
     * @return Command The current instance
     */
    public function setProcessTitle($title)
    {
        $this->decoratedCommand->setProcessTitle($title);

        return $this;
    }

    /**
     * Returns the command name.
     *
     * @return string The command name
     *
     * @api
     */
    public function getName()
    {
        return $this->decoratedCommand->getName();
    }

    /**
     * Sets the description for the command.
     *
     * @param string $description The description for the command
     *
     * @return Command The current instance
     *
     * @api
     */
    public function setDescription($description)
    {
        $this->decoratedCommand->setDescription($description);

        return $this;
    }

    /**
     * Returns the description for the command.
     *
     * @return string The description for the command
     *
     * @api
     */
    public function getDescription()
    {
        return $this->decoratedCommand->getDescription();
    }

    /**
     * Sets the help for the command.
     *
     * @param string $help The help for the command
     *
     * @return Command The current instance
     *
     * @api
     */
    public function setHelp($help)
    {
        $this->decoratedCommand->setHelp($help);

        return $this;
    }

    /**
     * Returns the help for the command.
     *
     * @return string The help for the command
     *
     * @api
     */
    public function getHelp()
    {
        return $this->decoratedCommand->getHelp();
    }

    /**
     * Returns the processed help for the command replacing the %command.name% and
     * %command.full_name% patterns with the real values dynamically.
     *
     * @return string The processed help for the command
     */
    public function getProcessedHelp()
    {
        return $this->decoratedCommand->getProcessedHelp();
    }

    /**
     * Sets the aliases for the command.
     *
     * @param string[] $aliases An array of aliases for the command
     *
     * @return Command The current instance
     *
     * @throws \InvalidArgumentException When an alias is invalid
     *
     * @api
     */
    public function setAliases($aliases)
    {
        $this->decoratedCommand->setAliases($aliases);

        return $this;
    }

    /**
     * Returns the aliases for the command.
     *
     * @return array An array of aliases for the command
     *
     * @api
     */
    public function getAliases()
    {
        return $this->decoratedCommand->getAliases();
    }

    /**
     * Returns the synopsis for the command.
     *
     * @return string The synopsis
     */
    public function getSynopsis()
    {
        return $this->decoratedCommand->getSynopsis();
    }

    /**
     * Gets a helper instance by name.
     *
     * @param string $name The helper name
     *
     * @return mixed The helper value
     *
     * @throws \InvalidArgumentException if the helper is not defined
     *
     * @api
     */
    public function getHelper($name)
    {
        return $this->decoratedCommand->getHelper($name);
    }

    /**
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
     * @param InputInterface $input
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

        $output->writeln($message);
    }
}