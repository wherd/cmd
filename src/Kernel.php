<?php

declare(strict_types=1);

namespace Wherd\Cmd;

class Kernel
{
    /** @param array<string,callable|string|object> $handlers */
    public function __construct(protected array $handlers = [])
    {
    }

    /** @param array<string,string|callable|object> $handlers */
    public function addHandlers(array $handlers): void
    {
        $this->handlers = array_merge($this->handlers, $handlers);
    }

    /** @param string|callable|object $callback */
    public function handle(string $command, $callback): void
    {
        $this->handlers[$command] = $callback;
    }

    /** @param mixed $args */
    public function dispatch(string $command, ...$args): string
    {
        if (!isset($this->handlers[$command])) {
            return 'Command not found.';
        }

        $handler = $this->handlers[$command];

        if (is_callable($handler)) {
            return $handler(...$args);
        } elseif (is_string($handler)) {
            [$name, $method] = array_pad(explode('@', $handler, 2), 2, '');

            $handler = [new $name(), $method ?: 'dispatch'];

            if (is_callable($handler)) {
                return $handler(...$args);
            }
        } elseif (is_object($handler)) {
            $handler = [$handler, 'dispatch'];

            if (is_callable($handler)) {
                return $handler(...$args);
            }
        }

        return 'Command not found.';
    }

    public function handleError(int $errno, string $msg, string $file = '', int $line = 0): void
    {
        echo 'Error(', $errno ,'): ', $msg, ' on ', basename($file), ':', $line, "\n\n";
        debug_print_backtrace();
        die;
    }
}
