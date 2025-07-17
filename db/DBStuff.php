<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

/**
 * Defines abstracted database query methods and connection methods.
 *
 * @since 1.9 Formerly dbStruct, removed after 1.9.5.
 * @since 1.10.00
 */
interface DBStuff
{
    public const SQL_ASSOC = 1;
    public const SQL_NUM = 2;
    public const SQL_BOTH = 3;

    /**
     * Checks all runtime dependencies and informs the caller whether or not the object is useable.
     *
     * @return bool
     */
    public function isInstalled(): bool;

    /**
     * Establishes a connection to the database.
     *
     * @param string $dbhost
     * @param string $dbuser
     * @param string $dbpw
     * @param string $dbname
     * @param bool   $pconnect Keep the connection open after the script ends.
     * @param bool   $force_db Generate a fatal error if the $dbname database doesn't exist on the server.
     * @return bool  Whether or not the database was found after connecting.
     */
    public function connect(string $dbhost, string $dbuser, string $dbpw, string $dbname, bool $pconnect = false, bool $force_db = false): bool;

    /**
     * Attempts a connection and does not generate error messages.
     *
     * @param string $dbhost
     * @param string $dbuser
     * @param string $dbpw
     * @param string $dbname
     * @return bool  Whether or not the connection was made and the database was found.
     */
    public function testConnect(string $dbhost, string $dbuser, string $dbpw, string $dbname): bool;

    /**
     * Gets any error message that was encountered during the last call to testConnect().
     *
     * Error messages are likely to contain sensitive file path info.
     * This method is intended for use by Super Administrators and install/upgrade scripts only.
     *
     * @return string Error message or empty string.
     */
    public function getTestError(): string;

    /**
     * Closes a connection that is no longer needed.
     */
    public function close();

    /**
     * Sets the name of the database to be used on this connection.
     *
     * @param string $database The full name of the database.
     * @param string $force Optional. Specifies error mode. Dies if 'yes'.
     * @return bool TRUE on success.
     */
    public function select_db(string $database, string $force = 'yes'): bool;

    /**
     * Searches for an accessible database containing the XMB settings table.
     *
     * @param string $tablepre The settings table name prefix.
     * @return bool
     */
    public function find_database(string $tablepre): bool;

    /**
     * Fetch the last error message.
     */
    public function error(): string;

    /**
     * Frees memory used by a result set that is no longer needed.
     */
    public function free_result($result);

    /**
     * Fetch an array representing the next row of a result.
     *
     * The array type can be associative, numeric, or both.
     *
     * @param mixed $result
     * @param int $type The type of indexing to add to the array: SQL_ASSOC, SQL_NUM, or SQL_BOTH
     * @return array|null Returns an array representing the fetched row, or null if there are no more rows.
     */
    public function fetch_array($result, int $type = self::SQL_ASSOC): ?array;

    /**
     * Fetch an array representing all rows of a result.
     *
     * The 2nd-level array type used within the list of rows can be associative, numeric, or both.
     *
     * @param mysqli_result $result
     * @param int $type The type of indexing to add to the array: SQL_ASSOC, SQL_NUM, or SQL_BOTH
     * @return array Returns an array representing a list of row arrays.
     */
    public function fetch_all($result, int $type = self::SQL_ASSOC): array;

    /**
     * Get the name of the specified field from a result set.
     */
    public function field_name($result, int $field): string;

    /**
     * Returns the length of a field as specified in the database schema.
     *
     * @param mixed $result The result of a query.
     * @param int $field The field_offset starts at 0.
     * @return int
     */
    public function field_len($result, int $field): int;

    /**
     * Can be used to make any expression query-safe.
     *
     * Example:
     *  $sqlinput = $db->escape($rawinput);
     *  $db->query("UPDATE a SET b = 'Hello, my name is $sqlinput'");
     *
     * @param string $rawstring
     * @return string
     */
    public function escape(string $rawstring): string;

    /**
     * Preferred for performance when escaping any string variable.
     *
     * Note this only works when the raw value can be discarded.
     *
     * Example:
     *  $db->escape_fast($rawinput);
     *  $db->query("UPDATE a SET b = 'Hello, my name is $rawinput'");
     *
     * @param string $sql Read/Write Variable
     */
    public function escape_fast(string &$sql);

    /**
     * Escape a string used with the LIKE operator.
     *
     * Any required wildcards must be added separately (must not be escaped by this method).
     *
     * @param string $rawstring
     * @return string
     */
    public function like_escape(string $rawstring): string;

    /**
     * Escape a string used with the REGEXP operator.
     */
    public function regexp_escape(string $rawstring): string;

    /**
     * Executes a Query against the database.
     *
     * @param string $sql Unique query (multiple queries are not supported). The query string should not end with a semicolon.
     * @param bool $panic XMB will die and use dbstuff::panic() in case of any error unless this param is set to FALSE.
     * @return mixed Returns a resource or a bool, depending on the query type and error status.
     */
    public function query(string $sql, bool $panic = true);

    /**
     * Sends a query without fetching the result rows.
     *
     * You cannot use mysqli_num_rows() and mysqli_data_seek() on a result set
     * returned from mysqli_use_result(). You also have to call
     * mysqli_free_result() before you can send a new query.
     *
     * @param string $sql Unique query (multiple queries are not supported). The query string should not end with a semicolon.
     * @param bool $panic XMB will die and use dbstuff::panic() in case of any error unless this param is set to FALSE.
     * @return mixed Returns a resource or a bool, depending on the query type and error status.
     */
    public function unbuffered_query(string $sql, $panic = true);

    /**
     * Fetch the list of tables in a database.
     */
    public function fetch_tables(?string $dbname = null): array;

    /**
     * Retrieves the contents of one cell from a MySQL result set.
     *
     * @param mixed $result
     * @param int $row Optional. The zero-based row number from the result that's being retrieved.
     * @param int $field Optional. The zero-based offset of the field being retrieved.
     * @return ?string
     */
    public function result($result, int $row = 0, int $field = 0): ?string;

    /**
     * Retrieves the row count from a query result.
     */
    public function num_rows($result): int;

    /**
     * Retrieves the column count from a query result.
     */
    public function num_fields($result): int;

    /**
     * Retrieves the ID of the last auto increment record or insert ID.
     *
     * @return mixed
     */
    public function insert_id(): int|string;

    /**
     * Fetch an enumerated array representing the next row of a result.
     *
     * @return ?array Enumerated array of values, or null for end of result.
     */
    public function fetch_row($result): ?array;

    /**
     * Adjusts the result pointer in a specific row in the result set.
     */
    public function data_seek($result, int $row): bool;

    /**
     * Gets the number of rows affected by the previous query.
     */
    public function affected_rows(): int;

    /**
     * Retrieve the database server version number.
     *
     * @return string
     */
    public function server_version(): string;

    /**
     * Retrieve the cumulative query time on this object.
     *
     * @return float
     */
    public function getDuration(): float;

    /**
     * Retrieve the cumulative query count on this object.
     *
     * @return int
     */
    public function getQueryCount(): int;

    /**
     * Retrieve the list of queries sent by this object.
     *
     * @return array
     */
    public function getQueryList(): array;

    /**
     * Retrieve the list of times used by each query sent by this object.
     *
     * @return array
     */
    public function getQueryTimes(): array;
}
