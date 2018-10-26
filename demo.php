<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include 'src/Parallel.php';

$p = new Qqes\Parallel\Parallel();

$actors = ['w', 'a', 'n', 'g', 'z', 'h', 'e', 'n'];

function testCallBack($key, $actor){
    $result =  $actor. rand(0, 100);
    return $result;
}

$return = $p->run($actors, 'testCallBack', true);



var_dump($return);