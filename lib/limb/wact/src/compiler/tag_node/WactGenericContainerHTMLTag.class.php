<?php
/**
 * Limb Web Application Framework
 *
 * @link http://limb-project.com
 *
 * @copyright  Copyright &copy; 2004-2007 BIT
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 * @version    $Id: WactGenericContainerHTMLTag.class.php 5021 2007-02-12 13:04:07Z pachanga $
 * @package    wact
 */

/**
* Compile time component for tags in template which are not
* recognized WACT tags but have a runat="server" attribute.
* This allows native HTML tags, for example, to be manipulated
* at runtime.
* WactGenericContainerHTMLTag is for tags with children.
*/
class WactGenericContainerHTMLTag extends WactRuntimeComponentHTMLTag
{
  protected $runtimeIncludeFile;
  protected $runtimeComponentName = 'WactRuntimeTagComponent';

  function postGenerate($code_writer)
  {
    $code_writer->writePHP($this->getComponentRefCode() . '->render();');

    parent::postGenerate($code_writer);
  }
}

?>