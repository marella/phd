<?php

namespace PhD;

interface ConnectionResolverInterface
{
    /**
     * Get a database connection instance.
     *
     * @param string $name
     *
     * @return \PhD\Connections\ConnectionInterface
     */
    public function connection($name = null);

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection();
}
