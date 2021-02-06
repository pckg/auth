<?php

class AuthControllerTest extends \Codeception\Test\Unit
{

    use \Pckg\Framework\Test\MockFramework;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $context;

    protected function _before()
    {
        $this->context = $this->mockFramework();
    }

    protected function _after()
    {
    }

    protected function getAuthController()
    {
        return new \Pckg\Auth\Controller\Auth();
    }

    protected function getAuthService()
    {
        $authService = new \Pckg\Auth\Service\Auth();
        context()->bind(\Pckg\Auth\Service\Auth::class, $authService);
        return $authService;
    }

    // tests
    public function testGetUserAction()
    {
        /**
         * Create dependencies.
         */
        $authController = $this->getAuthController();
        $authService = $this->getAuthService();
        $meta = new \Pckg\Manager\Meta();

        /**
         * Test guest scenario.
         */
        $authService->setLoggedIn(false);
        $getUserActionResponse = $authController->getUserAction($meta);
        $this->assertEquals(false, $getUserActionResponse['loggedIn'], 'Should be logged out');

        /**
         * Test logged in scenario.
         */
        $authService->setLoggedIn();
        $getUserActionResponse = $authController->getUserAction($meta);
        $this->assertEquals(true, $getUserActionResponse['loggedIn'], 'Should be logged in');
    }

    public function testGetUserAddressesAction()
    {
        $authController = $this->getAuthController();
        $authService = $this->getAuthService();

        $authService->setLoggedIn(false);
        $getUserAddressesActionResponse = $authController->getUserAddressesAction();
        $this->assertEquals([], $getUserAddressesActionResponse['addresses'], 'Guest addresses should be empty');

        $authService->setLoggedIn(true);
        $getUserAddressesActionResponse = $authController->getUserAddressesAction();
        $this->assertEquals([], $getUserAddressesActionResponse['addresses'], 'User addresses should be empty');
    }

    public function testGetLoginAction()
    {
        $authController = $this->getAuthController();
        $authService = $this->getAuthService();

        $authService->setLoggedIn(false);
        $getLoginActionResponse = $authController->getLoginAction();
        $this->assertEquals(\Pckg\Framework\View\Twig::class, get_class($getLoginActionResponse), 'Should be twig');
        $this->assertEquals('Pckg/Auth/View/login', $getLoginActionResponse->getFile(), 'Should be login template');

        $authService->setLoggedIn(true);
        $getLoginActionResponse = $authController->getLoginAction();
        $this->assertEquals(301, $getLoginActionResponse->getCode(), 'Is redirect');
        $this->assertEquals('/', $getLoginActionResponse->getRedirected(), 'Is redirected to homepage');
    }

    public function testPostLoginAction()
    {
        $authController = $this->getAuthController();
        $authService = $this->getAuthService();

        $loginForm = new \Pckg\Auth\Form\Login();
        $loginUserCommand = new \Pckg\Auth\Command\LoginUserViaForm($loginForm);

        $authService->setLoggedIn(false);
        $authController->postLoginAction($loginForm, $loginUserCommand);
        $this->assertEquals(301, $this->context->get(\Pckg\Framework\Response::class)->getCode(), 'Should be redirected');
    }

}