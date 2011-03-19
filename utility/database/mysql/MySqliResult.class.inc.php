<?php

class MySqliResult
{

  private $result;
  public static $FETCH_ARRAY = 0;
  public static $FETCH_ASSOC = 1;
  //public static $FETCH_FIELD_DIRECT = 2;
  //public static $FETCH_FIELD = 3;
  public static $FETCH_FIELDS = 4;
  public static $FETCH_LENGTHS = 5;
  public static $FETCH_OBJECT = 6;
  public static $FETCH_ROW = 7;
  //public static $FETCH = 8;
  private $fetch_type = null;

  //Create a result. Set the default fetch type. (currently fetch_object)
  function __construct($result)
  {
    $this->result = $result;
    $this->fetch_type = MySqliResult::$FETCH_OBJECT;
  }

  //Close the result.
  function __destruct()
  {
    $this->result->close();
  }

  //Returns the type of fetch being used. The default fetch type is fetch_object
  function fetch_type()
  {
    return $this->fetch_type;
  }

  //Sets the fetch type.
  function set_fetch_type($fetch)
  {
    $this->fetch_type = $fetch;
  }

  //The number of rows in the table.
  function rows()
  {
    return $this->result->num_rows;
  }

  //Fetches the next row using the stored fetch type and returns the result.
  function fetch_next($class=null)
  {

    $fetch = $this->fetch_type;

    $result = $this->result;


    switch ($fetch)
    {
      case (MySqliResult::$FETCH_OBJECT):
        if ($class != null)
          $row = $result->fetch_object($class, null); //mysqli_fetch_object($result,$class);
        else
          $row= $result->fetch_object(); //mysqli_fetch_object($result);
        break;
      case (MySqliResult::$FETCH_ARRAY):
        $row = mysqli_fetch_array($result);
        break;
      case (MySqliResult::$FETCH_FIELDS):
        $row = mysqli_fetch_fields($result);
        break;
      case (MySqliResult::$FETCH_LENGTHS):
        $row = mysqli_fetch_fields($result);
        break;
      case (MySqliResult::$FETCH_ROW):
        $row = mysqli_fetch_row($result);
        break;
      case (MySqliResult::$FETCH_ASSOC):
        $row = mysqli_fetch_assoc($result);
        break;
    }

    return $row;
  }

  //Fetches the next row, applies the supplied function to it, passing the arguments as paramaters with the fetch result appened to the front.
  function fetch_next_map($function, $arguments=null)
  {

    $row = $this->fetch_next();
    if ($row)
    {
      if ($arguments == null)
        $arguments = array();
      array_unshift($arguments, $row);
      return call_user_func_array($function, $arguments);
    }
    else
      return false;
  }

  function fetch_next_process($function, $array=null)
  {
    return $this->fetch_next_map($function, $array);
  }

  //Fetches and returns all the rows.
  function fetch_all($class=null)
  {
    $array = array();
    while (($row = $this->fetch_next($class)))
      $array[] = $row;

    return $array;
  }

  function fetch_all_map($function, $arguments=null)
  {
    $processed = array();
    while (($result = $this->fetch_next_map($function, $arguments)))
    {
      $processed[] = $result;
    }
    return $processed;
  }

  //Fetches each row in order, applies the supplied function to it, passing the array as paramaters with the fetch result append to the front.
  //Note: If $function returns false for any reason, this function will stop and return the results gathered up to that point, excluding the final call that returned false.
  function fetch_all_process($function, $array=null)
  {
    return $this->fetch_all_map($function, $array); //$arguments);
  }

}

?>