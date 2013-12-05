<?php

class Items 
{
	private $id;
	private $title;
	private $url;
	private $date;
	private $author;
	private $description;
	private $guid;

	protected $self = array();

	public function __construct() {
		$arguments = func_get_args(); 

		switch(sizeof(func_get_args()))
		{
			case 8: 
				$this->id = $arguments[0];
				$this->title = $arguments[1];
				$this->url = $arguments[2];
				$this->date = $arguments[3];
				$this->author = $arguments[4];
				$this->description = $arguments[5];
				$this->guid = $arguments[6];
				$this->simpleXMLElement = $arguments[7];
			break;
			
			default: break; 
		}

	}

	/*
	public function __construct($id,$title,$url,$date,$author,$description,$guid,$simpleXMLElement) {
		$this->id = $id;
		$this->title = $title;
		$this->url = $url;
		$this->date = $date;
		$this->author = $author;
		$this->description = $description;
		$this->guid = $guid;
		$this->simpleXMLElement = $simpleXMLElement;
	}
	*/

	/* Getters */
	public function getId() {
		return $this->id;
	}
	public function getTitle() {
		return $this->title;
	}
	public function getUrl() {
		return $this->url;
	}
	public function getDate() {
		return $this->date;
	}
	public function getAuthor() {
		return $this->author;
	}
	public function getDescription() {
		return $this->description;
	}
	public function getGuid() {
		return $this->guid;
	}
	
	/* Setters */
	public function setId($id) {
		$this->id = $id;
	}
	public function setTitle($title) {
		$this->title = $title;
	}
	public function setUrl($url) {
		$this->url = $url;
	}
	public function setDate($date) {
		$this->date = $date;
	}
	public function setAuthor($author) {
		$this->author = $author;
	}
	public function setDescription($description) {
		$this->description = $description;
	}
	public function setGuid($guid) {
		$this->guid = $guid;
	}

	public function logsIt() {
		return "ID: ".$this->id." ".
					"title: ".$this->title." ".
					"url: ".$this->url." ".
					"date: ".$this->date." ".
					"author: ".$this->author." ".
					"description: ".$this->description." ".
					"guid: ".$this->guid;
	}
}
