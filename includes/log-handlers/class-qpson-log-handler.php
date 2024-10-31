<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

defined('ABSPATH') || exit;

class Qpson_Log_Handler extends AbstractProcessingHandler
{

    private static $instance;
    private $initialized = false;
    private $wpdb;
    private $tableName;

    const TABLE_NAME = Qpson_Meta::LOGGER_TABLE_NAME;


    public function __construct($wpdb, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->wpdb = $wpdb;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {

        if (!$this->initialized) {
            $this->initialize();
        }

        $query = $this->wpdb->prepare(
            "INSERT INTO %s (log,context, created_at)  VALUES (%s,%s,%s)", 
                sanitize_text_field($this->tableName),
                $record['formatted'],
                implode(',', $record['context']),
                $record['datetime']->jsonSerialize()
        );
        $this->wpdb->query($query);
    }

    public function initialize()
    {
        $this->tableName = $this->wpdb->prefix . self::TABLE_NAME;
        $this->initialized = true;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            global $wpdb;
            $handler = new self($wpdb);
            $lineFormatter = new LineFormatter(LineFormatter::SIMPLE_FORMAT, LineFormatter::SIMPLE_DATE);
            $lineFormatter->includeStacktraces();
            $handler->setFormatter($lineFormatter);

            self::$instance = $handler;
        }
        return self::$instance;
    }
}
