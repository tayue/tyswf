<?php


namespace App\Dao;

use App\Annotation\Bean;
use App\Service\UserService as userService;
use App\Service\OrderService as orderService;
use App\Annotation\BeforeAspect;
use App\Annotation\AfterAspect;

class OrderDao
{
    /**
     * @Bean(name="userService")
     */
    protected $userService;

    /**
     * @Bean(name="orderService")
     */
    protected $orderService;

    public function setUserService(userService $userService)
    {
        $this->userService = $userService;
    }

    public function setOrderService(orderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getUserService(){
        return $this->userService;
    }

    public function getOrderService(){
        return $this->orderService;
    }

    /**
     * @BeforeAspect()
     * @AfterAspect()
     */
    public function createOrder($orderData)
    {
        $this->orderService->create($orderData);
        $this->orderService->create($orderData);
        $this->orderService->create($orderData);
        $this->userService->notify($orderData);
    }


    /**
     * @BeforeAspect()
     * @AfterAspect()
     */
    public function createUser($orderData)
    {
        $this->orderService->create($orderData);
        $this->orderService->create($orderData);
        $this->orderService->create($orderData);
        $this->userService->notify($orderData);
        $this->userService->notify($orderData);

    }
}