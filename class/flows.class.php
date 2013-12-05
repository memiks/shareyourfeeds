<?php


class Flows 
{
	private $id;
	private $name;
	private $url;
	private $update_date;
	private $number_of_articles;
	private $comment;
	   
	protected $self = array();

	public function __construct($id,$name,$url,$update_date,$number_of_articles,$comment) {
		$this->id = $id;
		$this->name = $name;
		$this->url = $url;
		$this->update_date = $update_date;
		$this->number_of_articles = $number_of_articles;
		$this->comment= $comment;
	}

	/* Getters */
	public function getId() {
		return $this->id;
	}
	public function getName() {
		return $this->name;
	}
	public function getUrl() {
		return $this->url;
	}
	public function getUpdateDate() {
		return $this->update_date;
	}
	public function getNumberOfArticles() {
		return $this->number_of_articles;
	}
	public function getComment() {
		return $this->comment;
	}
	
	/* Setters */
	public function setId($id) {
		$this->id = $id;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function setUrl($url) {
		$this->url = $url;
	}
	public function setUpdateDate($update_date) {
		$this->update_date = $update_date;
	}
	public function setNumberOfArticles($number_of_articles) {
		$this->number_of_articles = $number_of_articles;
	}
	public function setComment($comment) {
		$this->comment = $comment;
	}

	public function logsIt() {
		return "ID: ".$this->id." ".
					"Name: ".$this->name." ".
					"Url: ".$this->url." ".
					"number_of_articles: ".$this->number_of_articles." ".
					"Update_date: ".$this->update_date." ".
					"Comment: ".$this->comment;
	}
}
