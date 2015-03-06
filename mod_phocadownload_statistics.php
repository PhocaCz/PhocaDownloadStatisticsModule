<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @module Phoca - Phoca Module
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 
defined('_JEXEC') or die('Restricted access');// no direct access
if (!JComponentHelper::isEnabled('com_phocadownload', true)) {
	return JError::raiseError(JText::_('Phoca Download Error'), JText::_('Phoca Download is not installed on your system'));
}
require_once( JPATH_BASE.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocadownload.php' );
require_once( JPATH_BASE.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'route.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocadownload.php' );

$user 		=& JFactory::getUser();
$aid 		= $user->get('aid', 0);	
$db 		=& JFactory::getDBO();
$menu 		=& JSite::getMenu();
$document	=& JFactory::getDocument();


// PARAMS 
$module_width 		= $params->get( 'module_width', 400 );
$font_size 			= $params->get( 'font_size', 10 );
$display_downloads 	= $params->get( 'display_downloads', 1 );
$display_cat_sec 	= $params->get( 'display_cat_sec', 1 );
$display_title 		= $params->get( 'display_title', 1 );
$display_filename 	= $params->get( 'display_filename', 1 );
$number_item	 	= $params->get( 'number_item',6 );
$displayS		 	= $params->get( 'section_id','' );


// Max Hit
$wheres		= array();	
$wheres[] 	= ' a.published = 1';
$wheres[] 	= ' s.published = 1';
$wheres[] 	= ' cc.published = 1';
$wheres[] 	= ' a.textonly = 0';

// Active
$jnow		=& JFactory::getDate();
$now		= $jnow->toMySQL();
$nullDate	= $db->getNullDate();
$wheres[] = ' ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )';
$wheres[] = ' ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )';

// SQL, QUERY
if (count($displayS) > 1) {
	JArrayHelper::toInteger($displayS);
	$displaySString	= implode(',', $displayS);
	$wheres[]	= ' a.id IN ( '.$displaySString.' ) ';
} else if ((int)$displayS > 0) {
	$wheres[]	= ' a.id IN ( '.$displayS.' ) ';
}

$where		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
$query = ' SELECT MAX(a.hits) AS maxhit '
	. ' FROM #__phocadownload AS a '
	. ' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = a.catid '
	. ' LEFT JOIN #__phocadownload_sections AS s ON s.id = a.sectionid '
	. ' LEFT JOIN #__groups AS g ON g.id = a.access '
	. $where;
$db->setQuery( $query );
$maxHit = $db->loadObjectList();


// Items
$wheres		= array();	
$wheres[] 	= ' a.published = 1';
$wheres[] 	= ' s.published = 1';
$wheres[] 	= ' cc.published = 1';
$wheres[] 	= ' a.textonly = 0';

// Active
$jnow		=& JFactory::getDate();
$now		= $jnow->toMySQL();
$nullDate	= $db->getNullDate();
$wheres[] = ' ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )';
$wheres[] = ' ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )';

if (count($displayS) > 1) {
	JArrayHelper::toInteger($displayS);
	$displaySString	= implode(',', $displayS);
	$wheres[]	= ' s.id IN ( '.$displaySString.' ) ';
} else if ((int)$displayS > 0) {
	$wheres[]	= ' s.id IN ( '.$displayS.' ) ';
}

$where		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
$orderby	= ' ORDER by a.hits DESC';
$limit		= ' LIMIT 0,'.(int)$number_item;
$query = ' SELECT a.*, cc.id as categoryid, cc.title AS categorytitle, cc.alias as categoryalias, s.id as sectionid, s.title AS sectiontitle, cc.access as cataccess, cc.accessuserid as cataccessuserid '
	. ' FROM #__phocadownload AS a '
	. ' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = a.catid '
	. ' LEFT JOIN #__phocadownload_sections AS s ON s.id = a.sectionid '
	. ' LEFT JOIN #__groups AS g ON g.id = a.access '
	. ' LEFT JOIN #__users AS u ON u.id = a.checked_out '
	. $where
	. ' GROUP by a.id'
	. $orderby
	. $limit;

$db->setQuery( $query );
$items = $db->loadObjectList();

// DISPLAY
$module_width_div = (int)$module_width - 10;
$output = '<div class="phoca-dl-statistics-box-module">';
$output .= '<table width="'.$module_width.'">';

if (!empty($items)) {
	$color 	= 0;
	$i		= 1;
	foreach ($items as $value) {
	
		// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
		// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
		$rightDisplay	= 0;
		if (!empty($value)) {
			$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $value->cataccessuserid, $value->cataccess, $user->get('aid', 0), $user->get('id', 0), 0);
		}
		// - - - - - - - - - - - - - - - - - - - - - -
		
		if ($rightDisplay == 1) {
		
	
			$colors = array('#FFE6BF', '#FFECBF', '#FFF2BF', '#FFF9BF', '#FFFFBF', '#F2FFBF', '#E6FFBF', '#CCFFBF', '#BFFFBF', '#BFFFE4', '#BFFFFF', '#BFE4FF', '#BFCFFF', '#C8C8FF', '#D5BFFF', '#DABFFF', '#EABFFF', '#FFBFFF', '#FFBFEF', '#FFBFDC', '#FFBFBF', '#FFCCBF', '#FFD9BF', '#FFDFBF');
			
			if ((int)$maxHit[0]->maxhit == 0) {
				$per = 0;
			} else {
				$per = round((int)$value->hits / (int)$maxHit[0]->maxhit * (int)$module_width);
			}
			
			// Only text (description - no file)
			if ($value->textonly == 0) {
			
				$link	= JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($value->categoryid, $value->categoryalias, $value->sectionid));
		
				$output .= '<tr>';
				$output .= '<td align="right"><span style="font-size:'.$font_size.'">'. $i .'. </span></td>';
				$output .= '<td>';
				$output .= '<div style="background:'.$colors[$color].' url(\''. JURI::base(true).'/components/com_phocadownload/assets/images/white-space.png'.'\') '.$per.'px 0px no-repeat;width:'.$module_width_div.'px;padding:5px 5px;margin:5px 0px;font-size:'.$font_size.'px">';
			//	echo '<small style="color:#666666">['. $value->id .']</small>';
				
				if ((int)$display_title == 1) {
					$output .= '<strong  style="color:#666666;"><a href="'.$link.'">'.$value->title .'</a></strong>';
				}
				
				if ((int)$display_filename == 1) {
					if ((int)$display_title == 1) {
						$output .= ' - ';
					}
					
				
					$output .= '<em><a href="'.$link.'">'. $value->filename .'</a></em>';
				}
				
				if ((int)$display_cat_sec == 1) {
					$output .= ' <small style="color:#666666">('. $value->sectiontitle .'/'. $value->categorytitle .')</small>';
				}
				$output .= '</div>';
				$output .= '</td>';
				
				if ((int)$display_downloads == 1) {
					$output .= '<td align="center">'. $value->hits .'</td>';
				}
				$output .= '</tr>';
			
				$color++;
				$i++;
				if ($color > 23) {
					$color = 0;
				}
			}
		}
	}
}

$output .= '</table></div>';

require(JModuleHelper::getLayoutPath('mod_phocadownload_statistics'));
?>