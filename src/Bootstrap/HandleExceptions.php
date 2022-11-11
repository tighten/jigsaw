<?php

namespace TightenCo\Jigsaw\Bootstrap;

use ErrorException;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;
use TightenCo\Jigsaw\Container;

class HandleExceptions
{
    public static $reservedMemory;

    protected static Container $app;

    public static function forgetApp(): void
    {
        static::$app = null;
    }

    public function bootstrap(Container $app): void
    {
        self::$reservedMemory = str_repeat('x', 32768);

        static::$app = $app;

        error_reporting(-1);

        set_error_handler($this->forwardTo('handleError'));
        set_exception_handler($this->forwardTo('handleException'));
        register_shutdown_function($this->forwardTo('handleShutdown'));

        // TODO
        // if (! $app->environment('testing')) {
        ini_set('display_errors', 'Off');
        // }
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = []): void
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block
     * in the kernel, but fatal error exceptions must be handled
     * differently since they are not normal exceptions.
     */
    public function handleException(Throwable $e): void
    {
        self::$reservedMemory = null;

        try {
            static::$app->make(ExceptionHandler::class)->report($e);
        } catch (Exception $e) {
            //
        }

        static::$app->make(ExceptionHandler::class)->renderForConsole(new ConsoleOutput, $e);
    }

    /**
     * Handle the PHP shutdown event.
     */
    public function handleShutdown(): void
    {
        self::$reservedMemory = null;

        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(new FatalError($error['message'], 0, $error, 0));
        }
    }

    /**
     * Forward a method call to the given method (on this class) if an application instance exists.
     */
    protected function forwardTo($method): callable
    {
        return fn (...$arguments) => static::$app
            ? $this->{$method}(...$arguments)
            : false;
    }

    protected function isFatal(int $type): bool
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
