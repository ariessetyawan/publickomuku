<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Model_DataCache extends XenForo_Model
{
    const MINUTE    = 60;
    const HOUR      = 3600;
    const DAY       = 86400;
    const WEEK      = 604800;
    const MONTH     = 2592000;
    const YEAR      = 31536000;

    public function get($itemName)
    {
        $cacheItem = $this->_getCacheEntryName($itemName);
        $cache = $this->_getCache(true);

        $cacheData = ($cache ? $cache->load($cacheItem) : false);
        if ($cacheData !== false)
        {
            return unserialize($cacheData);
        }

        $row = $this->_getFromDb($itemName);

        if (is_array($row))
        {
            if ($cache)
            {
                $cache->save($row['data_value'], $cacheItem, array(), $row['expire_date'] - XenForo_Application::$time);
            }

            return unserialize($row['data_value']);
        }

        return $this->_getFromSource($itemName);
    }

    protected function _getFromDb($itemName)
    {
        return $this->_getDb()->fetchRow(
            'SELECT data_value, expire_date
            FROM gfncore_data_cache
            WHERE data_key = ?
            AND expire_date > ?', array($itemName, XenForo_Application::$time)
        );
    }

    protected function _getFromSource($itemName)
    {
        $value = null; $lifetime = 0;
        XenForo_CodeEvent::fire('gfncore_cache_source', array($itemName, &$value, &$lifetime), $itemName);

        if ($value === null || intval($lifetime) == 0)
        {
            return null;
        }

        $this->set($itemName, $value, $lifetime);
        return $value;
    }

    public function getMulti($itemNames)
    {
        if (!$itemNames)
        {
            return array();
        }

        $cache = $this->_getCache(true);
        $dbItemNames = $itemNames;
        $data = array();

        foreach ($itemNames as $k => $itemName)
        {
            $cacheData = ($cache ? $cache->load($this->_getCacheEntryName($itemName)) : false);
            if ($cacheData !== false)
            {
                $data[$itemName] = $cacheData;
                unset($dbItemNames[$k]);
            }
        }

        if ($dbItemNames)
        {
            $dbData = $this->_getMultiFromDb($dbItemNames);

            if ($dbData)
            {
                foreach ($dbData as $itemName => $row)
                {
                    $data[$itemName] = $row['data_value'];

                    if ($cache)
                    {
                        $cache->save(
                            $row['data_value'], $this->_getCacheEntryName($itemName),
                            array(), $row['expire_date'] - XenForo_Application::$time
                        );
                    }
                }
            }
        }

        foreach ($itemNames as $itemName)
        {
            if (!isset($data[$itemName]))
            {
                $data[$itemName] = $this->_getFromSource($itemName);
            }
            else
            {
                $data[$itemName] = unserialize($data[$itemName]);
            }
        }

        return $data;
    }

    protected function _getMultiFromDb(array $itemNames)
    {
        if (!$itemNames)
        {
            return false;
        }

        $db = $this->_getDb();

        return $db->fetchAssoc(
            'SELECT data_key, data_value, expire_date
            FROM gfncore_data_cache
            WHERE data_key IN (' . $db->quote($itemNames) . ')'
        );
    }

    public function set($itemName, $value, $lifetime)
    {
        $lifetime = intval($lifetime);
        if (!$lifetime)
        {
            return;
        }

        $serialized = serialize($value);

        $this->_getDb()->query(
            'INSERT INTO gfncore_data_cache
              (data_key, data_value, expire_date)
            VALUES
              (?, ?, ?)
            ON DUPLICATE KEY UPDATE
              data_value = VALUES(data_value),
              expire_date = VALUES(expire_date)', array(
                $itemName, $serialized,
                XenForo_Application::$time + $lifetime
            )
        );

        $cache = $this->_getCache(true);
        if ($cache)
        {
            $cache->save($serialized, $this->_getCacheEntryName($itemName), array(), $lifetime);
        }
    }

    public function delete($itemName, $onlyIfExpired = false)
    {
        $db = $this->_getDb();
        $affected = $db->delete('gfncore_data_cache', 'data_key = ' . $db->quote($itemName) . ($onlyIfExpired ? ' AND expire_date <= ' . $db->quote(XenForo_Application::$time) : ''));

        $cache = $this->_getCache(true);
        if ($affected && $cache)
        {
            $cache->remove($this->_getCacheEntryName($itemName));
        }

        return $affected;
    }

    public function deleteExpiredItems()
    {
        $db = $this->_getDb();

        $items = $db->fetchCol(
            'SELECT data_key
            FROM gfncore_data_cache
            WHERE expire_date <= ?', XenForo_Application::$time
        );

        if (!$items)
        {
            return;
        }

        $db->delete('gfncore_data_cache', 'expire_date <= ' . $db->quote(XenForo_Application::$time));

        $cache = $this->_getCache(true);
        if ($cache)
        {
            foreach ($items as $itemName)
            {
                $cache->remove($this->_getCacheEntryName($itemName));
            }
        }
    }

    protected function _getCacheEntryName($itemName)
    {
        return 'gfncache_' . $itemName;
    }
}