<?php

class KomuKu_NodeConverter_XenForo_Model_Node extends XFCP_KomuKu_NodeConverter_XenForo_Model_Node
{
	public function getNodeById($nodeId, array $fetchOptions = array())
	{
		$node = parent::getNodeById($nodeId, $fetchOptions);

		if (!empty($node) && $node['node_type_id'] == 'Category')
		{
			$node['canConvert'] = true;
		}

		return $node;
	}
}