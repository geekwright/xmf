<?php

namespace Xmf\Mvc;

/**
 * This file has its roots as part of the Mojavi package which was
 * Copyright (c) 2003 Sean Kerr. It has been incorporated into this
 * derivative work under the terms of the LGPL V2.1.
 * (license terms)
 *
 * @author          Richard Griffith
 * @author          Sean Kerr
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @copyright       Portions Copyright (c) 2003 Sean Kerr
 * @license         (license terms)
 * @package         Xmf\Mvc
 * @since           1.0
 */

/**
 * A FilterList provides for registering a sequence of filters in a
 * form that can be added to the FilterChain. The Controller will
 * look for classes to instantiate both a global and a per-unit
 * filter list using specific files (GlobalFilterList.php and
 * (modulename)/filters/(unitname)FilterList.php under the
 * configured UNITS_DIR. The lists will be used to create the
 * FilterChain.
 *
 */
class FilterList extends ContextAware
{

    /**
     * An associative array of filters.
     *
     * @since  1.0
     * @var    array
     */
    protected $filters;

    /**
     * Create a new FilterList instance.
     *
     * @since  1.0
     */
    public function __construct ()
    {

        $this->filters = array();

    }

    /**
     * Register filters.
     *
     *  _This method should never be called manually._
     *
     * @since  1.0
     */
    public function registerFilters (&$filterChain)
    {

        $keys  = array_keys($this->filters);
        $count = sizeof($keys);

        // loop through cached filters and register them
        for ($i = 0; $i < $count; $i++) {

            $filterChain->register($this->filters[$keys[$i]]);

        }

    }

}