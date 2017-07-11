<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Model_Thread extends XFCP_KomuKu_LikeThreads_Model_Thread
{
	//Sort threads based on likes
	public function prepareThreadFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $orderBy = '';

        if (!empty($fetchOptions['order'])) {
            switch ($fetchOptions['order']) {
                case 'like_count':
                    $orderBy = 'thread.like_count';
                    break;
            }
            if ($orderBy) {
                if (!isset($fetchOptions['orderDirection']) || $fetchOptions['orderDirection'] == 'desc') {
                    $orderBy .= ' DESC';
                } else {
                    $orderBy .= ' ASC';
                }
            }
        }

        $threadFetchOptions = parent::prepareThreadFetchOptions($fetchOptions);

        return array(
            'selectFields' => $threadFetchOptions['selectFields'] . $selectFields,
            'joinTables' => $joinTables . $threadFetchOptions['joinTables'],
            'orderClause' => ($orderBy ? "ORDER BY $orderBy" : $threadFetchOptions['orderClause'])
        );
    }
}