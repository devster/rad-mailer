<?php

class User
{
    public function getMail()
    {
        return 'bob@example.com';
    }

    public function __toString()
    {
        return 'bob';
    }
}
