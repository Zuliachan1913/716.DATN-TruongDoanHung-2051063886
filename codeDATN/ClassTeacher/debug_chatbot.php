<?php
$msg='test';
$_SERVER['REQUEST_METHOD']='POST';
$_POST['message']=$msg;
session_start();
$_SESSION=['userId'=>1,'classId'=>1,'classArmId'=>1];
include 'c:\xampp\htdocs\ClassTeacher\chatbot.php';
