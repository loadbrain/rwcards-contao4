<?php
if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2011 Ralf Weber
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  LoadBrain 2011
 * @author     Ralf Weber <http://www.loadbrain.de>
 * @package    Comments
 * @license    LGPL
 * @filesource
 */


/**
 * Class OneCategoryRwCards
 *
 * @copyright  LoadBrain 2011
 * @author     Ralf Weber <http://www.loadbrain.de>
 * @package    Controller
 */
class FillOutCardRwCards extends Frontend
{

	/**
	 * Add all cards of one category to a template
	 * @param object
	 * @param object
	 * @param string
	 * @param integer
	 */
	public function addToTemplate($objTemplate, $objConfig, $strSource, $intParent, $alias)
    {
		$this->import("Session");
		$objPage = new stdClass();
		$objPage = $this->Database->prepare('SELECT id, alias FROM tl_page WHERE id=?')->execute($intParent);
		$this->nextPage = $objPage->fetchAssoc();

		$limit = null;
		$this->sessionId = (\Input::get('sessionId') != "" ) ? \Input::get('sessionId') : false;
		$this->category_id = (\Input::get('category_id') > 0 ) ? \Input::get('category_id') : false;
		$this->card_id = (\Input::get('id') > 0 ) ? \Input::get('id') : false;

		// New Card or someone answers
		if ( $this->sessionId != "")
		{
			$resCats = $this->Database->prepare("select tl_rwcardsdata.*, tl_rwcards.*, tl_files.path from tl_rwcardsdata, tl_rwcards left join tl_files on tl_files.uuid =  tl_rwcards.picture where tl_rwcards.id = '" . $this->card_id . "' and tl_rwcardsdata.sessionId = '" . $this->sessionId ."'");
			$this->data = $resCats->execute()->fetchAllAssoc();
		} else {
			$resCats = $this->Database->prepare("select tl_rwcards.*, tl_files.path from tl_rwcards left join tl_files on tl_files.uuid =  tl_rwcards.picture where tl_rwcards.id = '" . $this->card_id . "'");
			$this->data = $resCats->execute()->fetchAllAssoc();
		}

		if (count($this->data) > 0 && $objConfig->template == '')
		{
		    $objTemplate->template = 'rwcards_filloutcard';
		}

		/**
		 * set some vars
		 */
		$GLOBALS['TL_CSS'][''] = \Environment::get('base') . "system/modules/rwcards/assets/css/rwcards.filloutform.css";
		$objTemplate->data = $this->data;
		$objTemplate->rwcards_fillout_cards_please_fill_out = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_please_fill_out'] ;
		$objTemplate->rwcards_fillout_cards_name_to = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_name_to'];
		$objTemplate->rwcards_fillout_cards_email_to = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_email_to'];
		$objTemplate->rwcards_fillout_cards_add_receiver = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_add_receiver'];
		$objTemplate->rwcards_fillout_cards_remove_receiver = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_remove_receiver'];
		$objTemplate->rwcards_fillout_cards_back = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_back'];
		$objTemplate->rwcards_fillout_cards_preview_card = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_preview_card'];
		$objTemplate->rwcards_fillout_cards_send_card = $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_send_card'];

		$objTemplate->reWritetoSender = (\Input::get('reWritetoSender')) ? 1 : 0;
		$objTemplate->category_id = $this->category_id;
		if($this->view != "rwconecategory"){
			$objTemplate->sessionId = "";
		}
		$objTemplate->alias = $alias;
		$objTemplate->formId = 'rwcards_fillout_form';
		$objTemplate->action = ampersand($this->Environment->request);
		$objTemplate->nextPage = $this->nextPage;

		// Form fields
		$arrFields = array
		(
			'rwNameFrom' => array
			(
				'name' => 'rwNameFrom',
				'label' => $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_name_from'],
				'value' => ($this->Session->get('rwNameFrom') != "") ? trim($this->Session->get('rwNameFrom')) : trim($this->data[0]['nameTo']),
				'inputType' => 'text',
				'eval' => array('mandatory'=>true, 'maxlength'=>64)
			),
			'rwEmailFrom' => array
			(
				'name' => 'rwEmailFrom',
				'label' => $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_email_from'],
				'value' => ($this->Session->get('rwEmailFrom') != "") ? trim($this->Session->get('rwEmailFrom')) : trim($this->data[0]['emailTo']),
				'inputType' => 'text',
				'eval' => array('rgxp'=>'email', 'mandatory'=>true, 'maxlength'=>128, 'decodeEntities'=>true)
			),
			'rwMessage' => array
			(
				'name' => 'rwMessage',
				'id' => 'rwCardsFormMessage',
				'label' =>  $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_message'],
      			'inputType'    => 'textarea',
      			'eval'  => array('rte'=>'tinyFlash', 'decodeEntities'=>true, 'mandatory'=>true,),
				'value' => $this->Session->get('rwMessage')
			),
			'rwcardsReceiver' => array
			(
				'name' => 'rwcardsReceiver',
				'label' => $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_name_to'],
				'value' => ($this->Session->get('rwcardsReceiver') != "") ? trim($this->Session->get('rwcardsReceiver')): trim($this->data[0]['nameFrom']),
				'inputType' => 'text',
				'eval' => array('mandatory'=>true, 'maxlength'=>64)
			),
			'rwCardsFormEmailTo' => array
			(
				'name' => 'rwCardsFormEmailTo',
				'label' => $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_email_to'],
				'value' => ($this->Session->get('rwCardsFormEmailTo') != "") ? $this->Session->get('rwCardsFormEmailTo'): trim($this->data[0]['emailFrom']),
				'inputType' => 'text',
				'eval' => array('rgxp'=>'email', 'mandatory'=>true, 'maxlength'=>128, 'decodeEntities'=>true)
			),
			'rwCardsCaptcha' => array
			(
				'name' => 'rwCardsCaptcha',
				'inputType' => 'captcha',
				'eval' => array('mandatory'=>true)
			)
		);

		$doNotSubmit = false;
		$arrWidgets = array();

		$objTemplate->hasError = $doNotSubmit;

		// Initialize widgets
		foreach ($arrFields as $arrField)
		{
			$strClass = $GLOBALS['TL_FFL'][$arrField['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$arrField['eval']['required'] = $arrField['eval']['mandatory'];
			$objWidget = new $strClass($this->prepareForWidget($arrField, $arrField['name'], $arrField['value']));

			// Validate the widget
			if (\Input::post('FORM_SUBMIT') == 'rwcards_fillout_form')
			{
				$objWidget->validate();

				if ($objWidget->hasErrors())
				{
					$doNotSubmit = true;
				}
			}

			$arrWidgets[$arrField['name']] = $objWidget;
		}

		$objTemplate->fields  = $arrWidgets;

		if (\Input::post('FORM_SUBMIT') == $objTemplate->formId && !$doNotSubmit)
		{
			foreach ( $_POST as $key=>$value )
			{
			    $this->Session->set($key, \Input::post(($key)));
			}

			// Preview button clicked
			if(\Input::post('submit') == $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_preview_card'] )
			{
				$this->redirect(\Controller::generateFrontendUrl($objPage->row()) . '?view=rwcardspreview&id=' . $this->data[0]['id'] .'&category_id=' . $this->category_id . '&reWritetoSender=' . $objTemplate->reWritetoSender . '&sessionId=' . @$this->sessionId);
			}
			// Sending button clicked
			if(\Input::post('submit') == $GLOBALS['TL_LANG']['tl_rwcards']['rwcards_fillout_cards_send_card'] )
			{
				$this->redirect(\Controller::generateFrontendUrl($objPage->row()) . '?view=rwcardssendcard&id=' . $this->data[0]['id'] .'&category_id=' . $this->category_id);
			}
		}
	}
}
