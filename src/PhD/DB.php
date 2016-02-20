<?php

namespace PhD;

use RuntimeException;

/**
 * Wrapper for DatabaseManager.
 */
class DB
{
    /**
     * Facade root instance.
     *
     * @var \PhD\DatabaseManager
     */
    protected static $facadeRoot;

    /**
     * Get the root object behind the facade.
     *
     * @return \PhD\DatabaseManager
     */
    public static function getFacadeRoot()
    {
        return static::$facadeRoot;
    }

    /**
     * Set the root object behind the facade.
     *
     * @param \PhD\DatabaseManager $db
     */
    public static function setFacadeRoot(DatabaseManager $db)
    {
        static::$facadeRoot = $db;
    }

    /**
     * Clear the root object behind the facade.
     */
    public static function clearFacadeRoot()
    {
        static::$facadeRoot = null;
    }

    /**
     * Create and set the root object behind the facade.
     *
     * @param array $config
     */
    public static function init(array $config)
    {
        if (!static::getFacadeRoot()) {
            $factory = new ConnectionFactory();
            $db = new DatabaseManager($config, $factory);
            static::setFacadeRoot($db);
        }
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        switch (count($args)) {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
}
