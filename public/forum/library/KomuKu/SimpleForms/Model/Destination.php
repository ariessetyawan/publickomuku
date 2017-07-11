<?php

class KomuKu_SimpleForms_Model_Destination extends XenForo_Model
{
	/**
	 * Gets a destinations by ID.
	 *
	 * @param string $destinationId
	 *
	 * @return array|false
	 */
	public function getDestinationById($destinationId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM kmkform__destination
			WHERE destination_id = ?
		', $destinationId);
	}
	
	/**
	 * Gets a destinations by Form ID.
	 *
	 * @param string $formId
	 *
	 * @return array|false
	 */
	public function getDestinationsByFormId($formId)
	{
		return $this->fetchAllKeyed('
			SELECT kmkform__destination.*, `kmkform__form_destination`.*, kmkform__destination.name AS destination_name
			FROM `kmkform__form_destination`
			JOIN `kmkform__destination`
			  ON `kmkform__destination`.`destination_id` = `kmkform__form_destination`.`destination_id`
			WHERE `kmkform__form_destination`.`form_id` = ?
			', 'form_destination_id', $formId);
	}	
	
	/**
	 * Gets a destination by Form Destination ID
	 * 
	 * @param integer $formDestinationId
	 * 
	 * @return array|false
	 */
	public function getDestinationByFormDestinationId($formDestinationId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM kmkform__destination
			WHERE destination_id = (
				SELECT
					destination_id
				FROM
					kmkform__form_destination
				WHERE
					form_destination_id = ?
			)
			', $formDestinationId);		
	}

	/**
	 * Gets destinations that match the specified criteria.
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return array [destination id] => info
	 */
	public function getDestinations()
	{
		return $this->fetchAllKeyed('
			SELECT kmkform__destination.*
			FROM kmkform__destination
			ORDER BY kmkform__destination.display_order
		', 'destination_id');
	}
	
	/**
	 * Gets destinations that have a redirect method.
	 * 
	 * @return array [destination id] => info
	 */
	public function getDestinationsWithRedirect()
	{
		return $this->fetchAllKeyed('
			SELECT destination.*
			FROM kmkform__destination destination
			WHERE redirect_method != \'\'
			ORDER BY destination.display_order
		', 'destination_id');
	}
	
	/**
	 * Prepares a destination for display.
	 *
	 * @param array $destination
	 * @param boolean $getFieldChoices If true, gets the choice options for this field (as phrases)
	 * @param mixed $fieldValue If not null, the value for the field; if null, pulled from field_value
	 *
	 * @return array Prepared destination
	 */
	public function prepareDestination(array $destination)
	{
		$destination['name'] = new XenForo_Phrase($this->getDestinationNamePhraseName($destination['destination_id']));

		return $destination;
	}	
	
	/**
	 * Prepares a list of destinations for display.
	 *
	 * @param array $destinations
	 *
	 * @return array
	 */
	public function prepareDestinations(array $destinations)
	{
		foreach ($destinations AS &$destination)
		{
			$destination = $this->prepareDestination($destination);
		}

		return $destinations;
	}	
	
	/**
	 * Gets the destination's name phrase name.
	 *
	 * @param string $destinationId
	 *
	 * @return string
	 */
	public function getDestinationNamePhraseName($destinationId)
	{
		return 'destination_' . $destinationId;
	}	
	
	/**
	 * Gets a destination's master name phrase text.
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function getDestinationMasterNamePhraseValue($id)
	{
		$phraseName = $this->getDestinationNamePhraseName($id);
		return $this->_getPhraseModel()->getMasterPhraseValue($phraseName);
	}
	
	/**
	 * Rebuilds the cache of destination info for front-end display
	 *
	 * @return array
	 */
	public function rebuildDestinationCache()
	{
		$cache = array();
		foreach ($this->getDestinations() AS $destinationId => $destination)
		{
			$cache[$destinationId] = XenForo_Application::arrayFilterKeys($destination, array(
				'destination_id',
				'name'
			));
		}

		$this->_getDataRegistryModel()->set('destinationsInfo', $cache);
		return $cache;
	}	
	
	/**
	 * @return XenForo_Model_Phrase
	 */
	protected function _getPhraseModel()
	{
		return $this->getModelFromCache('XenForo_Model_Phrase');
	}	
}