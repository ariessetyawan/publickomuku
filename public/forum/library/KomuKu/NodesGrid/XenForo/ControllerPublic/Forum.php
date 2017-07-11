<?php
class KomuKu_NodesGrid_XenForo_ControllerPublic_Forum extends XFCP_KomuKu_NodesGrid_XenForo_ControllerPublic_Forum
{
	public function actionIndex()
	{
		$response = parent::actionIndex();
		if($response instanceof XenForo_ControllerResponse_View && empty($response->params['canManageGrid']))
		{
			/* User will be able to manage grid if he has permissions to manage nodes in general */
			$response->params['canManageGrid'] = XenForo_Application::get('options')->KomuKu_nodes_grid_frontend_enabled && XenForo_Visitor::getInstance()->hasAdminPermission('node');
			if(!empty($response->params['nodeList']))
			{
				self::NodesGrid_pushDataToNodes($response->params['nodeList']['parentNodeId'], $response->params['nodeList']['nodesGrouped'], array(
					'canManageGrid' => $response->params['canManageGrid']
				));
			}
		}
		return $response;
	}

	public static function NodesGrid_pushDataToNodes($parentNodeId, array &$nodesGrouped, array $data, $level = 1)
	{
		/* Level 3 and above are sub-forums which are not supported */
		if(!empty($nodesGrouped[$parentNodeId]) && $level <= 2)
		{
			/* Default position value */
			$prevPosition = 'full';

			foreach($nodesGrouped[$parentNodeId] AS &$node)
			{
				/**
				 * Proceed to next level on the node's tree.
				 */
				self::NodesGrid_pushDataToNodes($node['node_id'], $nodesGrouped, $data, $level+1);

				/**
				 * Add grid classes to data.
				 * For columns also add a class with the expected position of the column (left or right).
				 */
				$data['grid_class'] = self::NodesGrid_getGridPosition($node['grid_column'], $prevPosition);

				$node += $data;
			}
		}
	}

	/**
	 * Get the expected position of a grid column (full, left or right).
	 * Returns a string containing the required css classes.
	 */
	public static function NodesGrid_getGridPosition($isColumn, &$prevPosition)
	{
		if($isColumn)
		{
			if($prevPosition == 'column')
			{
				$prevPosition = 'full';
				return 'grid_column grid_right';
			}
			else
			{
				$prevPosition = 'column';
				return 'grid_column grid_left';
			}
		}
		else
		{
			$prevPosition = 'full';
			return 'grid_full';
		}
	}
}
?>