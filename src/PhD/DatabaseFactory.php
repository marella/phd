<?php

namespace PhD;

class DatabaseFactory
{
    /**
     * The database connection factory instance.
     *
     * @var \PhD\ConnectionFactory
     */
    protected $factory;

    /**
     * @param \PhD\ConnectionFactory $factory
     */
    public function __construct(ConnectionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Create a new database manager instance.
     *
     * @param array $config
     *
     * @return \PhD\DatabaseManager
     */
    public function create(array $config)
    {
        return new DatabaseManager($config, $this->factory);
    }
}
