<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * ProfilerStorageInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Ecocode_Profiler_Model_Profiler_StorageInterface
{
    /**
     * Finds profiler tokens for the given criteria.
     *
     * @param string   $ip     The IP
     * @param string   $url    The URL
     * @param string   $limit  The maximum number of tokens to return
     * @param string   $method The request method
     * @param int|null $start  The start date to search from
     * @param int|null $end    The end date to search to
     *
     * @return array An array of tokens
     */
    public function find($ip, $url, $limit, $method, $start = null, $end = null);

    /**
     * Reads data associated with the given token.
     *
     * The method returns false if the token does not exist in the storage.
     *
     * @param string $token A token
     *
     * @return Ecocode_Profiler_Model_Profile The profile associated with token
     */
    public function read($token);

    /**
     * Saves a Profile.
     *
     * @param Ecocode_Profiler_Model_Profile $profile A Profile instance
     *
     * @return bool Write operation successful
     */
    public function write(Ecocode_Profiler_Model_Profile $profile);

    /**
     * Purges all data from the database.
     */
    public function purge();
}
