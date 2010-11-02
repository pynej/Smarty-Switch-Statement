<?php
/**
* Switch statement plugin for smarty.
*    This smarty plugin provides php switch statement functionality in smarty tags.
*    To install this plugin just drop it into your smarty plugins folder.
*
* @author Jeremy Pyne <jeremy.pyne@gmail.com>
* - Donations accepted via PayPal at the above address.
* - Updated: 03/08/2008 - Version 2
* - File: smarty/plugins/compiler.switch.php
* - Updates
*    Version 2:
*       Changed the break attribute to cause a break to be printed before the next case, instead of before this
*          case.  This way makes more sense and simplifies the code.  This change in incompatible with code in
*          from version one.  This is written to support nested switches and will work as expected.
*    Version 2.1:
*       Added {/case} tag, this is identical to {break}.
* - Bugs/Notes:
*       If you are using the short form, you must case condition before the break option.  In long hand this is
*          not necessary.
*
* @package Smarty
* @subpackage plugins
*
* Sample usage:
* <code>
* {foreach item=$debugItem from=$debugData}
*  // Switch on $debugItem.type
*    {switch $debugItem.type}
*       {case 1}
*       {case "invalid_field"}
*          // Case checks for string and numbers.
*       {/case}
*       {case $postError}
*       {case $getError|cat:"_ajax"|lower}
*          // Case checks can also use variables and modifiers.
*          {break}
*       {default}
*          // Default case is supported.
*    {/switch}
* {/foreach}
* </code>
*
* Note in the above example that the break statements work exactly as expected.  Also the switch and default
*    tags can take the break attribute. If set they will break automatically before the next case is printed.
*
* Both blocks produce the same switch logic:
* <code>
*    {case 1 break}
*       Code 1
*    {case 2}
*       Code 2
*    {default break}
*       Code 3
* </code>
*
* <code>
*    {case 1}
*     Code 1
*       {break}
*    {case 2}
*       Code 2
*    {default}
*       Code 3
*       {break}
* </code>
*
* Finally, there is an alternate long hand style for the switch statments that you may need to use in some cases.
*
* <code>
* {switch var=$type}
*    {case value="box" break=true}
*    {case value="line"}
*       {break}
*    {default}
* {/switch}
* </code>
*/

// Register the other smarty methods in this file.
// smarty_compiler_switch is automatically registered.
$this->register_compiler_function('case', 'smarty_compiler_case');
$this->register_compiler_function('default', 'smarty_compiler_default');
$this->register_compiler_function('break', 'smarty_compiler_break');
$this->register_compiler_function('/case', 'smarty_compiler_break');
$this->register_compiler_function('/switch', 'smarty_compiler_endswitch');
$this->register_postfilter('smarty_postfilter_switch');

/**
* Start a new switch statement.
*    A variable must be passed to switch on.
*  Also, the switch can only directly contain {case} and {default} tags.
*
* @param string $tag_arg
* @param Smarty_Compiler $smarty
* @return string
*/
function smarty_compiler_switch($tag_arg, &$smarty) {
 // Add var= if needed.
 if(strpos($tag_arg, 'var=') === false)
    $tag_arg = 'var='.$tag_arg;

 // Run the smarty code passed in.
 $_params = $smarty->_parse_attrs($tag_arg);

 // Make sure we have a var.
  if (!isset($_params['var'])) {
      $smarty->_syntax_error("switch: missing 'var' parameter", E_USER_WARNING);
      return;
  }

  // Get the switch data.
  $switchData =& $smarty->get_template_vars("_switchData");
  // Add a new switch data array.
  if(is_null($switchData)) {
     $switchData = array();
     $smarty->assign_by_ref("_switchData", $switchData);
  }
  // Turn auto-break off.
 array_unshift($switchData, false);

 // Return the switch.
 return "switch ({$_params['var']}) {";
}

/**
* Print out a case line for this switch.
*    A condition must be passed to match on.
*    This can only go in {switch} tags.
*    If break is passed, a {break} will be rendered before the next case.
*
* @param string $tag_arg
* @param Smarty_Compiler $smarty
* @return string
*/
function smarty_compiler_case($tag_arg, &$smarty) {
 // Add value= if needed.
 if(strpos($tag_arg, 'value=') === false)
    $tag_arg = 'value='.$tag_arg;

 // Change break to break=true
 $tag_arg = preg_replace('/ break$/', ' break=true', $tag_arg);

 // Run the smarty code passed in.
 $_params = $smarty->_parse_attrs($tag_arg);

 // Make sure we have a value.
 if (!isset($_params['value'])) {
    $smarty->_syntax_error("switch: missing 'value' parameter", E_USER_WARNING);
    return;
 }

 // Get the switch data, and fetch the current auto-break value.
 $switchData =& $smarty->get_template_vars("_switchData");
 $break =& $switchData[0];

 // If auto-break is on, break before the new case.
 $return = ($break ? 'break; ' :'') . "case {$_params['value']}:";

 // If the break attribute was passed, set the auto-break for the next case.
 $break = (array_key_exists('break', $_params) && $_params['break'] == 'true');

 return $return;
}

/**
* Print out a default line for this switch.
*    This can only go in {switch} tags.
*    If break is passed, a {break} will be rendered before the next case.
*
* @param string $tag_arg
* @param Smarty_Compiler $smarty
* @return string
*/
function smarty_compiler_default($tag_arg, &$smarty) {
 // Change break to break=true
 $tag_arg = preg_replace('/break$/', ' break=true', $tag_arg);

 // Run the smarty code passed in.
 $_params = $smarty->_parse_attrs($tag_arg);

 // Get the switch data, and fetch the current auto-break value.
 $switchData =& $smarty->get_template_vars("_switchData");
 $break =& $switchData[0];

 // If auto-break is on, break before the new case.
 $return = ($break ? 'break; ' :'') . 'default;';

 // If the break attribute was passed, set the auto-break for the next case.
 $break = (array_key_exists('break', $_params) && $_params['break'] == 'true');

 return $return;
}

/**
* Print out a break command for the switch.
*    This can only go inside of {case} tags.
*
* @param string $tag_arg
* @param Smarty_Compiler $smarty
* @return string
*/
function smarty_compiler_break($tag_arg, &$smarty) {
 return "break;";
}

/**
* End a switch statement.
*
* @param string $tag_arg
* @param Smarty_Compiler $smarty
* @return string
*/
function smarty_compiler_endswitch($tag_arg, &$smarty) {
 $switchData =& $smarty->get_template_vars("_switchData");
 array_shift($switchData);

 return "}";
}

/**
* Filter the template after it is generated to fix switch bugs.
*    Remove any spaces after the 'switch () {' code and before the first switch.  Any tabs or spaces
*       for layout would cause php errors witch this reged will fix.
*
* @param string $compiled
* @param Smarty_Compiler $smarty
* @return string
*/
function smarty_postfilter_switch($compiled, &$smarty) {
 // Remove the extra spaces after the start of the switch tag and before the first case statement.
 return preg_replace('/({ \?>)\s+(<\?php case)/', "$1\n$2", $compiled); }
?>

