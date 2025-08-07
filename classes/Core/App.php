<?php

namespace Core;

class App
{
    private Router $router;
    private Container $container;
    
    public function __construct()
    {
        $this->container = new Container();
        $this->configureServices();
        $this->router = new Router($this->container);
        $this->setupRoutes();
    }
    
    private function configureServices(): void
    {
        // Register Database as singleton
        $this->container->singleton('database', function(Container $container) {
            return new Database();
        });
        
        // Register RoomModel factory
        $this->container->register('roomModel', function(Container $container) {
            return function(string $roomId) use ($container) {
                return new \Models\RoomModel($roomId, $container->get('database'));
            };
        });
        
        // Register UserModel factory
        $this->container->register('userModel', function(Container $container) {
            return function(string $name, bool $isAdmin = false, ?string $id = null) {
                return new \Models\UserModel($name, $isAdmin, $id);
            };
        });
    }
    
    private function setupRoutes(): void
    {
        // API routes
        $this->router->addRoute('POST', 'login', 'Controllers\\ApiController', 'login');
        $this->router->addRoute('GET', 'users', 'Controllers\\ApiController', 'users');
        $this->router->addRoute('POST', 'logout', 'Controllers\\ApiController', 'logout');
        $this->router->addRoute('POST', 'vote', 'Controllers\\ApiController', 'vote');
        $this->router->addRoute('POST', 'flip', 'Controllers\\ApiController', 'flip');
        $this->router->addRoute('GET', 'votes', 'Controllers\\ApiController', 'votes');
        $this->router->addRoute('GET', 'flipstate', 'Controllers\\ApiController', 'flipstate');
        $this->router->addRoute('POST', 'resetflip', 'Controllers\\ApiController', 'resetflip');
        $this->router->addRoute('GET', 'session-user', 'Controllers\\ApiController', 'sessionUser');
        $this->router->addRoute('POST', 'promote-user', 'Controllers\\ApiController', 'promoteUser');
        
        // Main app route
        $this->router->addRoute('GET', '', 'Controllers\\RoomController', 'index');
        // Login routes
        $this->router->addRoute('POST', 'createRoom', 'Controllers\\LoginController', 'createRoom');
        $this->router->addRoute('POST', 'joinRoom', 'Controllers\\LoginController', 'joinRoom');
    }
    
    public function run(): void
    {
        $this->router->dispatch();
    }
    
    public function getContainer(): Container
    {
        return $this->container;
    }
}
