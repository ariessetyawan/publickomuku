<?php

/**
 *
 * @see XenForo_Model_Node
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Model_Node extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Model_Node
{

    /**
     *
     * @see XenForo_Model_Node::getPossibleParentNodes()
     */
    public function getPossibleParentNodes($node = null)
    {
        $nodes = parent::getPossibleParentNodes($node);

        foreach ($nodes as &$node) {
            $node['title'] = strip_tags($node['title']);
        }

        return $nodes;
    } /* END getPossibleParentNodes */

    /**
     *
     * @see XenForo_Model_Node::getNodeBreadCrumbs()
     */
    public function getNodeBreadCrumbs(array $node, $includeSelf = true)
    {
        $breadCrumbs = parent::getNodeBreadCrumbs($node, $includeSelf);

        foreach ($breadCrumbs as &$breadCrumb) {
            $breadCrumb['value'] = strip_tags($breadCrumb['value']);
        }

        return $breadCrumbs;
    } /* END getNodeBreadCrumbs */

    public function getViewableNodeList(array $nodePermissions = null, $listView = false)
    {
        $nodes = parent::getViewableNodeList($nodePermissions, $listView);

        foreach ($nodes as &$node) {
            $node['title'] = strip_tags($node['title']);
        }

        return $nodes;
    } /* END getViewableNodeList */

    public function getAllNodes($ignoreNestedSetOrdering = false, $listView = false)
    {
        $nodes = parent::getAllNodes($ignoreNestedSetOrdering, $listView);

        if (!$listView) {
            foreach ($nodes as &$node) {
                $node['title'] = strip_tags($node['title']);
            }
        }

        return $nodes;
    } /* END getAllNodes */
}