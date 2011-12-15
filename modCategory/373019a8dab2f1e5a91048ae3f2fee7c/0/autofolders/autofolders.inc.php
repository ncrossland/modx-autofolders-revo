<?php

//$modx->fire->log('Plain Message');
//$modx->fire->log('Plain message with label', 'Label');
//$modx->fire->info('Info Message');
//$modx->fire->warn('Warn Message');
//$modx->fire->error('Error Message');



// Get the current event
$e = &$modx->event;
$r = &$e->params['resource'];

// Get the plugin options, setting defaults if they're not available
$template = $modx->getOption('template', $scriptProperties, '');
$new_page_template = $modx->getOption('new_page_template', $scriptProperties, '');
$parent = $modx->getOption('parent', $scriptProperties, '');


// Those fields are required
if (empty($parent) || empty($template) || empty($new_page_template)) {
	return false;	
}

$folder_structure = $modx->getOption('folder_structure', $scriptProperties, 'y/m');
$date_field = $modx->getOption('date_field', $scriptProperties, 'publishedon');

$alias_year_format = $modx->getOption('alias_year_format', $scriptProperties, '4');
$alias_month_format = $modx->getOption('alias_month_format', $scriptProperties, '1');
$alias_day_format = $modx->getOption('alias_day_format', $scriptProperties, '1');
$title_year_format = $modx->getOption('title_year_format', $scriptProperties, '4');
$title_month_format = $modx->getOption('title_month_format', $scriptProperties, '1');
$title_day_format = $modx->getOption('title_day_format', $scriptProperties, '1');



// Is the document we are creating using a template we have been asked to target?
$tpls = explode(',', $template);
$tpls = array_map("trim", $tpls);

// If it's not a template we're targetting, do nothing
if (!in_array($r->get('template'), $tpls)) {
	return false;
}


// Prevent errors in E_STRICT
date_default_timezone_set( @date_default_timezone_get() );


// These are ModX's built in date fields. These are really easy to spot
$modx_builtin_dates = array('pub_date', 'unpub_date', 'createdon', 'editedon', 'deletedon', 'publishedon');

// If it's one of these, we now know our date / time value
if (in_array($date_field, $modx_builtin_dates)) {
	$the_date = $r->get($date_field);
} else {
	// If it's a TV
	$the_date = $r->getTVValue($date_field);
}

// Parse the date string
$dt = strtotime($the_date);

// If there is no date value found yet, give up
if ($dt === false || $dt === -1) { // If date can't be parsed, it returns false (PHP5.1) or -1 (<PHP5.1)
 	$modx->log(modX::LOG_LEVEL_ERROR, "Could not parse a valid date from the date field ($date_field)");
	return;
}





// A function to format a date, as specified in the plugin options
if (!function_exists('getFormattedDate') ) {
	function getFormattedDate($dt, $part, $format) {		
	
		// $dt = datetime 
		// Part should be y, m or d
		// format should be the format ID from the config dropdown
		
		switch ($part) {
		
			case 'y':
			
				switch ($format) {
					case '4':
					case 'menuindex':
						return strftime("%Y", $dt);
					break;
					case '2':
						return strftime("%y", $dt);
					break;		
					default:
						return false;
					break;
				}
			
			break;
			
			
			case 'm':
			
				switch ($format) {					
					case '1':
					case 'menuindex':
						return intval(strftime("%m", $dt));
					break;	
					case '2':
						return strftime("%m", $dt);
					break;	
					case '3':
						return strftime("%B", $dt);
					break;
					case '4':
						return strftime("%b", $dt);
					break;
					default:
						return false;
					break;	
				}
			
			break;
			
			case 'd':
			
				switch ($format) {					
					case '1':
					case 'menuindex':
						return trim(strftime("%e", $dt));
					break;
					case '2':
						return strftime("%d", $dt);
					break;	
					default:
						return false;
					break;	
				}
			
			break;			
			
			default:
				return false;
			break;
			
		}
		
	}
}


// What are the formats specified?
$aliases['y'] = getFormattedDate($dt,  'y', $alias_year_format);
$aliases['m'] = getFormattedDate($dt,  'm', $alias_month_format);
$aliases['d'] = getFormattedDate($dt,  'd', $alias_day_format);
$titles['y'] = getFormattedDate($dt,  'y', $title_year_format);
$titles['m'] = getFormattedDate($dt,  'm', $title_month_format);
$titles['d'] = getFormattedDate($dt,  'd', $title_day_format);


// Explode the folder format
$folders = explode('/', $folder_structure);

// Where do we start looking for folders?
$last_parent = intval($parent);



// Go through each of the folder structure items...
foreach ($folders as $i=>$f) {
	
	
	//... and check if the required folder exists
	$theFolderExists = false;
	
	// Get all the child resources
	$this_folder_children = $modx->getCollection('modResource', array( 'parent' => $last_parent ));
	
		
	// Go through the children, and see if any of them have the alias we want
	foreach ($this_folder_children as $child) {
		
		if ($child->get('alias') == $aliases[$f]) {
			$theFolderExists = true;
			$last_parent = $child->get('id');
		}
	}
	
	
	// If we haven't found the folder, create it
	if (!$theFolderExists) {
		
		// Make sure the parent folder is a container
		$doc = $modx->getObject('modResource',array('id'=>$last_parent));
		$doc->set('isfolder', '1');
		$doc->save();
		
		// Generate a new title
		switch ($f) {
			case 'y':
				$new_title = $titles['y'];
			break;	
			case 'm':
				$new_title = $titles['m'] . ' ' . $titles['y'];
			break;	
			case 'd':
				$new_title = $titles['d'] . ' ' . $titles['m'] . ' ' . $titles['y'];
			break;	
			default:
				$new_title = '';
			break;
		}
		
		// Duplicate the parent
		$newdoc = $doc->duplicate( array(
			'newName' => $new_title,
			'parent' => $last_parent,
			'duplicateChildren' => false
			));
			
		$newdoc->set('isfolder', '1');	
		$newdoc->set('published', '1');
		$newdoc->set('template', $new_page_template);
		$newdoc->set('longtitle', $new_title);	
		$newdoc->set('menutitle', $titles[$f]);	
		$newdoc->set('alias', $aliases[$f]);
		$newdoc->set('menuindex', getFormattedDate($dt,  $f, 'menuindex'));
		$newdoc->save();
		
		$last_parent = $newdoc->get('id');
		
	}	
}




// Work out the menu index of the new page by grabbing the last part of the folder structure, and basing it off that
switch ($folders[count($folders)-1]) {
	case 'y':
		$menuindex = $aliases['m'];
	break;	
	case 'm':
		$menuindex = $aliases['d'];
	break;	
	case 'd':
		$menuindex = $aliases['d'] . strftime("%H%M", $dt);
	break;	
}


$r->set('menuindex', $menuindex );
$r->set('parent', $last_parent );
$r->save();