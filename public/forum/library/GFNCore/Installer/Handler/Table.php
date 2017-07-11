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
class GFNCore_Installer_Handler_Table extends GFNCore_Installer_Handler_Abstract
{
    public function seed($fromTable, $toTable, $primaryColumn, array $extraColumns = array())
    {
        $db = XenForo_Application::getDb();

        $selectFields = $primaryColumn;
        foreach ($extraColumns as $column)
        {
            $selectFields .= ', ' . $column;
        }

        do
        {
            $rows = $db->fetchAll(
                'SELECT ' . $selectFields . '
                FROM ' . $fromTable . '
                WHERE ' . $primaryColumn . ' NOT IN
                  (
                    SELECT ' . $primaryColumn . '
                    FROM ' . $toTable . '
                  )
                ORDER BY ' . $primaryColumn . '
                LIMIT 10000'
            );

            if (empty($rows))
            {
                return;
            }

            XenForo_Db::beginTransaction($db);

            try
            {
                foreach ($rows as $row)
                {
                    $db->insert($toTable, $row);
                }
            }
            catch (Exception $e) { }

            XenForo_Db::commit($db);
        }
        while (!empty($rows));
    }
}