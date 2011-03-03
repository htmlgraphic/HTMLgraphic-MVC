<?php

class IMAPEmail
{

	private $imap_stream;
	private $message_index;
	private $header;

	function __construct($imap_stream, $message_index)
	{
		$this->imap_stream = $imap_stream;
		$this->message_index = $message_index;
	}

	public function delete()
	{
		imap_delete($this->imap_stream, $this->message_index);
		$this->imap_stream = false;
		$this->message_index = false;
	}

	public function getBody()
	{
		$structure = imap_fetchstructure($this->imap_stream, $this->message_index);
		$body = $this->findBody("TEXT/PLAIN", $structure);
		if (!$body)
			$body = $this->findBody("TEXT/HTML", $structure);
		return $body;
	}

	private function getHeader()
	{
		if (!isset($this->header))
		{
			try
			{
				$this->header = imap_headerinfo($this->imap_stream, $this->message_index, 800, 800);
			} catch (Exception $e)
			{
				return false;
			}
		}
		return $this->header;
	}

	public function getFromEmail()
	{
		if ($header = $this->getHeader())
			return $header->reply_toaddress;
	}

	public function getFromName()
	{
		if ($header = $this->getHeader())
			return $header->fromaddress;
	}

	public function getSubject()
	{
		if ($header = $this->getHeader())
			return $header->fetchsubject;
	}

	public function getDate()
	{
		if ($header = $this->getHeader())
			return $header->date;
	}

	public function getMailDate()
	{
		if ($header = $this->getHeader())
			return $header->MailDate;
	}

	private function findBody($mime_type, $structure, $prefix = false)
	{
		if ($structure)
		{
			if ($mime_type == $this->get_mime_type($structure))
			{
				if ($prefix)
					return $this->decodePart($prefix);
				else
					return $this->decodePart("1");
			}
			elseif ($structure->type == 1) /* multipart */
			{
				$recursive_prefix = '';
				if ($prefix)
					$recursive_prefix = $prefix . '.';
				foreach ($structure->parts as $index => $sub_structure)
				{
					if ($data = $this->findBody($mime_type, $sub_structure, $recursive_prefix . ($index + 1)))
						return $data;
				}
			}
		}
		return false;
	}

	/* Nice code from cleong@organic.com */
	private function get_mime_type($structure)
	{
		$primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
		if ($structure->subtype)
			return $primary_mime_type[$structure->type] . '/' . $structure->subtype;
		return "TEXT/PLAIN"; //Default
	}

	private function searchParametersForFilename($parameters)
	{
		foreach ($parameters as $parameter)
		{
			$type = strtolower($parameter->attribute);
			$value = $parameter->value;
			if ($type == 'filename' || $type == 'name')
				return $value;
		}
		return false;
	}

	public function getAttachments()
	{
		$attachments = array();
		$struct = imap_fetchstructure($this->imap_stream, $this->message_index);
		if (isset($struct->parts))
		{
			for ($part = 2; $part <= count($struct->parts); $part++)
			{
				$bodyStructure = imap_bodystruct($this->imap_stream, $this->message_index, $part);
				$filedata = $this->decodePart($part);
				$filename = false;
				if ($bodyStructure->ifdparameters)
					$filename = $this->searchParametersForFilename($bodyStructure->dparameters);
				if (!$filename && $bodyStructure->ifparameters)
					$filename = $this->searchParametersForFilename($bodyStructure->parameters);
				if (!$filename) //no name or filename parameters passed?
					$filename = "UNTITLED";
				$attachments[] = array("name" => $filename, "data" => $filedata);
			}
		}
		return $attachments;
	}

	private function decodePart($part)
	{
		$bodyStructure = imap_bodystruct($this->imap_stream, $this->message_index, $part);
		$att_data = imap_fetchbody($this->imap_stream, $this->message_index, $part);
		switch ($bodyStructure->encoding)
		{
			case '1': //utf7 //php lies, this is 0
				$filedata = @imap_utf7_decode($att_data);
				break;
//			case '1': //utf8 //php lies, this is utf7
//				$filedata = imap_utf8($att_data);
//				break;
			case '3': //base64
				$filedata = imap_base64($att_data);
				break;
			case '4': //quoted printable
				$filedata = imap_qprint($att_data);
				break;
			default:
				$filedata = $att_data;
		}
		return $filedata;
	}

	public static function parseTicketID($subject)
	{
		preg_match("/\[#\d+\]/", $subject, $ticket_matches);
		if (is_array($ticket_matches) && count($ticket_matches))
			$subject_ticket_match = $ticket_matches[0];
		else
			$subject_ticket_match = false;
		if ($subject_ticket_match)
			return substr($subject_ticket_match, 2, -1);
	}

}