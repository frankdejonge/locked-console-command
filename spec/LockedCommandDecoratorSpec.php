<?php

namespace spec\FrankDeJonge\LockedConsoleCommand;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

include __DIR__.'/../stubs/stubs.php';

class LockedCommandDecoratorSpec extends ObjectBehavior
{
    public function it_is_initializable(Command $command)
    {
        $this->beConstructedWith($command);
        $this->shouldHaveType('FrankDeJonge\LockedConsoleCommand\LockedCommandDecorator');
        $this->shouldHaveType('Symfony\Component\Console\Command\Command');
    }

    public function it_should_delegate_decorated_functions(Command $command, Application $application, InputDefinition $inputDefinition, HelperSet $helperSet)
    {
        $this->beConstructedWith($command);
        $command->setName('name')->shouldBeCalled();
        $this->setName('name');
        $command->setHelp('help')->shouldBeCalled();
        $this->setHelp('help');
        $command->setProcessTitle('process:title')->shouldBeCalled();
        $this->setProcessTitle('process:title');
        $command->setAliases(['alias'])->shouldBeCalled();
        $this->setAliases(['alias']);
        $command->setCode('code:input')->shouldBeCalled();
        $this->setCode('code:input');
        $command->setDescription('description')->shouldBeCalled();
        $this->setDescription('description');
        $command->addArgument('name', 1, 'desc', 'default')->shouldBeCalled();
        $this->addArgument('name', 1, 'desc', 'default');
        $command->addOption('name', 'shortcut', 1, 'desc', 'default')->shouldBeCalled();
        $this->addOption('name', 'shortcut', 1, 'desc', 'default');
        $command->getAliases()->willReturn(['alias']);
        $this->getAliases()->shouldBe(['alias']);
        $command->getApplication()->willReturn($application);
        $this->getApplication()->shouldBe($application);
        $command->getDefinition()->willReturn($inputDefinition);
        $this->getDefinition()->shouldBe($inputDefinition);
        $command->getDescription()->willReturn('description');
        $this->getDescription()->shouldBe('description');
        $command->getHelp()->willReturn('help');
        $this->getHelp()->shouldBe('help');
        $command->getHelper('name')->willReturn('name');
        $this->getHelper('name')->shouldBe('name');
        $command->getHelperSet()->willReturn($helperSet);
        $this->getHelperSet()->shouldBe($helperSet);
        $command->getName()->willReturn('command:name');
        $this->getName()->shouldBe('command:name');
        $command->getNativeDefinition()->willReturn($inputDefinition);
        $this->getNativeDefinition()->shouldBe($inputDefinition);
        $command->getProcessedHelp()->willReturn('processed:help');
        $this->getProcessedHelp()->shouldBe('processed:help');
        $command->getSynopsis(false)->willReturn('synopsis');
        $this->getSynopsis(false)->shouldBe('synopsis');
        $command->ignoreValidationErrors()->shouldBeCalled();
        $this->ignoreValidationErrors();
        $command->isEnabled()->willReturn(true);
        $this->isEnabled()->shouldBe(true);
        $command->mergeApplicationDefinition(['merge' => 'arguments'])->shouldBeCalled();
        $this->mergeApplicationDefinition(['merge' => 'arguments']);
        $command->setHelperSet($helperSet)->shouldBeCalled();
        $this->setHelperSet($helperSet);
        $command->setApplication($application)->shouldBeCalled();
        $this->setApplication($application);
        $command->setDefinition($inputDefinition)->shouldBeCalled();
        $this->setDefinition($inputDefinition);
    }

    public function it_should_should_delegate_run_calls(Command $command, InputInterface $input, OutputInterface $output, LockHandler $lockHandler, InputDefinition $definition)
    {
        $command->mergeApplicationDefinition(true)->shouldBeCalled();
        $command->getDefinition()->willReturn($definition);
        $input->bind($definition)->shouldBeCalled();
        $this->beConstructedWith($command, $lockHandler);
        $lockHandler->lock()->willReturn(true);
        $lockHandler->release()->shouldBeCalled();
        $command->run($input, $output)->willReturn('command:output');
        $this->run($input, $output)->shouldBe('command:output');
    }

    public function it_should_release_the_lock_when_an_exception_is_thrown(Command $command, InputInterface $input, OutputInterface $output, LockHandler $lockHandler, InputDefinition $definition)
    {
        $command->mergeApplicationDefinition(true)->shouldBeCalled();
        $command->getDefinition()->willReturn($definition);
        $input->bind($definition)->shouldBeCalled();

        $this->beConstructedWith($command, $lockHandler);
        $command->run($input, $output)->willThrow('Exception');
        $lockHandler->lock()->willReturn(true);
        $lockHandler->release()->shouldBeCalled();
        $this->shouldThrow('Exception')->during('run', [$input, $output]);
    }

    public function it_should_block_runs_when_there_is_a_lock(Command $command, InputInterface $input, OutputInterface $output, LockHandler $lockHandler, InputDefinition $definition)
    {
        $command->mergeApplicationDefinition(true)->shouldBeCalled();
        $command->getDefinition()->willReturn($definition);
        $input->bind($definition)->shouldBeCalled();

        $this->beConstructedWith($command, $lockHandler);
        $command->getName()->willReturn('command:name');
        $output->getFormatter()->willReturn(new OutputFormatter());
        $this->beConstructedWith($command, $lockHandler);
        $lockHandler->lock()->willReturn(false);
        $this->shouldThrow('RuntimeException')->duringRun($input, $output);
    }

    public function it_should_respect_a_file_lock(Command $command, InputInterface $input, OutputInterface $output, InputDefinition $definition)
    {
        $lockHandler = new LockHandler('lock:name');
        $lockHandler->lock();
        $command->getName()->willReturn('command:name');
        $command->mergeApplicationDefinition(true)->shouldBeCalled();
        $command->getDefinition()->willReturn($definition);
        $input->bind($definition)->shouldBeCalled();
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_VERBOSE);
        $output->writeln(Argument::type('string'))->shouldBeCalled();
        $this->beConstructedWith($command, 'lock:name');
        $this->run($input, $output)->shouldBe(0);
        $lockHandler->release();
    }

    public function it_should_get_the_lock_name_from_the_command(Command $command, InputInterface $input)
    {
        $this->beConstructedWith($command);
        $command->getName()->willReturn('command:name');
        $this->getLockName($input)->shouldBe('command:name');
    }

    public function it_should_get_the_lock_name_from_the_specifier(InputInterface $input)
    {
        $command = new \CommandThatSpecifiesTheLockName('name');
        $this->beConstructedWith($command);
        $this->getLockName($input)->shouldBe('specified.lock.name');
    }

    public function it_should_get_the_lock_path_from_the_specifier(InputInterface $input)
    {
        $command = new \CommandThatSpecifiesTheLockPath('name');
        $this->beConstructedWith($command);
        $this->getLockPath($input)->shouldBe('/tmp/lock/path/');
    }

    public function it_should_get_the_lock_path_and_name_if_injected(Command $command, InputInterface $input)
    {
        $this->beConstructedWith($command, 'lock.name', 'lock.path');
        $this->getLockName($input)->shouldBe('lock.name');
        $this->getLockPath($input)->shouldBe('lock.path');
    }
}
