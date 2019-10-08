<?php
/**
 * Created by PhpStorm.
 * User: Serere
 * Date: 6/23/2019
 * Time: 2:38 PM
 */

$environment = "PRODUCTION";
$HOST = 'localhost';
$USERNAME = ($environment === "DEVELOPMENT") ? "root" : "texazhwm_texazhwm";
$PASSWORD = ($environment === "DEVELOPMENT") ? "" : "Renovatio1664";
$DATABASE = ($environment === "DEVELOPMENT") ? "texa" : "texazhwm_2020";
