<?php
Loader::load("utility", "email/IMAPEmail");

class IMAPStream
{

	private $imap_stream;
	private $current_message_index;
	private $total_messages;

	function __construct($host, $user, $password, $port, $flags, $mailbox)
	{
		$this->total_messages = false;
		$this->current_message_index = false;
		$portstring = "";
		if ($port)
			$portstring = ":" . $port;
		$flagstring = "";
		foreach ($flags as $flag)
			$flagstring .= "/$flag";
		$this->imap_stream = imap_open("{" . $host . $portstring . $flagstring . "}" . $mailbox, $user, $password);
		if ($this->imap_stream)
		{
			$this->current_message_index = 0;
			$this->total_messages = imap_num_msg($this->imap_stream);
		}
	}

	function isConnected()
	{
		return ($this->imap_stream) ? true : false;
	}

	function getLastError()
	{
		return imap_last_error();
	}

	function closeConnection()
	{
		imap_expunge($this->imap_stream);
		imap_close($this->imap_stream);
	}

	function getCurrentEmail()
	{
		if ($this->total_messages && $this->current_message_index <= $this->total_messages)
		{
			$email = new IMAPEmail($this->imap_stream, $this->current_message_index);
			return $email;
		}
		return false;
	}

	function getNextEmail()
	{
		if ($this->total_messages && $this->current_message_index < $this->total_messages)
		{
			$this->current_message_index++; //This happens before loading so first index is 1;
			$email = new IMAPEmail($this->imap_stream, $this->current_message_index);
			return $email;
		}
		return false;
	}

	static function in_exclude_email_list($email)
	{
		$email_exclude_from_import_list = array(
		    'mailer-daemon@htmlgraphic.com'
		);
		foreach ($email_exclude_from_import_list as $exclude_email)
		{
			if (strtolower($exclude_email) == strtolower($email))
				return true;
		}
		return false;
	}

	static function in_exclude_subject_list($subject)
	{
		$subject_exclude_from_import_list = array(
		    'Mail Delivery Failed\: returning message to sender',
		    'Failure notice',
		    'Mail Delivery Problem',
		    'Delivery Status Notification \(Failure\)',
		    'Delivery Status Notification \(Delay\)',
		    '\[Auto-Reply\]',
		    'Returned Mail: see transcript for details',
		    'Undelivered Mail Returned to Sender',
		    'Warning\: message [a-zA-Z0-9\-]+ delayed \d+ hours', //Warning: message 1JOvzy-0001BD-At delayed 24 hours
		    'Warning\: message delayed \d+ days', //Warning: message delayed 2 days
		    'Warning\: message delayed \d+ hours'  //Warning: message delayed 25 hours
		);
		foreach ($subject_exclude_from_import_list as $exclude_subject)
		{
			if (strtolower($exclude_subject) == strtolower($subject))
				return true;
		}
		return false;
	}

}