<?php
class MySqliError
{

	private $number;
	private $message;
	private $query;

	function __construct($number, $error, $query=null)
	{
		$this->number = $number;
		$this->error = $error;
		$this->query = $query;
		Debugger::error(new Exception((string) $this));
	}

	function command()
	{
		return $this->query;
	}

	function number()
	{
		return $this->number;
	}

	function error()
	{
		return $this->error;
	}

	function description()
	{
		$description = "({$this->number})" . $this->error;
		if ($this->query)
			$description .= " Occurred while attempting: {$this->query}";

		return $description;
	}

	public function __tostring()
	{
		return "(" . $this->number . ") " . $this->error;
	}

}
?>