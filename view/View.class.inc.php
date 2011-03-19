<?php
/*
 *
 * Interface to be used by page layouts for grabbing the rss information for a page. included here because.
 *
 */

interface RSSView
{

  function getRSSLink();

  function getRSSName();
}

interface Printable
{

  function setPrintMode($bool);

  function getPrintMode();
}

/*
 * Displays a view.
 *
 * To display a view, called ->display()
 *
 * Before a page is displayed canDisplay() will be called.
 * If the view cannot be displayed, getAlternativeView is called and it's display method is execute.
 * If a view can be display, willDisplay is called, this is to give the view time to do whatever magic is required before output is sent to the browser.
 * The parts of the page are then called in the following order. getHead(), getTop(), getLeft(), getCenter(), getRight(), getBottom().
 *
 */

abstract class View implements RSSView
{

  protected $unique_id = 0;

  function getID()
  {
    return $this->unique_id;
  }

  function canDisplay()
  {
    return true;
  }

  abstract function getAlternativeView();

  function willDisplay()
  {
    
  }

  function didDisplay()
  {
    
  }

  function getRSSLink()
  {
    
  }

  function getRSSName()
  {
    
  }

  function allowsAds()
  {
    return true;
  }

  /*
   * The <head> of an html page.
   *
   */

  abstract function getHead();


  /*
   *
   * The top border of a page.
   */

  abstract function getTop();

  /*
   * The left border of a page.
   *
   */

  abstract function getLeft();

  /*
   * The right border of a page.
   *
   */

  abstract function getRight();

  /*
   * The bottom of the page.
   */

  abstract function getBottom();

  /*
   * The center content of a page. Should be unique for each page.
   *
   */

  abstract function getCenter();

  function getDocType()
  {
    return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
    //	return "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
  }

  function display()
  {
    if ($this->canDisplay())
    {
      $this->willDisplay();
      echo $this->getDocType() . "\n";
      ?><html><?
      $this->getHead();
      ?><body><?
      $this->getTop();
      $this->getLeft();
      $this->getCenter();
      $this->getRight();
      $this->getBottom();
      ?></body></html><?
      $this->didDisplay();
    }
    else
    {
      $this->getAlternativeView()->display();
    }
  }

}
?>