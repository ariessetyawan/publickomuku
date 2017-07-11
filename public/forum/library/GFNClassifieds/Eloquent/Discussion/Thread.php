<?php /*a4c3d7a084d1dec6c65f506025e97c1cd2f13126*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Eloquent_Discussion_Thread extends GFNCore_Eloquent
{
    protected $_writerClass = 'XenForo_DataWriter_Discussion_Thread';

    protected $_errorHandler = XenForo_DataWriter::ERROR_SILENT;
}