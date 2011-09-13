<?php

require_once t3lib_extMgm::extPath('advcache').'Classes/Api.php';

class Tx_Advcache_ParamsMatchTest extends Tx_Phpunit_TestCase {

	/**
	 * @var Tx_Advcache_Api
	 */
	protected $api;

	public function setup() {
		$this->api = t3lib_div::makeInstance('Tx_Advcache_Api'); /* @var $api Tx_Advcache_Api */
	}

	/**
	 * @test
	 */
	public function sameArraysMatch() {
		$params = array('a' => 1, 'b' => 2);
		$this->assertEquals($this->api->paramsMatch($params, $params), true);
	}

	/**
	 * @test
	 */
	public function differentArraysDontMatch() {
		$params1 = array('a' => 1, 'b' => 2);
		$params2 = array('a' => 1, 'b' => 3);
		$this->assertEquals($this->api->paramsMatch($params1, $params2), false);
	}

	/**
	 * @test
	 */
	public function differentOrderOfSameArraysMatch() {
		$params1 = array('a' => 1, 'b' => 2);
		$params2 = array('b' => 2, 'a' => 1);
		$this->assertEquals($this->api->paramsMatch($params1, $params2), true);
	}

	/**
	 * @test
	 */
	public function encryptionKeyIsIgnored() {
		$params1 = array('a' => 1, 'b' => 2, 'encryptionKey' => 'test');
		$params2 = array('a' => 1, 'b' => 2);
		$this->assertEquals($this->api->paramsMatch($params1, $params2), true);
	}

	/**
	 * @test
	 */
	public function asterisk() {
		$params1 = array('a' => 1, 'b' => '*');
		$params2 = array('a' => 1, 'b' => 2);
		$this->assertEquals($this->api->paramsMatch($params1, $params2), true);
	}

	/**
	 * @test
	 */
	public function asteriskWithAdditionalParam() {
		$params1 = array('a' => 1, 'b' => '*', 'c' => 3);
		$params2 = array('a' => 1, 'b' => 2);
		$this->assertEquals($this->api->paramsMatch($params1, $params2), false);
	}

}