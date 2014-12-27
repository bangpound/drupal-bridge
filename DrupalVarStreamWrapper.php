<?php

namespace Drufony\Bridge;

class DrupalVarStreamWrapper extends \DrupalLocalStreamWrapper
{
    /**
     * Returns a web accessible URL for the resource.
     *
     * This function should return a URL that can be embedded in a web page
     * and accessed from a browser. For example, the external URL of
     * "youtube://xIpLd0WQKCY" might be
     * "http://www.youtube.com/watch?v=xIpLd0WQKCY".
     *
     * @return
     *   Returns a string containing a web accessible URL for the resource.
     */
    public function getExternalUrl()
    {
        // TODO: Implement getExternalUrl() method.
    }

    /**
     * Gets the path that the wrapper is responsible for.
     * @TODO: Review this method name in D8 per http://drupal.org/node/701358
     *
     * @return
     *   String specifying the path.
     */
    public function getDirectoryPath()
    {
        return variable_get('file_var_path', conf_path().'/files/var');
    }
}
