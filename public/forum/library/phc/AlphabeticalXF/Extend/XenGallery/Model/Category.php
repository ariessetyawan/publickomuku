<?php

class phc_AlphabeticalXF_Extend_XenGallery_Model_Category extends XFCP_phc_AlphabeticalXF_Extend_XenGallery_Model_Category
{
    public function getAllCategories()
    {
        if(!XenForo_Application::get('options')->alphaxf_gallery_categories)
            return parent::getAllCategories();

        return $this->fetchAllKeyed('
			SELECT *
			FROM xengallery_category
			ORDER BY category_title ASC
		', 'category_id');
    }
}
