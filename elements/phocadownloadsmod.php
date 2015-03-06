<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

class JElementPhocaDownloadSMod extends JElement
{
	var	$_name = 'PhocaDownloadSMod';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$class 		= ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="inputbox"' );
		
		
		$db = &JFactory::getDBO();

		$query = ' SELECT s.id as value, s.title as text'
				.' FROM #__phocadownload_sections as s'
				.' WHERE s.published = 1 '
				.' GROUP BY s.id ';
		$db->setQuery( $query );
		
		$optionsS = $db->loadObjectList( );
		

		//array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select Category').' -', 'id', 'text'));

		//return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
		
		// Multiple
		$ctrl	= $control_name .'['. $name .']';
		$attribs	= ' ';
		if ($v = $node->attributes('size')) {
			$attribs	.= 'size="'.$v.'"';
		}
		if ($v = $node->attributes('class')) {
			$attribs	.= 'class="'.$v.'"';
		} else {
			$attribs	.= 'class="inputbox"';
		}
		if ($m = $node->attributes('multiple'))
		{
			$attribs	.= 'multiple="multiple"';
			$ctrl		.= '[]';
			//$value		= implode( '|', )
		}
		return JHTML::_('select.genericlist', $optionsS, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );
	}

}
