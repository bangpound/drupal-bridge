<?php

namespace Bangpound\Bridge\Drupal\Event;

class GetCallableEvent extends BootstrapEvent
{
    private $callable;

    /**
     * Sets a response and stops event propagation
     *
     * @param callable $callable
     *
     * @api
     */
    public function setCallable(callable $callable)
    {
        $this->callable = $callable;

        $this->stopPropagation();
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Returns whether a response was set
     *
     * @return Boolean Whether a response was set
     *
     * @api
     */
    public function hasCallable()
    {
        return null !== $this->callable;
    }
}
