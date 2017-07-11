<?php

class KomuKu_SimpleForms_Install_21100 extends KomuKu_SimpleForms_Install_Abstract
{
	public function install(&$db)
	{
		// convert timestamps to dates
		$forms = $db->fetchAll('SELECT * FROM `kmkform__form` WHERE `start_date` IS NOT NULL OR `end_date` IS NOT NULL');
		foreach ($forms as $form)
		{
			$data = array();
			
			$startDate = @unserialize($form['start_date']);
			if ($startDate === false)
			{
				if ($form['start_date'] > 0)
				{
					$startDate = array();
					$datetime = new DateTime('@' . $form['start_date']);
					$startDate['enabled'] = 'start';
					$startDate['ymd'] = $datetime->format('Y-m-d');
					$startDate['hh'] = '00';
					$startDate['mm'] = '00';
					$startDate['user_tz'] = 1;
					$startDate['timezone'] = '';
				}
				else
				{
					$startDate = array();
					$startDate['enabled'] = '';
					$startDate['ymd'] = '';
					$startDate['hh'] = '';
					$startDate['mm'] = '';
					$startDate['user_tz'] = 0;
					$startDate['timezone'] = '';
				}
				
				$data['start_date'] = serialize($startDate);
			}
				
			$endDate = @unserialize($form['end_date']);
			if ($endDate === false)
			{
				if ($form['end_date'] > 0)
				{
					$endDate = array();
					$datetime = new DateTime('@' . $form['end_date']);
					$endDate['enabled'] = 'end';
					$endDate['ymd'] = $datetime->format('Y-m-d');
					$endDate['hh'] = '00';
					$endDate['mm'] = '00';
					$endDate['user_tz'] = 1;
					$endDate['timezone'] = '';
				}
				else
				{
					$endDate = array();
					$endDate['enabled'] = '';
					$endDate['ymd'] = '';
					$endDate['hh'] = '';
					$endDate['mm'] = '';
					$endDate['user_tz'] = 0;
					$endDate['timezone'] = '';
				}
				
				$data['end_date'] = serialize($endDate);
			}

			if ($data !== array())
			{
				$db->update('kmkform__form',
					$data,
					$db->quoteInto('form_id = ?', $form['form_id'])
				);
			}
		}
		
		return true;
	}
}