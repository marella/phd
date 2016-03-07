<?php

namespace PhD;

use InvalidArgumentException;
use PDO;
use PhD\Connections\MySqlConnection;
use PhD\Connectors\MySqlConnector;
use PhD\Support\Arr;

class ConnectionFactory
{
    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param array  $config
     * @param string $name
     *
     * @return \PhD\Connections\Connection
     */
    public function make(array $config, $name = null)
    {
        $config = $this->parseConfig($config, $name);

        if (isset($config['read'])) {
            return $this->createReadWriteConnection($config);
        }

        return $this->createSingleConnection($config);
    }

    /**
     * Create a single database connection instance.
     *
     * @param array $config
     *
     * @return \PhD\Connections\Connection
     */
    protected function createSingleConnection(array $config)
    {
        $pdo = function () use ($config) {
            return $this->createConnector($config)->connect($config);
        };

        return $this->createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
    }

    /**
     * Create a single database connection instance.
     *
     * @param array $config
     *
     * @return \PhD\Connections\Connection
     */
    protected function createReadWriteConnection(array $config)
    {
        $connection = $this->createSingleConnection($this->getWriteConfig($config));

        return $connection->setReadPdo($this->createReadPdo($config));
    }

    /**
     * Create a new PDO instance for reading.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createReadPdo(array $config)
    {
        $readConfig = $this->getReadConfig($config);

        return $this->createConnector($readConfig)->connect($readConfig);
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @param array $config
     *
     * @return array
     */
    protected function getReadConfig(array $config)
    {
        $readConfig = $this->getReadWriteConfig($config, 'read');

        if (isset($readConfig['host']) && is_array($readConfig['host'])) {
            $readConfig['host'] = count($readConfig['host']) > 1
                ? $readConfig['host'][array_rand($readConfig['host'])]
                : $readConfig['host'][0];
        }

        return $this->mergeReadWriteConfig($config, $readConfig);
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @param array $config
     *
     * @return array
     */
    protected function getWriteConfig(array $config)
    {
        $writeConfig = $this->getReadWriteConfig($config, 'write');

        return $this->mergeReadWriteConfig($config, $writeConfig);
    }

    /**
     * Get a read / write level configuration.
     *
     * @param array  $config
     * @param string $type
     *
     * @return array
     */
    protected function getReadWriteConfig(array $config, $type)
    {
        if (isset($config[$type][0])) {
            return $config[$type][array_rand($config[$type])];
        }

        return $config[$type];
    }

    /**
     * Merge a configuration for a read / write connection.
     *
     * @param array $config
     * @param array $merge
     *
     * @return array
     */
    protected function mergeReadWriteConfig(array $config, array $merge)
    {
        return Arr::except(array_merge($config, $merge), ['read', 'write']);
    }

    /**
     * Parse and prepare the database configuration.
     *
     * @param array  $config
     * @param string $name
     *
     * @return array
     */
    protected function parseConfig(array $config, $name)
    {
        return Arr::add(Arr::add($config, 'prefix', ''), 'name', $name);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \PhD\Connectors\ConnectorInterface
     */
    public function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        switch ($config['driver']) {
            case 'mysql':
                return new MySqlConnector();
        }

        throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
    }

    /**
     * Create a new connection instance.
     *
     * @param string        $driver
     * @param \PDO|\Closure $connection
     * @param string        $database
     * @param string        $prefix
     * @param array         $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \PhD\Connections\Connection
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
        }

        throw new InvalidArgumentException("Unsupported driver [$driver]");
    }
}
