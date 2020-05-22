<?php


namespace App\Service;


class OrderService
{
    public  function create($orderData){
        print_r($orderData);
        echo __CLASS__ . "/" . __METHOD__ . "\r\n";
    }
}