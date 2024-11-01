<?php

class shoutbox2024_activation
{

    public function hooks()
    {
        register_activation_hook("shoutbox/shoutbox.php", array($this, 'execute_activation_hooks'));
    }

    public function execute_activation_hooks()
    {
        $database = new shoutbox2024_database();
        $database->create_tables();
    }

}