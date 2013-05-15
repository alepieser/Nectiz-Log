<?php

namespace Nectiz\Log;

//use Psr\LogLoggerInterface;
//use Psr\Log\InvalidArgumentException;

use Nectiz\Log\Writer\WriterInterface;

class Logger implements LoggerInterface
{
    /**
     * @const int defined from the BSD Syslog message severities
     * @link http://tools.ietf.org/html/rfc3164
     */
    const EMERG  = 0;
    const ALERT  = 1;
    const CRIT   = 2;
    const ERR    = 3;
    const WARN   = 4;
    const NOTICE = 5;
    const INFO   = 6;
    const DEBUG  = 7;

    /**
     * List of priority code => priority (short) name
     * @var array
     */
    protected static $levels = array(
        self::EMERG  => 'EMERG',
        self::ALERT  => 'ALERT',
        self::CRIT   => 'CRIT',
        self::ERR    => 'ERR',
        self::WARN   => 'WARN',
        self::NOTICE => 'NOTICE',
        self::INFO   => 'INFO',
        self::DEBUG  => 'DEBUG',
    );

    protected $chanel;

    protected $writers;

    public function __construct($chanel = '', array $writers = array())
    {
        $this->chanel = $chanel;
        $this->writers = $writers;
    }

    public function addWriter(Writer\WriterInterface $writer)
    {
        if (!$writer instanceof Writer\WriterInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Writer must implement %s\Writer\WriterInterface; received "%s"',
                __NAMESPACE__,
                is_object($writer) ? get_class($writer) : gettype($writer)
            ));
        }

        array_push($this->writers, $writer);
    }

    public function getLevelName($level)
    {
        if (!isset(static::$levels[$level])) {
            throw new InvalidArgumentException('Level "' . $level .'" is not defined');
        }

        return static::$levels[$level];
    }

    public function log($level, $message, array $context = array())
    {
        if (!is_int($level) || $level < 0 || $level >= count(static::$levels)) {
            throw new InvalidArgumentException(sprintf(
                '$level must be an integer > 0 and %d; received %s',
                count($this->levels),
                var_export($level, 1)
            ));
        }

        if (is_object($message) && !method_exists($message, '__toString')) {
            throw new InvalidArgumentException('$message must implement magic __toString() method');
        }

        if ($this->writers->count() === 0) {
            throw new RuntimeException('No log writer specified');
        }

        $timestamp = new DateTime();

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        $log = array(
            'message' => (string)$message,
            'context' => $context,
            'level' => $level,
            'levelName' => static::getLevelName($level),
            'channel' => $this->chanel,
            'timestamp' => $timestamp,
            'extra' => array(),
        );

        $writerKey = null;
        foreach ($this->writers as $key => $writer) {
            if ($writer->isWriting($log)) {
                $writerKey = $key;
                break;
            }
        }

        // none found
        if (null === $writerKey) {
            return false;
        }

        while (isset($this->writers[$writerKey]) &&
            false === $this->writers[$writerKey]->write($log)) {
            $writerKey++;
        }

        return true;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(self::EMERG, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(self::CRIT, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(self::ERR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(self::WARN, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(self::DEBUG, $message, $context);
    }

}