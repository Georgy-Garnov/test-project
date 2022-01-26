<?php

namespace App\Common;

use Doctrine\DBAL\Connection;

/**
 * Database connection holder
 */
class DBConnection {
  private static Connection $connection;

  /**
   * Save database connection
   *
   * @param Connection $connection
   */
  public static function setConnection(Connection $connection): void {
    static::$connection = $connection;
  }

  /**
   * Return database connection
   *
   * @return Connection
   */
  public static function getConnection(): Connection {
    return static::$connection;
  }
}
