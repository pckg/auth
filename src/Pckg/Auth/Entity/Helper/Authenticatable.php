<?php namespace Pckg\Auth\Entity\Helper;

trait Authenticatable
{
    
    public function forUser()
    {
        return $this->where('user_id', auth()->user('id'));
    }

}