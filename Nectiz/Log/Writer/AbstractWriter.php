<?php

namespace Nectiz\Log\Writer;

use Nectiz\Log\Logger;
// use Nectiz\Formatter\FormatterInterface;
// use Nectiz\Formatter\LineFormatter;


abstract class AbstractHandler implements WriterInterface
{
    protected $level = Logger::DEBUG;
    protected $bubble = false;

    protected $formatter;
    protected $processors = array();

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        $this->level = $level;
        $this->bubble = $bubble;
    }

    public function isWriting(array $log)
    {
        return $log['level'] >= $this->level;
    }

    public function writeBatch(array $logs)
    {
        foreach ($logs as $log) {
            $this->write($log);
        }
    }

    abstract public function write(array $log);

    public function close()
    {
    }

    public function addProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }
        array_unshift($this->processors, $callback);
    }

    public function popProcessor()
    {
        if (!$this->processors) {
            throw new \LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Exception $e) {
            // do nothing
        }
    }
}
