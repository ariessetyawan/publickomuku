<?php

class phc_AlphabeticalXF_Model_Alpha extends XenForo_Model
{
    public function getAlphaStatement($fieldname, $res)
    {
        if(!empty($GLOBALS['alpha']))
        {
            $db = $this->_getDb();
            $sqlConditions = array($res);

            // Check Language
            $alphaHelper = new phc_AlphabeticalXF_Helper_Helper();
            $langugageABC = $alphaHelper->getLangugageABCForOther();

            $data = array();
            if($langugageABC)
            {
                foreach($langugageABC as $lang)
                {
                    foreach($lang as $value)
                    {
                        $data[] = $value;
                    }
                }
            }

            if($GLOBALS['alpha'] == '0-9' || $GLOBALS['alpha'] == 'other')
            {
                if($GLOBALS['alpha'] == '0-9')
                    $sqlConditions[] = $fieldname . ' REGEXP  \'^[0-9]\'';

                if($GLOBALS['alpha'] == 'other')
                    $sqlConditions[] = $fieldname . ' NOT REGEXP  \'^[' . implode('', $data) . '0-9]\'';
            }
            else
            {
                $sqlConditions[] = ' LOWER(' . $fieldname . ') LIKE ' . XenForo_Db::quoteLike(mb_convert_case($GLOBALS['alpha'], MB_CASE_LOWER, "UTF-8"), 'r', $db);
            }

            return $this->getConditionsForClause($sqlConditions);
        }

        return $res;
    }
}