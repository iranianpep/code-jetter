<?php

namespace CodeJetter\core;

use CodeJetter\libs\Monolog\Formatter\LineFormatter;
use CodeJetter\libs\Monolog\Handler\ChromePHPHandler;
use CodeJetter\libs\Monolog\Handler\HipChatHandler;
use CodeJetter\libs\Monolog\Handler\MongoDBHandler;
use CodeJetter\libs\Monolog\Handler\StreamHandler;
use CodeJetter\libs\Monolog\Logger;
use CodeJetter\libs\Psr\Log\LoggerInterface;

/**
 * Class ErrorHandler
 *
 * A class to handle errors, exceptions and also to log errors, notices, etc.
 *
 * How to use it:
 * (new ErrorHandler())->run();
 *
 * This will set the custom error handler to log errors including fatal and notices using a logger (currently Monolog)
 *
 * In order to log errors manually use it like this:
 * (new ErrorHandler())->logError('this is a test');
 */
class ErrorHandler
{
    private $configs;
    private $logger;

    /**
     * ErrorHandler constructor.
     *
     * @param array $configs
     */
    public function __construct(array $configs = [])
    {
        // overwrite the default configs if $configs is passed
        $defaultConfigs = Registry::getConfigClass()->get('errorHandler');
        $this->setConfigs(array_merge($defaultConfigs, $configs));

        $this->initLogger();
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * @param array $configs
     */
    public function setConfigs(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @param Logger $logger
     * @param $handlerKey
     * @param array $handlerConfig
     * @return Logger
     * @throws \Exception
     */
    private function pushMonologHandler(Logger $logger, $handlerKey, $handlerConfig = [])
    {
        $activeHandlers = [];

        switch ($handlerKey) {
            case 'hipchat':
                if (empty($handlerConfig['token'])) {
                    throw new \Exception('Token cannot be empty for Hipchat handler');
                }

                if (empty($handlerConfig['room'])) {
                    throw new \Exception('Room cannot be empty for Hipchat handler');
                }

                $handler = new HipChatHandler($handlerConfig['token'], $handlerConfig['room']);
                $handler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context%\n"));

                $activeHandlers[] = $handler;
                break;
            case 'mongo':
                if (empty($handlerConfig['server']) || empty($handlerConfig['db'])
                    || empty($handlerConfig['collection'])) {
                    throw new \Exception('MongoDB details are not provided');
                }

                $activeHandlers[] = new MongoDBHandler(
                    new \MongoClient($handlerConfig['server']),
                    $handlerConfig['db'],
                    $handlerConfig['collection']
                );

                // Status 'new' means that the error has not been addressed yet
                $logger->pushProcessor(function ($record) {
                    $record['status'] = 'new';
                    return $record;
                });

                break;
            case 'chrome':
                $activeHandlers[] = new ChromePHPHandler();
                break;
            case 'file':
                if (empty($handlerConfig['path'])) {
                    throw new \Exception('File path is empty');
                }

                $activeHandlers[] = new StreamHandler($handlerConfig['path']);
                break;
        }

        if (!empty($activeHandlers)) {
            foreach ($activeHandlers as $activeHandler) {
                $logger->pushHandler($activeHandler);
            }
        }

        return $logger;
    }

    /**
     * @throws \Exception
     */
    private function initLogger()
    {
        // get options
        $configs = $this->getConfigs();

        $channelName = isset($configs['monolog']['channel']) ? $configs['monolog']['channel'] : '';

        // init the logger
        $logger = new Logger($channelName);

        /**
         * Start pushing handlers
         */
        if (empty($configs['monolog']['handlers'])) {
            throw new \Exception('Monolog must have at least one handler');
        }

        foreach ($configs['monolog']['handlers'] as $handlerKey => $handlerConfig) {
            if ($handlerConfig['active'] === true) {
                $logger = $this->pushMonologHandler($logger, $handlerKey, $handlerConfig);
            }
        }
        /**
         * Finish pushing handlers
         */

        $this->setLogger($logger);
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    private function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function run()
    {
        if ($this->getConfigs()['inOperation'] !== true) {
            return false;
        }

        $this->registerShutdownFunction();
        $this->setErrorHandler();
        $this->setExceptionHandler();

        return $this;
    }

    /**
     * This is used to handle fatal errors since set_error_handler cannot do that
     */
    private function registerShutdownFunction()
    {
        register_shutdown_function(function () {
            $error = error_get_last();

            if (isset($error['type']) && $error['type'] === E_ERROR) {
                if ($this->canLog($error['message'], $error['file']) === true) {
                    $this->logError($error['message'], [
                        'module' => $this->extractComponentName($error['file']),
                        'number' => $error['type'],
                        'line' => $error['line'],
                        'file' => $error['file']
                    ]);
                }
            }
        });
    }

    private function setErrorHandler()
    {
        set_error_handler(function ($errorNo, $errorMessage, $errorFile, $errorLine) {
            if ($this->canLog($errorMessage, $errorFile) === true) {
                $this->logError($errorMessage, [
                    'component' => $this->extractComponentName($errorFile),
                    'number' => $errorNo,
                    'line' => $errorLine,
                    'file' => $errorFile
                ]);
            }

            if ($this->getConfigs()['bypassInternalErrorHandler'] === true) {
                // Do NOT execute PHP internal error handler
                return true;
            } else {
                // Execute PHP internal error handler as well
                return false;
            }
        });
    }

    private function canLog($errorMessage, $errorFile)
    {
        $configs = $this->getConfigs();
        if ($configs['inOperation'] !== true) {
            return false;
        }

        if ($configs['respectErrorReporting'] === true && error_reporting() == 0) {
            return false;
        }

        if ($configs['respectDisplayErrors'] === true && empty(ini_get('display_errors'))) {
            return false;
        }

        if ($this->isInBlacklist($errorMessage, $errorFile) !== false) {
            return false;
        }

        return true;
    }

    private function setExceptionHandler()
    {
        set_exception_handler(function (\Exception $exception) {
            if ($this->canLog($exception->getMessage(), $exception->getFile()) === true) {
                $this->logError($exception->getMessage(), [
                    'component' => $this->extractComponentName($exception->getFile()),
                    'number' => $exception->getCode(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile()
                ]);
            }
        });
    }

    /**
     * @param $errorFile
     * @return string
     */
    private function extractComponentName($errorFile)
    {
        // TODO implement body
    }

    /**
     * @param $errorMessage
     * @return bool
     */
    private function isInBlacklist($errorMessage, $errorFile)
    {
        $configs = $this->getConfigs();

        if ($configs['blacklist']['inOperation'] !== true) {
            return false;
        }

        /**
         * Start checking against strings
         */
        if (!empty($configs['blacklist']['strings'])) {
            foreach ($configs['blacklist']['strings'] as $string) {
                if (strpos($errorMessage, $string) !== false) {
                    return true;
                }
            }
        }
        /**
         * Finish checking against strings
         */

        /**
         * Start checking against regular expressions
         */
        if (!empty($configs['blacklist']['regex'])) {
            foreach ($configs['blacklist']['regex'] as $regex) {
                if (preg_match($regex, $errorMessage)) {
                    return true;
                }
            }
        }
        /**
         * Finish checking against regular expressions
         */

        $component = $this->extractComponentName($errorFile);
        if (!empty($component) && in_array($component, $configs['blacklist']['components'])) {
            return true;
        }

        // did not find in the blacklist, return false
        return false;
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logDebug($message, array $context = [])
    {
        $this->logMessage('debug', $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logInfo($message, array $context = [])
    {
        $this->logMessage('info', $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logNotice($message, array $context = [])
    {
        $this->logMessage('notice', $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logWarning($message, array $context = [])
    {
        $this->logMessage('warning', $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logError($message, array $context = [])
    {
        $this->logMessage('error', $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logCritical($message, array $context = [])
    {
        $this->logMessage('critical', $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logAlert($message, array $context = [])
    {
        $this->logMessage('alert', $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function logEmergency($message, array $context = [])
    {
        $this->logMessage('emergency', $message, $context);
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return bool
     * @throws \Exception
     */
    private function logMessage($level, $message, $context = [])
    {
        if ($this->getConfigs()['inOperation'] !== true) {
            return false;
        }

        switch ($level) {
            case 'debug':
                $this->getLogger()->debug($message, $context);
                break;
            case 'info':
                $this->getLogger()->info($message, $context);
                break;
            case 'notice':
                $this->getLogger()->notice($message, $context);
                break;
            case 'warning':
                $this->getLogger()->warning($message, $context);
                break;
            case 'error':
                $this->getLogger()->error($message, $context);
                break;
            case 'critical':
                $this->getLogger()->critical($message, $context);
                break;
            case 'alert':
                $this->getLogger()->alert($message, $context);
                break;
            case 'emergency':
                $this->getLogger()->emergency($message, $context);
                break;
            default:
                throw new \Exception("'{$level}' is invalid log level");
        }
    }
}
