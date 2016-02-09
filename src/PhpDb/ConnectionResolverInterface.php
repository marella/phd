<?php

namespace PhpDb;

interface ConnectionResolverInterface
{
    /**
     * Get a database connection instance.
     *
     * @param string $name
     *
     * @return \PhpDb\Connections\ConnectionInterface
     */
    public function connection($name = null);

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection();
}
