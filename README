#Overview
This project is a simple plugin for Smarty to add a C style switch statement. To install it just drop it into your smarty plugins folder.

To install the plugin simply download it and save the compiler.switch.php file into your SmartyDir/libs/plugins folder.
Also you will need to manually load this plugin as there are more then one hook present to auto-loading may nor work all the time.  To do so just add the last line in the sample below to your site.


#Sample Code
```
// Load the Smarty plugin source code.
require("$SmartyDir/libs/Smarty.class.php");
// Create an istance of the smarty class.
$smartyObj = new Smarty();

// Other smarty configuration:

// Load the smarty switch plugin manually so that all the operations get loaded beforehand and to prevent some auto-load errors.
$smartyObj->loadPlugin('smarty_compiler_switch');
$smartyObj->registerFilter('post', 'smarty_postfilter_switch');
```