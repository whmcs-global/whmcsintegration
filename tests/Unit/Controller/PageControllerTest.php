<?php

namespace OCA\WhmcsIntegration\Tests\Unit\Controller;

use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\TemplateResponse;

use OCA\WhmcsIntegration\Controller\PageController;


class PageControllerTest extends PHPUnit_Framework_TestCase {
	private $controller;
	private $userId = 'john';

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$usermanager = $this->getMockBuilder('OCP\IUserManager')->getMock();
		$groupManager = $this->getMockBuilder('OCP\IGroupManager')->getMock();
		$userSession = $this->getMockBuilder('OCP\IUserSession')->getMock();

		$this->controller = new PageController(
			'whmcsintegration', $request, $usermanager, $groupManager, $userSession, $this->userId
		);
	}

	public function testIndex() {
		$result = $this->controller->index();
		$this->assertEquals('index', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}

}
