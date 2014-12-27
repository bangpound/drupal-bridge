<?php

namespace Drufony\Bridge\Event;

class GetCallableForPhase extends BootstrapEvent
{
    /**
     * The callable object
     * @var callable
     */
    private $callable;

    /**
     * Returns the callable object
     *
     * @return callable
     *
     * @api
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Sets a callable and stops event propagation
     *
     * @param $callable
     *
     * @api
     */
    public function setCallable($callable)
    {
        if (is_object($callable) && method_exists($callable, '__invoke')) {
            $this->callable = $callable;

            $this->stopPropagation();
        }
    }

    /**
     * Returns whether a callable was set
     *
     * @return Boolean Whether a callable was set
     *
     * @api
     */
    public function hasCallable()
    {
        return null !== $this->callable;
    }
}
