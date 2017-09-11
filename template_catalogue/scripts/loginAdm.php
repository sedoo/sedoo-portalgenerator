<?php

require_once ("forms/login_form.php");

$form = new login_form;
$form->createLoginForm('Mail');

//Action logout
if (isset($_POST['logout']))
{
        session_destroy();
        $form->user=null;
}

//Action login
if (isset($_POST['bouton_login'])){
        if ($form->validate()){
                $form->loginAdmin();
        }
        $form->saveErrors();
}      
?>
