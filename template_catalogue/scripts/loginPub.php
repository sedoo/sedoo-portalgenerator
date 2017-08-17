<?php

require_once("forms/login_form.php");

$form = new login_form;
$form->createLoginForm('Mail',true);

//Action logout
if (isset($_POST['logout']))
{
        session_destroy();
        $form->user=null;
}


//Action login
if (isset($_POST['bouton_public'])){
        if ($form->validate()){
                $form->loginPublic();
        }
        $form->saveErrors();
}

?>
