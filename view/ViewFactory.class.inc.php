<?php
/*
 * 	A view factory can create links to the views it manages.
 *
 * 	each view has a static identifier.
 * 
 */

abstract class ViewFactory
{
	abstract function createLinkToView($view_id);
}

abstract class ViewLink
{
	/*
	 * Created the minimum link necessary to href from $link to this view link.
	 */
	abstract function href(ViewLink $link);
}
?>