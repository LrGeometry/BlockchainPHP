<?php
/*
BlockchainPHP - Blockchain.info wallet object-oriented PHP framework
Copyright (c) 2017 Filip Matiaska
https://github.com/AtomisCZ/BlockchainPHP/blob/master/LICENSE
*/
defined('IN_INDEX') or die('<span class="error">You cannot access this file directly!</span>');

class BlockchainException extends Exception {
	protected $message;

	public function errorMessage() {
		$errorMsg = '<span class="error">The blockchain.info framework is not working now, please try it again later.</span><br /><span class="errorMsg">Error message: ' . $this->message . '</span>';
		return $errorMsg;
	}

	public function __construct($message) {
		$this->message = $message;
	}
}

class Blockchain {
	private static $fee = 10000;
	private $id, $pw, $address, $port;

	public function __construct($id, $pw, $address = 'localhost', $port = 3000) {
		if($port < 0 || $port > 65535 || !is_int($port)) {
			throw new BlockchainException('Client error [constructor] - port must be between 0-65535!');
		}

		$this->address = $address;
		$this->id = $id;
		$this->pw = $pw;
		$this->port = $port;

	}

	public function getID() {
		return $this->id;
	}

	public function getAddress() {
		return $this->address;
	}


	public function getPort() {
		return $this->port;
	}

	public static function setFee($fee) {
		Blockchain::$fee = $fee;
	}

	public static function getFee() {
		return Blockchain::$fee;
	}


	public function getAddressBalance($address) {
		$parse = json_decode(file_get_contents("http://$this->address:$this->port/merchant/$this->id/address_balance?password=$this->pw&address=$address"));
		return $parse->balance;
	}

	public function newAddress($label) {
		$parse = json_decode(file_get_contents("http://$this->address:$this->port/merchant/$this->id/new_address?password=$this->pw&label=$label"));
		return $parse->address;
	}

	public function payment($from, $to, $amount, $feeFromAmount = false) {
		if(!is_int($amount) || !is_bool($feeFromAmount) || $amount <= 0) {
			throw new BlockchainException('Client error [payment] - wrong declaration of parameters!');
		}

		$balance = $this->getAddressBalance($from);
		$fee = Blockchain::$fee;

		$sendingAmount = $feeFromAmount ? $amount-$fee : $amount;

		if((!$feeFromAmount && $sendingAmount+$fee > $balance)||($feeFromAmount && $sendingAmount > $balance)) {
			throw new BlockchainException('Client error [payment] - Insufficient balance!');
		}


		$parse = json_decode(file_get_contents("http://$this->address:$this->port/merchant/$this->id/payment?password=$this->pw&to=$to&amount=$sendingAmount&from=$from&fee=$fee"));

		return $parse->message;


	}
}