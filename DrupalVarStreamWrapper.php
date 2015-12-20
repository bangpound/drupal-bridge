<?php

namespace Drufony\Bridge;

/**
 * {@inheritDoc}
 */
class DrupalVarStreamWrapper extends \DrupalLocalStreamWrapper
{
    /**
     * {@inheritDoc}
     */
    public function getExternalUrl()
    {
        // TODO: Implement getExternalUrl() method.
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectoryPath()
    {
        return variable_get('file_var_path', conf_path().'/files/var');
    }
}
