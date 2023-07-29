<?php

declare(strict_types=1);

namespace App\Facade;

use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

class TaskoryExecuteCommand extends Command
{
    public function __construct(
        #[TaggedLocator('app.facade')] private readonly ServiceLocator $facades,
    ) {
        parent::__construct(null);
    }

    public function configure(): void
    {
        $this->setName('taskory:execute')
            ->addArgument('facade', InputArgument::REQUIRED, 'Facade to call a method on.')
            ->addArgument('method', InputArgument::REQUIRED, 'Method to call on the facade.')
            ->addArgument('arguments', InputArgument::IS_ARRAY, 'Method arguments to pass on.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $facadeArgument = $input->getArgument('facade');
        $method = $input->getArgument('method');
        $argumentsArgument = $input->getArgument('arguments');

        $facade = $this->getFacade($facadeArgument);
        $this->validateMethod($facade, $method);
        $arguments = $this->prepareArguments($argumentsArgument);

        $result = $facade->$method(...$arguments);

        if ($this->isValueReturned($facade, $method))
        {
            dump($result);
        }

        return 0;
    }

    private function getFacade(string $facadeName): FacadeInterface
    {
        if (!$this->facades->has($facadeName))
        {
            $facadesRegistered = implode(', ', array_keys($this->facades->getProvidedServices()));
            throw new RuntimeException("Facade '$facadeName' is not registered. The following facades are registered: $facadesRegistered");
        }

        return $this->facades->get($facadeName);
    }

    private function validateMethod(FacadeInterface $facade, string $method): void
    {
        if (!method_exists($facade, $method))
        {
            $facadeClass = get_class($facade);
            throw new RuntimeException("Method '$method' does not exist or is not accessible on facade '$facadeClass'.");
        }
    }

    private function isValueReturned(FacadeInterface $facade, string $method): bool
    {
        $methodMeta = new ReflectionMethod($facade, $method);
        $returnType = $methodMeta->getReturnType();

        return !($returnType instanceof ReflectionNamedType && in_array($returnType->getName(), ['void', 'never']));
    }

    private function prepareArguments(array $arguments): array
    {
        return array_map(static function($argument): int|float|string|array {
            return match (true) {
                ($argumentFloat = preg_replace('/^(float:)(.+)/', '$2', $argument, 1, $count)) && $count === 1 => (float) $argumentFloat,
                ($argumentInt = preg_replace('/^(int:)(.+)/', '$2', $argument, 1, $count)) && $count === 1 => (int) $argumentInt,
                ($argumentString = preg_replace('/^(string:)(.+)/', '$2', $argument, 1, $count)) && $count === 1 => (string) $argumentString,
                preg_match('/^\d*\.\d+$/', $argument) === 1 => (float) $argument,
                is_numeric($argument) => (int) $argument,
                ($array = json_decode($argument, true)) !== null => $array,
                default => $argument,
            };
        }, $arguments);
    }
}
